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
      if (response.containsKey('data') && response['data'] is Map && response['data'].containsKey('bookings')) {
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

  Future<Booking> getBookingDetail(int id) async {
    try {
      final response = await _apiService.getBookingDetail(id);
      
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
      // Format passengers data
      final List<Map<String, dynamic>> formattedPassengers =
          passengers.map((p) {
            // Convert id_type to uppercase
            String apiIdType;
            switch (p['identity_type']?.toLowerCase()) {
              case 'ktp':
                apiIdType = 'KTP';
                break;
              case 'sim':
                apiIdType = 'SIM';
                break;
              case 'passport':
                apiIdType = 'PASPOR';
                break;
              default:
                apiIdType = 'KTP'; // Default value
            }

            // Convert gender to correct API format
            String apiGender;
            switch (p['gender']?.toLowerCase()) {
              case 'm':
                apiGender = 'MALE';
                break;
              case 'f':
                apiGender = 'FEMALE';
                break;
              default:
                apiGender = 'MALE'; // Default value
            }

            return {
              'id_number': p['identity_number'],
              'id_type': apiIdType,
              'dob': p['date_of_birth'],
              'gender': apiGender,
              'name': p['name'],
              'phone': p['phone'],
              'email': p['email'],
              'address': p['address'],
            };
          }).toList();

      // Format vehicle data if it exists
      List<Map<String, dynamic>>? formattedVehicles;
      if (vehicles != null && vehicles.isNotEmpty) {
        formattedVehicles =
            vehicles.map((v) {
              // Check what valid vehicle types your API accepts
              String apiVehicleType;
              switch (v['type']) {
                case 'car':
                  apiVehicleType = 'CAR';
                  break;
                case 'motorcycle':
                  apiVehicleType = 'MOTORCYCLE';
                  break;
                case 'bus':
                  apiVehicleType = 'BUS';
                  break;
                case 'truck':
                  apiVehicleType = 'TRUCK';
                  break;
                default:
                  apiVehicleType = 'CAR'; // Default value
              }

              return {
                'type': apiVehicleType,
                'license_plate': v['license_plate'],
                'brand': v['brand'],
                'model': v['model'],
                'weight': v['weight'],
              };
            }).toList();
      }

      // Create the request body
      final Map<String, dynamic> bookingData = {
        'schedule_id': scheduleId,
        'booking_date': DateTime.now().toIso8601String().split('T')[0],
        'passengers': formattedPassengers,
      };

      if (formattedVehicles != null && formattedVehicles.isNotEmpty) {
        bookingData['vehicles'] = formattedVehicles;
      }

      // Make API request
      final response = await _apiService.createBooking(bookingData);
      
      // Log the full response for debugging
      print('Create booking full response: ${jsonEncode(response)}');
      
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
      
      // If ID field is missing or zero, try to extract from booking_code
      if ((!extractedBookingData.containsKey('id') || extractedBookingData['id'] == 0) && 
          extractedBookingData.containsKey('booking_code')) {
        print('ID field missing or zero, trying to find ID by booking_code');
        // Additional logic to find booking ID from booking_code if needed
        // This might require an additional API call
      }
      
      // Create the booking object
      final booking = Booking.fromJson(extractedBookingData);
      
      // Validate ID
      if (booking.id <= 0) {
        print('WARNING: Created booking has invalid ID: ${booking.id}');
        print('Booking data: $extractedBookingData');
      }
      
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
}