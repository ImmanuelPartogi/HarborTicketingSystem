import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/booking_model.dart';
import '../models/passenger_model.dart';
import '../models/vehicle_model.dart';
import '../models/payment_model.dart';
import '../services/api_service.dart';
import '../services/booking_service.dart';
import '../services/payment_service.dart';
import '../services/storage_service.dart';
import 'dart:convert';

class BookingProvider extends ChangeNotifier {
  // Mengubah dari late menjadi nullable
  ApiService? _apiService;
  BookingService? _bookingService;
  PaymentService? _paymentService;
  StorageService? _storageService;

  List<Booking> _bookings = [];
  Booking? _currentBooking;

  List<Map<String, dynamic>> _pendingPassengers = [];
  List<Map<String, dynamic>> _pendingVehicles = [];
  int _scheduleId = 0;

  bool _isLoadingBookings = false;
  bool _isCreatingBooking = false;
  bool _isProcessingPayment = false;
  bool _isLoadingBookingDetail = false;
  bool _isCancellingBooking = false;
  bool _isReschedulingBooking = false;
  bool _isGeneratingTickets = false;
  bool _isCheckingPaymentStatus = false;
  bool _isInitialized = false;

  String? _bookingError;
  String? _paymentError;

  // Getters
  List<Booking> get bookings => _bookings;
  Booking? get currentBooking => _currentBooking;

  List<Map<String, dynamic>> get pendingPassengers => _pendingPassengers;
  List<Map<String, dynamic>> get pendingVehicles => _pendingVehicles;
  int get scheduleId => _scheduleId;

  bool get isLoadingBookings => _isLoadingBookings;
  bool get isCreatingBooking => _isCreatingBooking;
  bool get isProcessingPayment => _isProcessingPayment;
  bool get isLoadingBookingDetail => _isLoadingBookingDetail;
  bool get isCancellingBooking => _isCancellingBooking;
  bool get isReschedulingBooking => _isReschedulingBooking;
  bool get isGeneratingTickets => _isGeneratingTickets;
  bool get isCheckingPaymentStatus => _isCheckingPaymentStatus;
  bool get isInitialized => _isInitialized;

  String? get bookingError => _bookingError;
  String? get paymentError => _paymentError;

  // Constructor
  BookingProvider() {
    _initServices();
  }

