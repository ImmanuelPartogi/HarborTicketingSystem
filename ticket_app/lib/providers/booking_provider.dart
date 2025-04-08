import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:provider/provider.dart';

import '../models/booking_model.dart';
import '../models/passenger_model.dart';
import '../models/vehicle_model.dart';
import '../models/payment_model.dart';
import '../services/api_service.dart';
import '../services/booking_service.dart';
import '../services/payment_service.dart';
import '../services/storage_service.dart';
import 'dart:convert';
import '../providers/ferry_provider.dart';

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
  List<Map<String, dynamic>> get passengers => _pendingPassengers;
  int _scheduleId = 0;

  // Tambahkan _isLoading yang hilang
  bool _isLoading = false;
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

  // Tambahkan getter untuk _isLoading
  bool get isLoading => _isLoading;
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

  Future<bool> createBooking(BuildContext context) async {
    if (!_checkInitialized()) return false;

    if (_scheduleId <= 0) {
      _bookingError = 'Data pemesanan tidak valid';
      notifyListeners();
      return false;
    }

    _isCreatingBooking = true;
    _bookingError = null;
    notifyListeners();

    try {
      // Dapatkan FerryProvider dari Provider yang sudah ada
      final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
      final schedule = await ferryProvider.getScheduleById(_scheduleId);

      if (schedule == null) {
        _bookingError = 'Gagal mendapatkan informasi jadwal';
        _isCreatingBooking = false;
        notifyListeners();
        return false;
      }

      // Hitung total amount
      double totalAmount = schedule.finalPrice * _pendingPassengers.length;

      // Tambahkan biaya kendaraan jika ada
      if (_pendingVehicles.isNotEmpty) {
        for (var vehicle in _pendingVehicles) {
          final vehicleType =
              _convertVehicleType(vehicle['type']).toUpperCase();

          switch (vehicleType) {
            case 'MOTORCYCLE':
              totalAmount += schedule.route?.motorcyclePrice ?? 40000;
              break;
            case 'CAR':
              totalAmount += schedule.route?.carPrice ?? 75000;
              break;
            case 'BUS':
              totalAmount += schedule.route?.busPrice ?? 150000;
              break;
            case 'TRUCK':
              totalAmount += schedule.route?.truckPrice ?? 200000;
              break;
          }
        }
      }

      // Buat booking dengan parameter yang benar
      final bookingData = {
        'schedule_id': _scheduleId,
        'booking_date': DateTime.now().toIso8601String().split('T')[0],
        'passenger_count': _pendingPassengers.length,
        'total_amount': totalAmount,
        'vehicles':
            _pendingVehicles.isNotEmpty
                ? _pendingVehicles.map((v) {
                  return {
                    'type': _convertVehicleType(v['type']).toUpperCase(),
                    'plate_number': v['license_plate'],
                    'brand': v['brand'] ?? null,
                    'model': v['model'] ?? null,
                  };
                }).toList()
                : null,
      };

      print('Booking request data: ${json.encode(bookingData)}');

      _currentBooking = await _bookingService!.createBooking(
        bookingData: bookingData,
      );

      if (_currentBooking == null) {
        _bookingError =
            'Gagal membuat pemesanan. Server tidak memberikan response yang valid.';
        _isCreatingBooking = false;
        notifyListeners();
        return false;
      }

      _isCreatingBooking = false;
      notifyListeners();

      return _currentBooking != null && _currentBooking!.bookingCode.isNotEmpty;
    } catch (e) {
      _bookingError = 'Gagal membuat pemesanan: ${e.toString()}';
      _isCreatingBooking = false;
      notifyListeners();

      // Log error lebih detail
      print('Error saat membuat booking: $e');
      return false;
    }
  }

  String _convertVehicleType(String type) {
    // Sesuaikan dengan tipe kendaraan yang diharapkan API
    switch (type.toLowerCase()) {
      case 'motor':
      case 'motorcycle':
        return 'motorcycle';
      case 'car':
      case 'mobil':
        return 'car';
      case 'truck':
      case 'truk':
        return 'truck';
      case 'bus':
        return 'bus';
      default:
        return 'car'; // default ke car jika tidak dikenali
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
  Future<bool> processPayment(String paymentType, String paymentMethod) async {
    if (_currentBooking == null) {
      _paymentError = 'Tidak ada booking aktif';
      return false;
    }

    // Pastikan _paymentService tidak null sebelum digunakan
    if (_paymentService == null) {
      _paymentError = 'Payment service not initialized';
      return false;
    }

    _isProcessingPayment = true;
    _paymentError = null;
    notifyListeners();

    try {
      // Debug logs
      print('Memproses pembayaran untuk booking ID: ${_currentBooking!.id}');
      print('Metode: $paymentMethod, Tipe: $paymentType');

      final response = await _paymentService!.createPayment(
        bookingIdentifier: 'id/${_currentBooking!.id}',
        paymentMethod: paymentType,
        paymentChannel: paymentMethod,
      );

      // Debug log response
      print('Response API pembayaran: $response');

      // Selalu muat ulang data booking setelah pembayaran
      await fetchBookingDetail(_currentBooking!.id, forceRefresh: true);

      _isProcessingPayment = false;
      notifyListeners();

      return true;
    } catch (e) {
      _isProcessingPayment = false;
      _paymentError = e.toString();
      notifyListeners();
      print('Error saat memproses pembayaran: $e');
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
  // Fetch booking detail
  // Fetch booking detail
  Future<void> fetchBookingDetail(
    dynamic bookingIdentifier, {
    bool forceRefresh = false,
  }) async {
    if (bookingIdentifier == null) {
      throw Exception('ID booking tidak valid');
    }

    // Pastikan _apiService tidak null
    if (_apiService == null) {
      throw Exception('API service not initialized');
    }

    // Skip jika sudah ada data booking yang sama (kecuali force refresh)
    if (!forceRefresh &&
        _currentBooking != null &&
        (_currentBooking!.id.toString() == bookingIdentifier.toString() ||
            _currentBooking!.bookingCode == bookingIdentifier)) {
      print('Menggunakan data booking yang ada, skip fetch');
      return;
    }

    _isLoadingBookingDetail = true;
    notifyListeners();

    try {
      // PERBAIKAN: Tentukan endpoint berdasarkan tipe bookingIdentifier
      // Format endpoint yang benar untuk ID numerik adalah /api/v1/bookings/id/{id}
      final String endpoint;

      if (bookingIdentifier is int ||
          int.tryParse(bookingIdentifier.toString()) != null) {
        // Gunakan format /id/ untuk ID numerik sesuai dengan format yang berhasil di endpoint pembayaran
        endpoint = '/api/v1/bookings/id/$bookingIdentifier';
      } else {
        // Jika menggunakan booking code
        endpoint = '/api/v1/bookings/code/$bookingIdentifier';
      }

      print('Fetching booking dari endpoint: $endpoint');
      final response = await _apiService!.get(endpoint);

      if (response['success'] == true && response['data'] != null) {
        final bookingData = response['data']['booking'];
        _currentBooking = Booking.fromJson(bookingData);
        print('Berhasil memuat booking: ${_currentBooking!.id}');

        // Debug data payment
        if (_currentBooking!.payment != null) {
          print('Payment data ditemukan:');
          print('Payment ID: ${_currentBooking!.payment!.id}');
          print('Payment Method: ${_currentBooking!.payment!.paymentMethod}');
          print('Payment Channel: ${_currentBooking!.payment!.paymentChannel}');
          print('VA Number: ${_currentBooking!.payment!.vaNumber}');
        } else {
          print('WARNING: Payment data tidak ditemukan dalam booking');
        }
      } else {
        _bookingError =
            'Failed to load booking: ${response['message'] ?? "Unknown error"}';
        print('Error loading booking: ${_bookingError}');
      }
    } catch (e) {
      _bookingError = 'Error saat memuat detail booking: $e';
      print(_bookingError);
      throw Exception(_bookingError);
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

    // Pastikan _apiService tidak null
    if (_apiService == null) {
      throw Exception('API service not initialized');
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
        print('Error checking payment status: $e, second attempt: $innerError');
        _isCheckingPaymentStatus = false;
        notifyListeners();
        return false;
      }
    }
  }

  // Generate tickets for a booking - dengan pendekatan yang lebih robust
  Future<bool> generateTickets(int bookingId) async {
    if (!_checkInitialized()) return false;

    // Pastikan _apiService tidak null
    if (_apiService == null) {
      throw Exception('API service not initialized');
    }

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
    if (!_checkInitialized() || _paymentService == null) return [];
    return _paymentService!.getAvailablePaymentMethods();
  }

  // Get payment methods by type
  List<Map<String, dynamic>> getPaymentMethodsByType(String type) {
    if (!_checkInitialized() || _paymentService == null) return [];
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
