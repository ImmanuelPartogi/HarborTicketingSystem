import 'payment_model.dart';
import 'ticket_model.dart';
import 'vehicle_model.dart';
import 'schedule_model.dart';

class Booking {
  final int id;
  final int userId;
  final int scheduleId;
  final String bookingNumber;
  final String
  status; // 'pending', 'confirmed', 'cancelled', 'completed', 'refunded'
  final int passengerCount;
  final double totalAmount;
  final DateTime bookedAt;
  final DateTime? cancelledAt;
  final DateTime createdAt;
  final DateTime updatedAt;
  final ScheduleModel? schedule;
  final List<Ticket>? tickets;
  final List<Vehicle>? vehicles;
  final Payment? payment;

  Booking({
    required this.id,
    required this.userId,
    required this.scheduleId,
    required this.bookingNumber,
    required this.status,
    required this.passengerCount,
    required this.totalAmount,
    required this.bookedAt,
    this.cancelledAt,
    required this.createdAt,
    required this.updatedAt,
    this.schedule,
    this.tickets,
    this.vehicles,
    this.payment,
  });

  // Getter untuk kompatibilitas
  String get bookingCode => bookingNumber;

  factory Booking.fromJson(Map<String, dynamic> json) {
    try {
      // Debug logs
      print('Booking.fromJson received: ${json.keys.toList()}');

      // Normalize data structure based on API response format
      Map<String, dynamic> bookingData = json;

      // Handle nested response from different API endpoints
      if (json.containsKey('booking')) {
        bookingData = json['booking'];
        print('Found nested booking object');
      } else if (json.containsKey('data') && json['data'] is Map) {
        if (json['data'].containsKey('booking')) {
          bookingData = json['data']['booking'];
          print('Found deeply nested booking object in data');
        } else {
          bookingData = json['data'];
          print('Using data object directly');
        }
      }

      // Log resolved booking data
      print('Processing booking data with keys: ${bookingData.keys.toList()}');
      print('ID field present: ${bookingData.containsKey('id')}');
      if (bookingData.containsKey('id')) {
        print(
          'ID value: ${bookingData['id']} (${bookingData['id'].runtimeType})',
        );
      }

      // Helper functions for safe parsing
      int parseInt(dynamic value, {int defaultValue = 0}) {
        if (value == null) return defaultValue;
        if (value is int) return value;
        try {
          return int.parse(value.toString());
        } catch (e) {
          print('Error parsing int: $value');
          return defaultValue;
        }
      }

      double parseDouble(dynamic value, {double defaultValue = 0.0}) {
        if (value == null) return defaultValue;
        if (value is double) return value;
        try {
          return double.parse(value.toString());
        } catch (e) {
          print('Error parsing double: $value');
          return defaultValue;
        }
      }

      String parseString(dynamic value, {String defaultValue = ''}) {
        if (value == null) return defaultValue;
        return value.toString();
      }

      DateTime parseDateTime(dynamic value, {DateTime? defaultValue}) {
        if (value == null) {
          return defaultValue ?? DateTime.now();
        }
        try {
          return DateTime.parse(value.toString());
        } catch (e) {
          print('Error parsing datetime: $value');
          return defaultValue ?? DateTime.now();
        }
      }

      DateTime? parseNullableDateTime(dynamic value) {
        if (value == null) return null;
        try {
          return DateTime.parse(value.toString());
        } catch (e) {
          print('Error parsing nullable datetime: $value');
          return null;
        }
      }

      // Process payment data correctly - handle both singular and plural forms
      Payment? paymentData;

      // Check for single payment
      if (bookingData['payment'] != null) {
        print('Found single payment data');
        paymentData = Payment.fromJson(bookingData['payment']);
      }
      // Check for payments array (plural)
      else if (bookingData['payments'] != null &&
          bookingData['payments'] is List) {
        final paymentsList = bookingData['payments'] as List;
        if (paymentsList.isNotEmpty) {
          print(
            'Found payments array with ${paymentsList.length} items, using latest',
          );
          // Use the latest payment (last item in the array)
          paymentData = Payment.fromJson(paymentsList.last);
        } else {
          print('Payments array is empty');
        }
      } else {
        print('WARNING: Payment data tidak ditemukan dalam booking');
      }

      // PERBAIKAN: Gunakan alternatif untuk booked_at (yang tidak ada di API)
      DateTime bookedAt;
      if (bookingData.containsKey('booked_at') &&
          bookingData['booked_at'] != null) {
        bookedAt = parseDateTime(bookingData['booked_at']);
        print('Using provided booked_at field');
      } else if (bookingData.containsKey('booking_date') &&
          bookingData['booking_date'] != null) {
        bookedAt = parseDateTime(bookingData['booking_date']);
        print('Using booking_date as a replacement for booked_at');
      } else {
        bookedAt = parseDateTime(bookingData['created_at']);
        print('Using created_at as a replacement for booked_at');
      }

      // Create booking object with the normalized data
      final booking = Booking(
        id: parseInt(bookingData['id']),
        userId: parseInt(bookingData['user_id']),
        scheduleId: parseInt(bookingData['schedule_id']),
        bookingNumber: parseString(bookingData['booking_code']),
        status: parseString(bookingData['status'], defaultValue: 'pending'),
        passengerCount: parseInt(bookingData['passenger_count']),
        totalAmount: parseDouble(bookingData['total_amount']),
        bookedAt:
            bookedAt, // Menggunakan nilai yang sudah diambil dari alternatif
        cancelledAt: parseNullableDateTime(bookingData['cancelled_at']),
        createdAt: parseDateTime(bookingData['created_at']),
        updatedAt: parseDateTime(bookingData['updated_at']),
        schedule:
            bookingData['schedule'] != null
                ? ScheduleModel.fromJson(bookingData['schedule'])
                : null,
        tickets:
            bookingData['tickets'] != null
                ? List<Ticket>.from(
                  bookingData['tickets'].map((x) => Ticket.fromJson(x)),
                )
                : null,
        vehicles:
            bookingData['vehicles'] != null
                ? List<Vehicle>.from(
                  bookingData['vehicles'].map((x) => Vehicle.fromJson(x)),
                )
                : null,
        payment: paymentData,
      );

      // Log created booking object
      print('Booking created successfully with ID: ${booking.id}');
      print('Booking code: ${booking.bookingNumber}');
      if (booking.payment != null) {
        print(
          'Payment data found: ID=${booking.payment!.id}, Method=${booking.payment!.paymentMethod}',
        );
      }

      return booking;
    } catch (e) {
      print('Error parsing booking: $e');
      print('JSON data: $json');
      rethrow;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'schedule_id': scheduleId,
      'booking_number': bookingNumber,
      'booking_code': bookingNumber, // Include both for compatibility
      'status': status,
      'passenger_count': passengerCount,
      'total_amount': totalAmount,
      'booked_at': bookedAt.toIso8601String(),
      'cancelled_at': cancelledAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'schedule': schedule?.toJson(),
      'tickets': tickets?.map((x) => x.toJson()).toList(),
      'vehicles': vehicles?.map((x) => x.toJson()).toList(),
      'payment': payment?.toJson(),
    };
  }

