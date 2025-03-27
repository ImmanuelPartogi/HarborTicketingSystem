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
      // Prepare data by mapping the field names to what the server expects
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
                  break; // Use uppercase or whatever API expects
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
                // Include any other required fields
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
