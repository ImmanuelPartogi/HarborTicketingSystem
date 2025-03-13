import '../models/booking_model.dart';
import '../models/ticket_model.dart';
import '../models/vehicle_model.dart';
import 'api_service.dart';

class BookingService {
  final ApiService _apiService;

  BookingService(this._apiService);

  Future<List<Booking>> getBookings({
    String? status,
    String? dateFrom,
    String? dateTo,
  }) async {
    try {
      final Map<String, dynamic> queryParams = {};
      
      if (status != null) {
        queryParams['status'] = status;
      }
      
      if (dateFrom != null) {
        queryParams['date_from'] = dateFrom;
      }
      
      if (dateTo != null) {
        queryParams['date_to'] = dateTo;
      }
      
      final response = await _apiService.getBookings(
        queryParams: queryParams.isNotEmpty ? queryParams : null,
      );
      
      final List<dynamic> bookingsData = response['data'];
      return bookingsData.map((json) => Booking.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to fetch bookings: ${e.toString()}');
    }
  }

  Future<Booking> getBookingDetail(int id) async {
    try {
      final response = await _apiService.getBookingDetail(id);
      return Booking.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to fetch booking details: ${e.toString()}');
    }
  }

  Future<Booking> createBooking({
    required int scheduleId,
    required List<Map<String, dynamic>> passengers,
    List<Map<String, dynamic>>? vehicles,
  }) async {
    try {
      final Map<String, dynamic> bookingData = {
        'schedule_id': scheduleId,
        'passengers': passengers,
      };
      
      if (vehicles != null && vehicles.isNotEmpty) {
        bookingData['vehicles'] = vehicles;
      }
      
      final response = await _apiService.createBooking(bookingData);
      return Booking.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to create booking: ${e.toString()}');
    }
  }

  Future<Booking> cancelBooking(int id, {String? reason}) async {
    try {
      final response = await _apiService.cancelBooking(id, reason: reason);
      return Booking.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to cancel booking: ${e.toString()}');
    }
  }

  Future<Booking> rescheduleBooking(int id, int newScheduleId) async {
    try {
      final response = await _apiService.rescheduleBooking(id, newScheduleId);
      return Booking.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to reschedule booking: ${e.toString()}');
    }
  }

  Future<List<Ticket>> getTickets({
    String? status,
    bool upcomingOnly = false,
  }) async {
    try {
      final Map<String, dynamic> queryParams = {};
      
      if (status != null) {
        queryParams['status'] = status;
      }
      
      if (upcomingOnly) {
        queryParams['upcoming_only'] = '1';
      }
      
      final response = await _apiService.getTickets(
        queryParams: queryParams.isNotEmpty ? queryParams : null,
      );
      
      final List<dynamic> ticketsData = response['data'];
      return ticketsData.map((json) => Ticket.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to fetch tickets: ${e.toString()}');
    }
  }

  Future<Ticket> getTicketDetail(int id) async {
    try {
      final response = await _apiService.getTicketDetail(id);
      return Ticket.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to fetch ticket details: ${e.toString()}');
    }
  }

  // Calculate total price for booking
  double calculateTotalPrice({
    required double basePrice,
    required int passengerCount,
    List<Map<String, String>>? vehicles,
    double? discountPercentage,
  }) {
    double total = basePrice * passengerCount;
    
    if (vehicles != null) {
      for (var vehicle in vehicles) {
        final price = double.tryParse(vehicle['price'] ?? '0') ?? 0;
        total += price;
      }
    }
    
    if (discountPercentage != null && discountPercentage > 0) {
      total = total - (total * discountPercentage / 100);
    }
    
    return total;
  }
}