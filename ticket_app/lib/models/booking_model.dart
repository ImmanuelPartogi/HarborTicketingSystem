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
  final Schedule? schedule;
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

  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: json['id'],
      userId: json['user_id'],
      scheduleId: json['schedule_id'],
      bookingNumber: json['booking_number'],
      status: json['status'],
      passengerCount: json['passenger_count'],
      totalAmount: json['total_amount'].toDouble(),
      bookedAt: DateTime.parse(json['booked_at']),
      cancelledAt: json['cancelled_at'] != null ? DateTime.parse(json['cancelled_at']) : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      schedule: json['schedule'] != null ? Schedule.fromJson(json['schedule']) : null,
      tickets: json['tickets'] != null
          ? List<Ticket>.from(json['tickets'].map((x) => Ticket.fromJson(x)))
          : null,
      vehicles: json['vehicles'] != null
          ? List<Vehicle>.from(json['vehicles'].map((x) => Vehicle.fromJson(x)))
          : null,
      payment: json['payment'] != null ? Payment.fromJson(json['payment']) : null,
    );
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