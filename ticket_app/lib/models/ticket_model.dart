import 'package:flutter/material.dart';
import 'schedule_model.dart';

class Ticket {
  final int id;
  final int bookingId;
  final int scheduleId;
  final int passengerId;
  final String ticketNumber;
  final String status; // 'active', 'used', 'expired', 'cancelled'
  final DateTime createdAt;
  final DateTime updatedAt;
  final DateTime? usedAt;
  final Schedule? schedule;
  final Passenger? passenger;

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
  });

  factory Ticket.fromJson(Map<String, dynamic> json) {
    return Ticket(
      id: json['id'],
      bookingId: json['booking_id'],
      scheduleId: json['schedule_id'],
      passengerId: json['passenger_id'],
      ticketNumber: json['ticket_number'],
      status: json['status'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      usedAt: json['used_at'] != null ? DateTime.parse(json['used_at']) : null,
      schedule: json['schedule'] != null ? Schedule.fromJson(json['schedule']) : null,
      passenger: json['passenger'] != null ? Passenger.fromJson(json['passenger']) : null,
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
    };
  }

  bool get isActive {
    return status == 'active';
  }

  bool get isUsed {
    return status == 'used';
  }

  bool get isExpired {
    return status == 'expired';
  }

  bool get isCancelled {
    return status == 'cancelled';
  }

  String get statusText {
    switch (status) {
      case 'active':
        return 'Active';
      case 'used':
        return 'Used';
      case 'expired':
        return 'Expired';
      case 'cancelled':
        return 'Cancelled';
      default:
        return 'Unknown';
    }
  }

  Color get statusColor {
    switch (status) {
      case 'active':
        return Colors.blue;
      case 'used':
        return Colors.green;
      case 'expired':
        return Colors.grey;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}

class Passenger {
  final int id;
  final int? userId;
  final String name;
  final String identityNumber;
  final String identityType; // 'ktp', 'sim', 'passport'
  final String? dateOfBirth;
  final String? gender;
  final String? phone;
  final String? email;
  final String? address;
  final DateTime createdAt;
  final DateTime updatedAt;

  Passenger({
    required this.id,
    this.userId,
    required this.name,
    required this.identityNumber,
    required this.identityType,
    this.dateOfBirth,
    this.gender,
    this.phone,
    this.email,
    this.address,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Passenger.fromJson(Map<String, dynamic> json) {
    return Passenger(
      id: json['id'],
      userId: json['user_id'],
      name: json['name'],
      identityNumber: json['identity_number'],
      identityType: json['identity_type'],
      dateOfBirth: json['date_of_birth'],
      gender: json['gender'],
      phone: json['phone'],
      email: json['email'],
      address: json['address'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'name': name,
      'identity_number': identityNumber,
      'identity_type': identityType,
      'date_of_birth': dateOfBirth,
      'gender': gender,
      'phone': phone,
      'email': email,
      'address': address,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get identityTypeText {
    switch (identityType.toLowerCase()) {
      case 'ktp':
        return 'KTP';
      case 'sim':
        return 'SIM';
      case 'passport':
        return 'Passport';
      default:
        return identityType;
    }
  }

  String get genderText {
    if (gender == null) return '';
    
    switch (gender!.toLowerCase()) {
      case 'm':
      case 'male':
        return 'Male';
      case 'f':
      case 'female':
        return 'Female';
      default:
        return gender!;
    }
  }
}