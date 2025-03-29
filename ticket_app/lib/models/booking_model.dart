import 'payment_model.dart';
import 'ticket_model.dart';
import 'vehicle_model.dart';
import 'schedule_model.dart';

class Booking {
  final int id;
  final int userId;
  final int scheduleId;
  final String bookingNumber;
  final String status; // 'pending', 'confirmed', 'cancelled', 'completed', 'refunded'
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

  // Tambahkan getter untuk bookingCode yang merujuk ke bookingNumber
  // Ini akan membuat kode kompatibel dengan kedua konvensi penamaan
  String get bookingCode => bookingNumber;

  factory Booking.fromJson(Map<String, dynamic> json) {
    try {
      // Helper functions untuk parsing data dengan aman
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
      
      return Booking(
        id: parseInt(json['id']),
        userId: parseInt(json['user_id']),
        scheduleId: parseInt(json['schedule_id']),
        bookingNumber: parseString(json['booking_number']),
        status: parseString(json['status'], defaultValue: 'pending'),
        passengerCount: parseInt(json['passenger_count']),
        totalAmount: parseDouble(json['total_amount']),
        bookedAt: parseDateTime(json['booked_at']),
        cancelledAt: parseNullableDateTime(json['cancelled_at']),
        createdAt: parseDateTime(json['created_at']),
        updatedAt: parseDateTime(json['updated_at']),
        schedule: json['schedule'] != null ? ScheduleModel.fromJson(json['schedule']) : null,
        tickets: json['tickets'] != null
            ? List<Ticket>.from(json['tickets'].map((x) => Ticket.fromJson(x)))
            : null,
        vehicles: json['vehicles'] != null
            ? List<Vehicle>.from(json['vehicles'].map((x) => Vehicle.fromJson(x)))
            : null,
        payment: json['payment'] != null ? Payment.fromJson(json['payment']) : null,
      );
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
    return isPending && (payment == null || payment!.isPending || payment!.isFailed);
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