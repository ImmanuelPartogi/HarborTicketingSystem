import '../models/booking_model.dart';
import '../models/ticket_model.dart';
import '../models/vehicle_model.dart';
import 'api_service.dart';
import 'dart:convert';

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

      // Log response structure for debugging
      print('Bookings fetch response: ${response.keys.toList()}');

      List<dynamic> bookingsData;
      if (response.containsKey('data') &&
          response['data'] is Map &&
          response['data'].containsKey('bookings')) {
        bookingsData = response['data']['bookings'];
      } else if (response.containsKey('data') && response['data'] is List) {
        bookingsData = response['data'];
      } else {
        bookingsData = [];
      }

      return bookingsData.map((json) => Booking.fromJson(json)).toList();
    } catch (e) {
      print('Error fetching bookings: ${e.toString()}');
      throw Exception('Failed to fetch bookings: ${e.toString()}');
    }
  }

  Future<Booking> getBookingDetail(dynamic bookingIdentifier) async {
    try {
      final response = await _apiService.getBookingDetail(bookingIdentifier);

      // Log the complete response structure
      print('Booking detail response: ${jsonEncode(response)}');

      // Extract the booking data from the response
      if (response.containsKey('data') && response['data'] is Map) {
        if (response['data'].containsKey('booking')) {
          return Booking.fromJson(response['data']['booking']);
        } else {
          return Booking.fromJson(response['data']);
        }
      }

      return Booking.fromJson(response);
    } catch (e) {
      print('Error fetching booking details: ${e.toString()}');
      throw Exception('Failed to fetch booking details: ${e.toString()}');
    }
  }

  Future<Booking> createBooking({
    required int scheduleId,
    required List<Map<String, dynamic>> passengers,
    List<Map<String, dynamic>>? vehicles,
  }) async {
    try {
      // Validasi data penumpang
      for (var i = 0; i < passengers.length; i++) {
        var passenger = passengers[i];

        // Validasi ID Number
        if (passenger['id_number'] == null ||
            passenger['id_number'].toString().trim().isEmpty) {
          throw Exception('ID Number is required for passenger ${i + 1}');
        }

        // Validasi Date of Birth
        if (passenger['dob'] == null ||
            passenger['dob'].toString().trim().isEmpty) {
          throw Exception('Date of Birth is required for passenger ${i + 1}');
        }

        // Lakukan cleaning data
        passengers[i] = {
          'name': passenger['name'] ?? '',
          'id_number': passenger['id_number'].toString().trim(),
          'id_type': passenger['id_type'] ?? 'KTP',
          'dob': passenger['dob'].toString().trim(),
          'gender': passenger['gender'] ?? 'MALE',
          'email': passenger['email'] ?? '',
          'phone': passenger['phone'] ?? '',
          'address': passenger['address'] ?? '',
        };
      }

      // Format request data
      final Map<String, dynamic> bookingData = {
        'schedule_id': scheduleId,
        'booking_date': DateTime.now().toIso8601String().split('T')[0],
        'passengers': passengers,
      };

      // Debug log: print request data
      print('Booking request data: ${json.encode(bookingData)}');

      if (vehicles != null && vehicles.isNotEmpty) {
        bookingData['vehicles'] = vehicles;
      }

      // Make API request
      final response = await _apiService.createBooking(bookingData);

      // Log the full response for debugging
      print('Create booking full response: ${json.encode(response)}');

      // Extract booking data from the nested structure
      Map<String, dynamic> extractedBookingData;
      if (response.containsKey('data')) {
        if (response['data'].containsKey('booking')) {
          extractedBookingData = response['data']['booking'];
        } else {
          extractedBookingData = response['data'];
        }
      } else if (response.containsKey('booking')) {
        extractedBookingData = response['booking'];
      } else {
        extractedBookingData = response;
      }

      // Create the booking object
      final booking = Booking.fromJson(extractedBookingData);

      // TAMBAHAN: Tambahkan delay untuk memastikan data tersedia di server
      print('Menunggu sinkronisasi data booking dengan server...');
      await Future.delayed(Duration(seconds: 2));

      return booking;
    } catch (e) {
      print('Error creating booking: ${e.toString()}');
      throw Exception('Failed to create booking: ${e.toString()}');
    }
  }

  Future<Booking> cancelBooking(int id, {String? reason}) async {
    try {
      final response = await _apiService.cancelBooking(id, reason: reason);

      // Extract booking data
      Map<String, dynamic> bookingData;
      if (response.containsKey('data') && response['data'] is Map) {
        if (response['data'].containsKey('booking')) {
          bookingData = response['data']['booking'];
        } else {
          bookingData = response['data'];
        }
      } else {
        bookingData = response;
      }

      return Booking.fromJson(bookingData);
    } catch (e) {
      throw Exception('Failed to cancel booking: ${e.toString()}');
    }
  }

  Future<Booking> rescheduleBooking(int id, int newScheduleId) async {
    try {
      final response = await _apiService.rescheduleBooking(id, newScheduleId);

      // Extract booking data
      Map<String, dynamic> bookingData;
      if (response.containsKey('data') && response['data'] is Map) {
        if (response['data'].containsKey('booking')) {
          bookingData = response['data']['booking'];
        } else {
          bookingData = response['data'];
        }
      } else {
        bookingData = response;
      }

      return Booking.fromJson(bookingData);
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

  Future<String> checkPaymentStatus(int bookingId) async {
    try {
      // Coba gunakan endpoint yang benar
      final response = await _apiService.get(
        '/api/v1/bookings/id/$bookingId/payment-status',
      );

      if (response['success'] == true && response['data'] != null) {
        return response['data']['status'] ?? 'UNKNOWN';
      }

      // Jika gagal, coba ambil dari booking detail
      return _getPaymentStatusFromBookingDetail(bookingId);
    } catch (e) {
      print('Error checking payment status: $e');
      return _getPaymentStatusFromBookingDetail(bookingId);
    }
  }

  Future<String> _getPaymentStatusFromBookingDetail(int bookingId) async {
    try {
      final bookingDetail = await getBookingDetail(bookingId);
      if (bookingDetail.payment != null) {
        return bookingDetail.payment!.status;
      }
    } catch (e) {
      print('Error getting booking detail for payment status: $e');
    }
    return 'UNKNOWN';
  }
}
