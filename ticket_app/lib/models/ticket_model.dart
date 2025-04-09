import 'package:flutter/material.dart';
import 'schedule_model.dart';
import 'vehicle_model.dart';
import 'passenger_model.dart';

class Ticket {
  final int id;
  final int bookingId;
  final int scheduleId;
  final int passengerId;
  final String ticketNumber;
  final String status;
  final DateTime createdAt;
  final DateTime updatedAt;
  final DateTime? usedAt;
  final ScheduleModel? schedule;
  final Passenger? passenger;
  final Vehicle? vehicle;

  Ticket({
    required this.id,
    required this.bookingId,
    required this.scheduleId,
    required this.passengerId,
    required this.ticketNumber,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
    this.usedAt,
    this.schedule,
    this.passenger,
    this.vehicle,
  });

  factory Ticket.fromJson(Map<String, dynamic> json) {
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

    ScheduleModel? scheduleData;
    if (json['schedule'] != null) {
      try {
        scheduleData = ScheduleModel.fromJson(json['schedule']);
      } catch (e) {
        print('Error parsing schedule: $e');
      }
    }

    Passenger? passengerData;
    if (json['passenger'] != null) {
      try {
        passengerData = Passenger.fromJson(json['passenger']);
      } catch (e) {
        print('Error parsing passenger: $e');
      }
    }

    Vehicle? vehicleData;
    if (json['vehicle'] != null) {
      try {
        vehicleData = Vehicle.fromJson(json['vehicle']);
      } catch (e) {
        print('Error parsing vehicle: $e');
      }
    }

    return Ticket(
      id: parseInt(json['id']),
      bookingId: parseInt(json['booking_id']),
      scheduleId: parseInt(json['schedule_id']),
      passengerId: parseInt(json['passenger_id']),
      ticketNumber: parseString(json['ticket_number']),
      status: parseString(json['status'], defaultValue: 'unknown'),
      createdAt: parseDateTime(json['created_at']),
      updatedAt: parseDateTime(json['updated_at']),
      usedAt: parseNullableDateTime(json['used_at']),
      schedule: scheduleData,
      passenger: passengerData,
      vehicle: vehicleData,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'booking_id': bookingId,
      'schedule_id': scheduleId,
      'passenger_id': passengerId,
      'ticket_number': ticketNumber,
      'status': status,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'used_at': usedAt?.toIso8601String(),
      'schedule': schedule?.toJson(),
      'passenger': passenger?.toJson(),
      'vehicle': vehicle?.toJson(),
    };
  }

  // Status getters
  bool get isActive => status.toLowerCase() == 'active';
  bool get isUsed => status.toLowerCase() == 'used';
  bool get isExpired => status.toLowerCase() == 'expired';
  bool get isCancelled => status.toLowerCase() == 'cancelled';

  String get statusText {
    switch (status.toLowerCase()) {
      case 'active':
        return 'Aktif';
      case 'used':
        return 'Digunakan';
      case 'expired':
        return 'Kadaluarsa';
      case 'cancelled':
        return 'Dibatalkan';
      default:
        return 'Tidak Diketahui';
    }
  }

  Color get statusColor {
    switch (status.toLowerCase()) {
      case 'active':
        return Colors.blue;
      case 'used':
        return Colors.green;
      case 'expired':
        return Colors.orange;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  // Cek apakah tiket sudah melewati waktu keberangkatan + 30 menit
  bool isExpiredByTime() {
    if (schedule == null) return false;
    
    final departureTime = schedule!.departureTime;
    
    // Estimasi waktu kedatangan jika tidak ada nilai arrivalTime
    final estimatedTripDuration = const Duration(hours: 1); // Default 1 jam
    final arrivalTime = schedule!.arrivalTime ?? 
        departureTime.add(estimatedTripDuration);
    
    // Tiket dianggap expired jika sudah 30 menit setelah waktu kedatangan
    final expirationTime = arrivalTime.add(const Duration(minutes: 30));
    
    return DateTime.now().isAfter(expirationTime);
  }

  // Cek apakah tiket sudah dekat dengan waktu keberangkatan
  bool isDepartureSoon() {
    if (schedule == null) return false;
    
    final departureTime = schedule!.departureTime;
    final now = DateTime.now();
    
    // Jika keberangkatan kurang dari 1 jam
    return departureTime.difference(now).inHours < 1 && 
           now.isBefore(departureTime);
  }
}