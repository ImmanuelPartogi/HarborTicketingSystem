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

class BookingProvider extends ChangeNotifier {
  late ApiService _apiService;
  late BookingService _bookingService;
  late PaymentService _paymentService;
  late StorageService _storageService;
  
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
  
  String? get bookingError => _bookingError;
  String? get paymentError => _paymentError;
  
  // Constructor
  BookingProvider() {
    _initServices();
  }
  
  // Initialize services
  Future<void> _initServices() async {
    final prefs = await SharedPreferences.getInstance();
    _storageService = StorageService(prefs);
    _apiService = ApiService(_storageService);
    _bookingService = BookingService(_apiService);
    _paymentService = PaymentService(_apiService);
  }
  
  // External initialization
  void initialize(ApiService apiService, StorageService storageService) {
    _apiService = apiService;
    _storageService = storageService;
    _bookingService = BookingService(_apiService);
    _paymentService = PaymentService(_apiService);
  }
  
  // Set schedule ID for booking
  void setScheduleId(int scheduleId) {
    _scheduleId = scheduleId;
    notifyListeners();
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
  
  // Load saved passengers from storage
  Future<List<Map<String, dynamic>>> loadSavedPassengers() async {
    return _storageService.getSavedPassengers();
  }
  
  // Load saved vehicles from storage
  Future<List<Map<String, dynamic>>> loadSavedVehicles() async {
    return _storageService.getSavedVehicles();
  }
  
  // Create a new booking
  Future<bool> createBooking() async {
    if (_scheduleId <= 0 || _pendingPassengers.isEmpty) {
      _bookingError = 'Invalid booking data';
      notifyListeners();
      return false;
    }
    
    _isCreatingBooking = true;
    _bookingError = null;
    notifyListeners();
    
    try {
      _currentBooking = await _bookingService.createBooking(
        scheduleId: _scheduleId,
        passengers: _pendingPassengers,
        vehicles: _pendingVehicles.isNotEmpty ? _pendingVehicles : null,
      );
      
      // Save passengers and vehicles for future use
      for (var passenger in _pendingPassengers) {
        if (passenger.containsKey('save_info') && passenger['save_info'] == true) {
          await _storageService.savePassenger(passenger);
        }
      }
      
      for (var vehicle in _pendingVehicles) {
        if (vehicle.containsKey('save_info') && vehicle['save_info'] == true) {
          await _storageService.saveVehicle(vehicle);
        }
      }
      
      _isCreatingBooking = false;
      notifyListeners();
      return true;
    } catch (e) {
      _bookingError = 'Failed to create booking: ${e.toString()}';
      _isCreatingBooking = false;
      notifyListeners();
      return false;
    }
  }
  
  // Process payment for current booking
  Future<bool> processPayment(String paymentMethod, String paymentType) async {
    if (_currentBooking == null) {
      _paymentError = 'No active booking found';
      notifyListeners();
      return false;
    }
    
    _isProcessingPayment = true;
    _paymentError = null;
    notifyListeners();
    
    try {
      final payment = await _paymentService.createPayment(
        bookingId: _currentBooking!.id,
        paymentMethod: paymentMethod,
        paymentType: paymentType,
      );
      
      // Update current booking with payment info
      if (_currentBooking != null) {
        // Since we can't modify the immutable Booking object directly,
        // we'll need to fetch the updated booking
        await fetchBookingDetail(_currentBooking!.id);
      }
      
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
  
  // Fetch all bookings
  Future<void> fetchBookings({String? status}) async {
    _isLoadingBookings = true;
    _bookingError = null;
    notifyListeners();
    
    try {
      _bookings = await _bookingService.getBookings(status: status);
      _isLoadingBookings = false;
      notifyListeners();
    } catch (e) {
      _bookingError = 'Failed to load bookings: ${e.toString()}';
      _isLoadingBookings = false;
      notifyListeners();
    }
  }
  
  // Fetch booking detail
  Future<void> fetchBookingDetail(int id) async {
    _isLoadingBookingDetail = true;
    _bookingError = null;
    notifyListeners();
    
    try {
      _currentBooking = await _bookingService.getBookingDetail(id);
      _isLoadingBookingDetail = false;
      notifyListeners();
    } catch (e) {
      _bookingError = 'Failed to load booking details: ${e.toString()}';
      _isLoadingBookingDetail = false;
      notifyListeners();
    }
  }
  
  // Cancel booking
  Future<bool> cancelBooking(int id, {String? reason}) async {
    _isCancellingBooking = true;
    _bookingError = null;
    notifyListeners();
    
    try {
      final booking = await _bookingService.cancelBooking(id, reason: reason);
      
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
    _isReschedulingBooking = true;
    _bookingError = null;
    notifyListeners();
    
    try {
      final booking = await _bookingService.rescheduleBooking(id, newScheduleId);
      
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
  
  // Check payment status
  Future<bool> checkPaymentStatus() async {
    if (_currentBooking == null || _currentBooking!.payment == null) {
      return false;
    }
    
    try {
      final isCompleted = await _paymentService.checkPaymentStatus(_currentBooking!.payment!.id);
      
      if (isCompleted) {
        // Refresh booking details to get updated status
        await fetchBookingDetail(_currentBooking!.id);
      }
      
      return isCompleted;
    } catch (e) {
      return false;
    }
  }
  
  // Get payment methods
  List<Map<String, dynamic>> getAvailablePaymentMethods() {
    return _paymentService.getAvailablePaymentMethods();
  }
  
  // Get payment methods by type
  List<Map<String, dynamic>> getPaymentMethodsByType(String type) {
    return _paymentService.getPaymentMethodsByType(type);
  }
  
  // Get payment instructions
  Map<String, String> getPaymentInstructions(String paymentMethod, String paymentType) {
    return _paymentService.getPaymentInstructions(paymentMethod, paymentType);
  }
  
  // Calculate total price for booking
  double calculateTotalPrice({
    required double basePrice,
    required int passengerCount,
    List<Map<String, dynamic>>? vehicles,
    double? discountPercentage,
  }) {
    return _bookingService.calculateTotalPrice(
      basePrice: basePrice,
      passengerCount: passengerCount,
      vehicles: vehicles,
      discountPercentage: discountPercentage,
    );
  }
}