  // Initialize services - menggunakan async/await lebih aman
  Future<void> _initServices() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _storageService = StorageService(prefs);
      _apiService = ApiService(_storageService!);
      _bookingService = BookingService(_apiService!);
      _paymentService = PaymentService(_apiService!);
      _isInitialized = true;
      notifyListeners();
    } catch (e) {
      print('Error initializing services: $e');
      _bookingError = 'Failed to initialize app services. Please restart.';
      notifyListeners();
    }
  }

  // Cek apakah provider sudah diinisialisasi
  bool _checkInitialized() {
    if (!_isInitialized ||
        _apiService == null ||
        _bookingService == null ||
        _paymentService == null ||
        _storageService == null) {
      _bookingError = 'Services not initialized yet. Please try again.';
      notifyListeners();
      return false;
    }
    return true;
  }

  // External initialization
  void initialize(ApiService apiService, StorageService storageService) {
    _apiService = apiService;
    _storageService = storageService;
    _bookingService = BookingService(_apiService!);
    _paymentService = PaymentService(_apiService!);
    _isInitialized = true;
    notifyListeners();
  }

  // Set schedule ID for booking
  void setScheduleId(int scheduleId) {
    _scheduleId = scheduleId;

    // Gunakan Future.microtask untuk memastikan notifyListeners tidak dipanggil selama build
    Future.microtask(() {
      notifyListeners();
    });
  }

  // Add a passenger to the pending list
  void addPassenger(Map<String, dynamic> passenger) {
    _pendingPassengers.add(passenger);
    notifyListeners();
  }

  // Update a passenger in the pending list
  void updatePassenger(int index, Map<String, dynamic> passenger) {
    if (index >= 0 && index < _pendingPassengers.length) {
      _pendingPassengers[index] = passenger;
      notifyListeners();
    }
  }

  // Remove a passenger from the pending list
  void removePassenger(int index) {
    if (index >= 0 && index < _pendingPassengers.length) {
      _pendingPassengers.removeAt(index);
      notifyListeners();
    }
  }

  // Clear all pending passengers
  void clearPassengers() {
    _pendingPassengers = [];
    notifyListeners();
  }

  // Add a vehicle to the pending list
  void addVehicle(Map<String, dynamic> vehicle) {
    _pendingVehicles.add(vehicle);
    notifyListeners();
  }

  // Update a vehicle in the pending list
  void updateVehicle(int index, Map<String, dynamic> vehicle) {
    if (index >= 0 && index < _pendingVehicles.length) {
      _pendingVehicles[index] = vehicle;
      notifyListeners();
    }
  }

  // Remove a vehicle from the pending list
  void removeVehicle(int index) {
    if (index >= 0 && index < _pendingVehicles.length) {
      _pendingVehicles.removeAt(index);
      notifyListeners();
    }
  }

  // Clear all pending vehicles
  void clearVehicles() {
    _pendingVehicles = [];
    notifyListeners();
  }

  // Load saved passengers from storage - dengan pengecekan inisialisasi
  Future<List<Map<String, dynamic>>> loadSavedPassengers() async {
    if (!_checkInitialized()) {
      return [];
    }

    try {
      return await _storageService!.getSavedPassengers();
    } catch (e) {
      print('Error loading saved passengers: $e');
      return [];
    }
  }

  // Load saved vehicles from storage - dengan pengecekan inisialisasi
  Future<List<Map<String, dynamic>>> loadSavedVehicles() async {
    if (!_checkInitialized()) {
      return [];
    }

    try {
      return await _storageService!.getSavedVehicles();
    } catch (e) {
      print('Error loading saved vehicles: $e');
      return [];
    }
  }

  // Create a new booking - dengan pengecekan inisialisasi
  Future<bool> createBooking() async {
    if (!_checkInitialized()) return false;

    if (_scheduleId <= 0 || _pendingPassengers.isEmpty) {
      _bookingError = 'Invalid booking data';
      notifyListeners();
      return false;
    }

    // Validasi data penumpang sebelum melanjutkan
    for (var passenger in _pendingPassengers) {
      // Cek nomor identitas
      if (passenger['identity_number'] == null ||
          passenger['identity_number'].toString().trim().isEmpty) {
        _bookingError = 'ID number is required for all passengers';
        notifyListeners();
        return false;
      }

      // Cek tanggal lahir
      if (passenger['date_of_birth'] == null ||
          passenger['date_of_birth'].toString().trim().isEmpty) {
        _bookingError = 'Date of birth is required for all passengers';
        notifyListeners();
        return false;
      }
    }

    _isCreatingBooking = true;
    _bookingError = null;
    notifyListeners();

    try {
      // Format data penumpang untuk API
      List<Map<String, dynamic>> formattedPassengers = [];
      for (var passenger in _pendingPassengers) {
        // Pastikan semua field penting terisi dan tidak null
        Map<String, dynamic> formattedPassenger = {
          'name': passenger['name'] ?? '',
          'id_number': passenger['identity_number'] ?? '',
          'id_type': _convertIdTypeToApi(passenger['identity_type'] ?? 'ktp'),
          'dob': passenger['date_of_birth'] ?? '',
          'gender': _convertGenderToApi(passenger['gender'] ?? 'm'),
          'email': passenger['email'] ?? '',
          'phone': passenger['phone'] ?? '',
          'address': passenger['address'] ?? '',
        };

        // Debug untuk melihat data yang akan dikirim
        print('Formatted passenger data: ${json.encode(formattedPassenger)}');

        formattedPassengers.add(formattedPassenger);
      }

      _currentBooking = await _bookingService!.createBooking(
        scheduleId: _scheduleId,
        passengers: formattedPassengers,
        vehicles: _pendingVehicles.isNotEmpty ? _pendingVehicles : null,
      );

      // Debug log
      print('Booking created successfully:');
      print('  ID: ${_currentBooking!.id}');
      print('  Code: ${_currentBooking!.bookingCode}');
      print('  Status: ${_currentBooking!.status}');

      // Simpan data penumpang dan kendaraan untuk penggunaan di masa mendatang
      for (var passenger in _pendingPassengers) {
        if (passenger.containsKey('save_info') &&
            passenger['save_info'] == true) {
          await _storageService!.savePassenger(passenger);
        }
      }

      for (var vehicle in _pendingVehicles) {
        if (vehicle.containsKey('save_info') && vehicle['save_info'] == true) {
          await _storageService!.saveVehicle(vehicle);
        }
      }

      _isCreatingBooking = false;
      notifyListeners();

      return _currentBooking != null && _currentBooking!.bookingCode.isNotEmpty;
    } catch (e) {
      _bookingError = 'Failed to create booking: ${e.toString()}';
      _isCreatingBooking = false;
      notifyListeners();
      return false;
    }
  }

  String _convertIdTypeToApi(String idType) {
    switch (idType.toLowerCase()) {
      case 'ktp':
        return 'KTP';
      case 'sim':
        return 'SIM';
      case 'passport':
        return 'PASPOR';
      default:
        return 'KTP';
    }
  }

  // TAMBAHAN: Helper method untuk konversi format gender ke format API
  String _convertGenderToApi(String gender) {
    switch (gender.toLowerCase()) {
      case 'm':
      case 'male':
        return 'MALE';
      case 'f':
      case 'female':
        return 'FEMALE';
      default:
        return 'MALE';
    }
  }

  // Process payment for current booking with Midtrans
  Future<bool> processPayment(
    String paymentMethod,
    String paymentChannel,
  ) async {
    if (!_checkInitialized()) return false;

    if (_currentBooking == null) {
      _paymentError = 'No active booking found';
      notifyListeners();
      return false;
    }

    print('Processing payment for booking:');
    print('  ID: ${_currentBooking!.id}');
    print('  Code: ${_currentBooking!.bookingCode}');

    _isProcessingPayment = true;
    _paymentError = null;
    notifyListeners();

    try {
      // Format payment method untuk validasi
      String formattedPaymentMethod = _convertPaymentMethodFormat(
        paymentMethod,
      );

      print('Creating payment for booking ID: ${_currentBooking!.id}');
      print(
        'Payment method: $formattedPaymentMethod, channel: $paymentChannel',
      );

      // Tunggu untuk memastikan data tersinkronisasi
      await Future.delayed(Duration(seconds: 3));

      // PERBAIKAN: Tambahkan null assertion untuk _apiService
      if (_apiService == null) {
        throw Exception('API service not initialized');
      }

      // Gunakan endpoint berdasarkan ID numerik
      final response = await _apiService!.post(
        '/api/v1/bookings/id/${_currentBooking!.id}/pay',
        body: {
          'payment_method': formattedPaymentMethod,
          'payment_channel': paymentChannel,
        },
      );

      // Refresh data booking setelah pembayaran
      await fetchBookingDetail(_currentBooking!.id);

      _isProcessingPayment = false;
      notifyListeners();
      return true;
    } catch (e) {
      _paymentError = 'Payment processing failed: ${e.toString()}';
      _isProcessingPayment = false;
      notifyListeners();
      return false;
    }
  }

  // Helper method untuk konversi format payment method
  String _convertPaymentMethodFormat(String paymentMethod) {
    switch (paymentMethod.toLowerCase()) {
      case 'virtual_account':
        return 'VIRTUAL_ACCOUNT';
      case 'e_wallet':
        return 'E_WALLET';
      case 'credit_card':
        return 'CREDIT_CARD';
      case 'bank_transfer':
        return 'BANK_TRANSFER';
      default:
        return paymentMethod.toUpperCase();
    }
  }

  // Fetch all bookings
  Future<void> fetchBookings({String? status}) async {
    if (!_checkInitialized()) return;

    _isLoadingBookings = true;
    _bookingError = null;
    notifyListeners();

    try {
      _bookings = await _bookingService!.getBookings(status: status);
      _isLoadingBookings = false;
      notifyListeners();
    } catch (e) {
      _bookingError = 'Failed to load bookings: ${e.toString()}';
      _isLoadingBookings = false;
      notifyListeners();
    }
  }

  // Fetch booking detail
  Future<void> fetchBookingDetail(dynamic bookingIdentifier) async {
    if (!_checkInitialized()) return;

    _isLoadingBookingDetail = true;
    _bookingError = null;
    notifyListeners();

    try {
      print('Fetching booking details for identifier: $bookingIdentifier');

      // Handle ID numerik (int) dengan endpoint khusus
      if (bookingIdentifier is int) {
        try {
          print('Using numeric ID endpoint for booking ID: $bookingIdentifier');

          // Gunakan endpoint khusus untuk ID numerik
          final response = await _apiService!.get(
            '/api/v1/bookings/id/$bookingIdentifier',
          );

          // Proses response
          if (response.containsKey('data') && response['data'] is Map) {
            if (response['data'].containsKey('booking')) {
              _currentBooking = Booking.fromJson(response['data']['booking']);
            } else {
              _currentBooking = Booking.fromJson(response['data']);
            }

            print('Successfully loaded booking by ID: ${_currentBooking!.id}');
            _isLoadingBookingDetail = false;
            notifyListeners();
            return;
          }
        } catch (e) {
          print('Error fetching booking by ID: $e');
          // Gagal dengan ID, lanjutkan dengan cara lain jika memungkinkan
        }
      }

      // Cara normal dengan booking code jika tidak berhasil dengan ID
      String identifier;

      // Tentukan identifier yang akan digunakan
      if (bookingIdentifier is Booking) {
        identifier = bookingIdentifier.bookingCode;
      } else if (bookingIdentifier is int) {
        // Coba gunakan booking code dari cache jika ada dan ID cocok
        if (_currentBooking != null &&
            _currentBooking!.id == bookingIdentifier) {
          identifier = _currentBooking!.bookingCode;
          print('Using booking code $identifier from cached booking');
        } else {
          throw Exception(
            'Tidak dapat menemukan booking code untuk ID: $bookingIdentifier',
          );
        }
      } else {
        // Asumsikan string
        identifier = bookingIdentifier.toString();
      }

      // Tunggu sebelum request untuk memastikan data tersinkronisasi
      print('Delaying initial request to allow data synchronization...');
      await Future.delayed(Duration(seconds: 2));

      // Gunakan booking service dengan retry
      _currentBooking = await _bookingService!.getBookingDetail(identifier);

      if (_currentBooking == null) {
        throw Exception(
          'Booking tidak ditemukan dengan identifier: $identifier',
        );
      }

      print('Successfully loaded booking: ${_currentBooking!.bookingCode}');
    } catch (e) {
      _bookingError = 'Gagal memuat detail booking: ${e.toString()}';
      print('Error in fetchBookingDetail: $_bookingError');
    } finally {
      _isLoadingBookingDetail = false;
      notifyListeners();
    }
  }

  // Cancel booking
  Future<bool> cancelBooking(int id, {String? reason}) async {
    if (!_checkInitialized()) return false;

    _isCancellingBooking = true;
    _bookingError = null;
    notifyListeners();

    try {
      final booking = await _bookingService!.cancelBooking(id, reason: reason);

      // Update lists
      _updateBookingInList(booking);
      _currentBooking = booking;

      _isCancellingBooking = false;
      notifyListeners();
      return true;
    } catch (e) {
      _bookingError = 'Failed to cancel booking: ${e.toString()}';
      _isCancellingBooking = false;
      notifyListeners();
      return false;
    }
  }

  // Reschedule booking
  Future<bool> rescheduleBooking(int id, int newScheduleId) async {
    if (!_checkInitialized()) return false;

    _isReschedulingBooking = true;
    _bookingError = null;
    notifyListeners();

    try {
      final booking = await _bookingService!.rescheduleBooking(
        id,
        newScheduleId,
      );

      // Update lists
      _updateBookingInList(booking);
      _currentBooking = booking;

      _isReschedulingBooking = false;
      notifyListeners();
      return true;
    } catch (e) {
      _bookingError = 'Failed to reschedule booking: ${e.toString()}';
      _isReschedulingBooking = false;
      notifyListeners();
      return false;
    }
  }

  // Helper to update a booking in the bookings list
  void _updateBookingInList(Booking booking) {
    final index = _bookings.indexWhere((b) => b.id == booking.id);
    if (index >= 0) {
      _bookings[index] = booking;
    }
  }

  // Clear current booking
  void clearCurrentBooking() {
    _currentBooking = null;
    _pendingPassengers = [];
    _pendingVehicles = [];
    _scheduleId = 0;
    notifyListeners();
  }

  // Check payment status with Midtrans
  Future<bool> checkPaymentStatus() async {
    if (!_checkInitialized()) return false;
    if (_currentBooking == null || _currentBooking!.payment == null) {
      return false;
    }

    _isCheckingPaymentStatus = true;
    notifyListeners();

    try {
      // PERBAIKAN: Gunakan endpoint yang benar untuk payment-status
      final response = await _apiService!.get(
        '/api/v1/bookings/${_currentBooking!.id}/payment-status',
        bypassThrottling: true, // Important for real-time status checking
      );

      _isCheckingPaymentStatus = false;
      notifyListeners();

      if (response['success'] == true && response['data'] != null) {
        final status = response['data']['status'] ?? '';
        final isCompleted = status.toString().toUpperCase() == 'SUCCESS';

        if (isCompleted) {
          // Refresh booking details to get updated status
          await fetchBookingDetail(_currentBooking!.id);
        }

        return isCompleted;
      }

      return false;
    } catch (e) {
      // Try alternative endpoint if first one fails
      try {
        print('First endpoint failed, trying alternative endpoint...');
        final response = await _apiService!.get(
          '/api/v1/payments/status/${_currentBooking!.payment!.id}',
          bypassThrottling: true,
        );

        _isCheckingPaymentStatus = false;
        notifyListeners();

        if (response['success'] == true && response['data'] != null) {
          final status = response['data']['status'] ?? '';
          final isCompleted = status.toString().toUpperCase() == 'SUCCESS';

          if (isCompleted) {
            await fetchBookingDetail(_currentBooking!.id);
          }

          return isCompleted;
        }

        return false;
      } catch (innerError) {
        debugPrint(
          'Error checking payment status: $e, second attempt: $innerError',
        );
        _isCheckingPaymentStatus = false;
        notifyListeners();
        return false;
      }
    }
  }

  // Generate tickets for a booking - dengan pendekatan yang lebih robust
  Future<bool> generateTickets(int bookingId) async {
    if (!_checkInitialized()) return false;

    try {
      if (_currentBooking == null || _currentBooking!.id != bookingId) {
        await fetchBookingDetail(bookingId);
      }

      if (_currentBooking == null) {
        _bookingError = 'Booking not found';
        notifyListeners();
        return false;
      }

      if (!_currentBooking!.isConfirmed) {
        // Coba verifikasi status terlebih dahulu
        bool statusChecked = await checkPaymentStatus();
        if (!statusChecked || !_currentBooking!.isConfirmed) {
          _bookingError =
              'Booking not confirmed. Please complete payment first.';
          notifyListeners();
          return false;
        }
      }

      _isGeneratingTickets = true;
      _bookingError = null;
      notifyListeners();

      // PERBAIKAN: Coba endpoint dengan ID numerik dahulu
      try {
        final response = await _apiService!.post(
          '/api/v1/bookings/${_currentBooking!.id}/generate-tickets',
          body: {},
          bypassThrottling: true,
        );

        // Refresh booking to get the generated tickets
        await fetchBookingDetail(bookingId);

        _isGeneratingTickets = false;
        notifyListeners();
        return response['success'] == true;
      } catch (e) {
        print('First ticket generation attempt failed: $e');

        // Coba endpoint dengan booking_code jika endpoint dengan ID gagal
        try {
          final response = await _apiService!.post(
            '/api/v1/bookings/${_currentBooking!.bookingCode}/generate-tickets',
            body: {},
            bypassThrottling: true,
          );

          // Refresh booking to get the generated tickets
          await fetchBookingDetail(bookingId);

          _isGeneratingTickets = false;
          notifyListeners();
          return response['success'] == true;
        } catch (innerError) {
          _bookingError = 'Failed to generate tickets: $innerError';
          _isGeneratingTickets = false;
          notifyListeners();
          return false;
        }
      }
    } catch (e) {
      _bookingError = 'Failed to generate tickets: ${e.toString()}';
      _isGeneratingTickets = false;
      notifyListeners();
      return false;
    }
  }

  // Check if tickets exist for the current booking
  bool hasTickets() {
    if (_currentBooking == null) return false;
    return _currentBooking!.tickets != null &&
        _currentBooking!.tickets!.isNotEmpty;
  }

  // Get available payment methods
  List<Map<String, dynamic>> getAvailablePaymentMethods() {
    if (!_checkInitialized()) return [];
    return _paymentService!.getAvailablePaymentMethods();
  }

  // Get payment methods by type
  List<Map<String, dynamic>> getPaymentMethodsByType(String type) {
    if (!_checkInitialized()) return [];
    return _paymentService!.getPaymentMethodsByType(type);
  }

  // Get payment instructions
  Map<String, String> getPaymentInstructions(
    String paymentMethod,
    String paymentType,
  ) {
    if (paymentType == 'virtual_account') {
      switch (paymentMethod.toLowerCase()) {
        case 'bca':
          return {
            'title': 'BCA Virtual Account Payment Instructions',
            'steps': '''
1. Login to your BCA Mobile Banking app or Internet Banking.
2. Choose "Transfer" > "Virtual Account".
3. Enter the BCA Virtual Account number shown above.
4. Confirm the payment details and amount.
5. Enter your PIN or password to authorize the payment.
6. A receipt will be shown when payment is successful.
7. Your booking will be automatically confirmed.

You can also pay through BCA ATM:
1. Insert your ATM card and enter your PIN.
2. Select "Other Transactions".
3. Select "Transfer".
4. Select "To BCA Virtual Account".
5. Enter the Virtual Account number.
6. Confirm the payment details and complete the transaction.
''',
          };
        case 'bni':
          return {
            'title': 'BNI Virtual Account Payment Instructions',
            'steps': '''
1. Login to your BNI Mobile Banking app or Internet Banking.
2. Choose "Transfer" > "Virtual Account" or "Transfer to BNI Virtual Account".
3. Enter the BNI Virtual Account number shown above.
4. Confirm the payment details and amount.
5. Enter your PIN or password to authorize the payment.
6. A receipt will be shown when payment is successful.
7. Your booking will be automatically confirmed.

You can also pay through BNI ATM:
1. Insert your ATM card and enter your PIN.
2. Select "Menu Lainnya".
3. Select "Transfer".
4. Select "Rekening Tabungan".
5. Select "Ke Rekening BNI".
6. Enter the Virtual Account number.
7. Confirm the payment details and complete the transaction.
''',
          };
        case 'bri':
          return {
            'title': 'BRI Virtual Account Payment Instructions',
            'steps': '''
1. Login to your BRI Mobile Banking app or Internet Banking.
2. Choose "Transfer" > "BRIVA".
3. Enter the BRI Virtual Account number shown above.
4. Confirm the payment details and amount.
5. Enter your PIN or password to authorize the payment.
6. A receipt will be shown when payment is successful.
7. Your booking will be automatically confirmed.

You can also pay through BRI ATM:
1. Insert your ATM card and enter your PIN.
2. Select "Transaksi Lainnya".
3. Select "Pembayaran".
4. Select "BRIVA".
5. Enter the BRIVA number.
6. Confirm the payment details and complete the transaction.
''',
          };
        case 'mandiri':
          return {
            'title': 'Mandiri Bill Payment Instructions',
            'steps': '''
1. Login to your Mandiri Mobile Banking app or Internet Banking.
2. Choose "Pembayaran" > "Multi Payment".
3. Select "Ferry Ticketing" as the biller.
4. Enter your payment code shown above.
5. Confirm the payment details and amount.
6. Enter your PIN or password to authorize the payment.
7. A receipt will be shown when payment is successful.
8. Your booking will be automatically confirmed.

You can also pay through Mandiri ATM:
1. Insert your ATM card and enter your PIN.
2. Select "Bayar/Beli".
3. Select "Multi Payment".
4. Enter company code "70012" (Ferry Ticketing).
5. Enter your payment code.
6. Confirm the payment details and complete the transaction.
''',
          };
        case 'permata':
          return {
            'title': 'Permata Virtual Account Payment Instructions',
            'steps': '''
1. Login to your Permata Mobile Banking app or Internet Banking.
2. Choose "Pembayaran" > "Pembayaran Tagihan".
3. Select "Virtual Account".
4. Enter the Permata Virtual Account number shown above.
5. Confirm the payment details and amount.
6. Enter your PIN or password to authorize the payment.
7. A receipt will be shown when payment is successful.
8. Your booking will be automatically confirmed.

You can also pay through Permata ATM:
1. Insert your ATM card and enter your PIN.
2. Select "Transaksi Lainnya".
3. Select "Pembayaran".
4. Select "Pembayaran Lainnya".
5. Select "Virtual Account".
6. Enter the Virtual Account number.
7. Confirm the payment details and complete the transaction.
''',
          };
        default:
          return {
            'title': 'Virtual Account Payment Instructions',
            'steps': '''
1. Login to your mobile banking app or internet banking.
2. Choose "Transfer" > "Virtual Account" or similar option.
3. Enter the Virtual Account number shown above.
4. Confirm the payment details and amount.
5. Enter your PIN or password to authorize the payment.
6. A receipt will be shown when payment is successful.
7. Your booking will be automatically confirmed.

You can also pay through ATM:
1. Insert your ATM card and enter your PIN.
2. Select "Transfer" or "Payment".
3. Select "Virtual Account" or similar option.
4. Enter the Virtual Account number.
5. Confirm the payment details and complete the transaction.
''',
          };
      }
    } else if (paymentType == 'e_wallet') {
      switch (paymentMethod.toLowerCase()) {
        case 'gopay':
          return {
            'title': 'GoPay Payment Instructions',
            'steps': '''
1. Press the "Pay Now" button below.
2. A QR code will be displayed on the next screen.
3. Open your GoPay app.
4. Tap "Scan" or "Pay" in your GoPay app.
5. Scan the QR code that appears.
6. Confirm the payment details and amount.
7. Enter your PIN to authorize payment.
8. A notification will confirm your successful payment.
9. Your booking will be automatically confirmed.

Alternatively:
- If using a mobile device, you may be redirected to the GoPay app automatically.
- Follow the instructions in the GoPay app to complete your payment.
''',
          };
        case 'ovo':
          return {
            'title': 'OVO Payment Instructions',
            'steps': '''
1. Press the "Pay Now" button below.
2. A QR code will be displayed on the next screen.
3. Open your OVO app.
4. Tap "Scan QR" in your OVO app.
5. Scan the QR code that appears.
6. Confirm the payment details and amount.
7. Enter your OVO PIN to authorize payment.
8. A notification will confirm your successful payment.
9. Your booking will be automatically confirmed.

Alternatively:
- If using a mobile device, you may be redirected to the OVO app automatically.
- Follow the instructions in the OVO app to complete your payment.
''',
          };
        case 'dana':
          return {
            'title': 'DANA Payment Instructions',
            'steps': '''
1. Press the "Pay Now" button below.
2. A QR code will be displayed on the next screen.
3. Open your DANA app.
4. Tap "Scan" icon at the bottom of the DANA app.
5. Scan the QR code that appears.
6. Confirm the payment details and amount.
7. Enter your DANA PIN to authorize payment.
8. A notification will confirm your successful payment.
9. Your booking will be automatically confirmed.

Alternatively:
- If using a mobile device, you may be redirected to the DANA app automatically.
- Follow the instructions in the DANA app to complete your payment.
''',
          };
        case 'shopeepay':
          return {
            'title': 'ShopeePay Payment Instructions',
            'steps': '''
1. Press the "Pay Now" button below.
2. You will be redirected to the Shopee app.
3. If the Shopee app doesn't open automatically, open it manually.
4. A payment page will appear in the Shopee app.
5. Confirm the payment details and amount.
6. Swipe to pay or enter your PIN to authorize payment.
7. A notification will confirm your successful payment.
8. Your booking will be automatically confirmed.

Note:
- Make sure you have the latest version of the Shopee app installed.
- Ensure you have sufficient balance in your ShopeePay wallet.
''',
          };
        default:
          return {
            'title': 'E-Wallet Payment Instructions',
            'steps': '''
1. Press the "Pay Now" button below.
2. A QR code or payment page will be displayed.
3. Open your e-wallet app.
4. Use the scan feature in your e-wallet app to scan the QR code.
5. Alternatively, you may be redirected to your e-wallet app automatically.
6. Confirm the payment details and amount.
7. Enter your PIN to authorize payment.
8. A notification will confirm your successful payment.
9. Your booking will be automatically confirmed.
''',
          };
      }
    } else if (paymentType == 'bank_transfer') {
      return {
        'title': 'Manual Bank Transfer Instructions',
        'steps': '''
1. Make a transfer to the following bank account:
   Bank: ${_getBankName(paymentMethod)}
   Account Number: ${_getBankAccountNumber(paymentMethod)}
   Account Name: PT Harbor Ferry Services

2. Use your booking code (shown above) as the transfer reference.

3. After making the transfer, please upload your transfer receipt:
   - Take a clear photo of your transfer receipt
   - Click the "Upload Receipt" button below
   - Verify the details are correct and submit

4. Your booking will be confirmed after our team verifies your payment.
5. This process may take up to 24 hours during business days.
''',
      };
    } else if (paymentType == 'credit_card') {
      return {
        'title': 'Credit Card Payment Instructions',
        'steps': '''
1. Press the "Pay Now" button below.
2. You will be redirected to a secure payment page.
3. Enter your credit card details:
   - Card number
   - Cardholder name
   - Expiry date
   - CVV/CVC (3-digit security code)
4. Some banks may require additional verification:
   - OTP (One-Time Password) sent to your registered mobile number
   - 3D Secure authentication
5. After successful verification, your payment will be processed.
6. You will be redirected back to the booking confirmation page.
7. Your booking will be automatically confirmed.

Note:
- This is a secure payment process. Your card details are encrypted.
- Make sure you have sufficient balance in your credit card.
''',
      };
    }

    return {
      'title': 'Payment Instructions',
      'steps':
          'Follow the instructions after clicking "Pay Now" button to complete your payment.',
    };
  }

  // Helper method untuk mendapatkan nama bank untuk pembayaran bank transfer
  String _getBankName(String bankCode) {
    switch (bankCode.toLowerCase()) {
      case 'bca':
        return 'BCA (Bank Central Asia)';
      case 'bni':
        return 'BNI (Bank Negara Indonesia)';
      case 'bri':
        return 'BRI (Bank Rakyat Indonesia)';
      case 'mandiri':
        return 'Bank Mandiri';
      case 'permata':
        return 'Bank Permata';
      default:
        return bankCode.toUpperCase();
    }
  }

  // Helper method untuk mendapatkan nomor rekening bank untuk pembayaran bank transfer
  String _getBankAccountNumber(String bankCode) {
    switch (bankCode.toLowerCase()) {
      case 'bca':
        return '1234567890';
      case 'bni':
        return '0123456789';
      case 'bri':
        return '0987654321';
      case 'mandiri':
        return '1357924680';
      case 'permata':
        return '9876543210';
      default:
        return '0000000000';
    }
  }

  // Calculate total price for booking
  double calculateTotalPrice({
    required double basePrice,
    required int passengerCount,
    List<Map<String, dynamic>>? vehicles,
    double? discountPercentage,
  }) {
    // Base price per passenger
    double total = basePrice * passengerCount;

    // Add vehicle costs
    if (vehicles != null && vehicles.isNotEmpty) {
      for (var vehicle in vehicles) {
        // Get vehicle type - ensure conversion to String if needed
        String vehicleType = vehicle['type'].toString().toLowerCase();

        switch (vehicleType) {
          case 'motor':
          case 'motorcycle':
            total += 50000; // Example price
            break;
          case 'car':
            total += 150000; // Example price
            break;
          case 'truck':
            total += 300000; // Example price
            break;
          case 'bus':
            total += 400000; // Example price
            break;
          default:
            total += 100000; // Default price
        }
      }
    }

    // Apply discount if available
    if (discountPercentage != null && discountPercentage > 0) {
      total = total - (total * (discountPercentage / 100));
    }

    return total;
  }
}