  // Getters untuk status (sudah ada sebelumnya)
  String get statusText {
    switch (status) {
      case 'pending':
        return 'Pending';
      case 'confirmed':
        return 'Confirmed';
      case 'cancelled':
        return 'Cancelled';
      case 'completed':
        return 'Completed';
      case 'refunded':
        return 'Refunded';
      default:
        return 'Unknown';
    }
  }

  bool get isPending {
    return status == 'pending';
  }

  bool get isConfirmed {
    return status == 'confirmed';
  }

  bool get isCancelled {
    return status == 'cancelled';
  }

  bool get isCompleted {
    return status == 'completed';
  }

  bool get isRefunded {
    return status == 'refunded';
  }

  bool get hasVehicles {
    return vehicles != null && vehicles!.isNotEmpty;
  }

  bool get needsPayment {
    return isPending &&
        (payment == null || payment!.isPending || payment!.isFailed);
  }

  bool get canBeCancelled {
    return (isPending || isConfirmed) &&
        schedule != null &&
        schedule!.departureTime.difference(DateTime.now()).inHours > 24;
  }

  bool get canBeRescheduled {
    return isConfirmed &&
        schedule != null &&
        schedule!.departureTime.difference(DateTime.now()).inHours > 48;
  }

  double get vehicleTotalPrice {
    if (vehicles == null || vehicles!.isEmpty) {
      return 0;
    }
    return vehicles!.fold(0, (sum, vehicle) => sum + vehicle.price);
  }

  double get passengerTotalPrice {
    if (schedule == null) {
      return 0;
    }
    return schedule!.finalPrice * passengerCount;
  }
}
