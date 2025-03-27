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
  final ScheduleModel? schedule;
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
    // Debug log untuk melihat data yang diterima
    print('Parsing Ticket data: ${json.keys.toList()}');
    
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
      schedule: json['schedule'] != null ? 
        _parseSchedule(json['schedule']) : null,
      passenger: json['passenger'] != null ? 
        _parsePassenger(json['passenger']) : null,
    );
  }
  
  // Helper method untuk parsing schedule dengan penanganan error
  static ScheduleModel? _parseSchedule(dynamic scheduleData) {
    if (scheduleData == null) return null;
    try {
      return ScheduleModel.fromJson(scheduleData);
    } catch (e) {
      print('Error parsing schedule in ticket: $e');
      return null;
    }
  }
  
  // Helper method untuk parsing passenger dengan penanganan error
  static Passenger? _parsePassenger(dynamic passengerData) {
    if (passengerData == null) return null;
    try {
      return Passenger.fromJson(passengerData);
    } catch (e) {
      print('Error parsing passenger: $e');
      return null;
    }
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
    // Debug log untuk melihat data yang diterima
    print('Parsing Passenger data: ${json.keys.toList()}');
    
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
    
    int? parseNullableInt(dynamic value) {
      if (value == null) return null;
      if (value is int) return value;
      try {
        return int.parse(value.toString());
      } catch (e) {
        print('Error parsing nullable int: $value');
        return null;
      }
    }
    
    String parseString(dynamic value, {String defaultValue = ''}) {
      if (value == null) return defaultValue;
      return value.toString();
    }
    
    String? parseNullableString(dynamic value) {
      if (value == null) return null;
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
    
    return Passenger(
      id: parseInt(json['id']),
      userId: parseNullableInt(json['user_id']),
      name: parseString(json['name']),
      identityNumber: parseString(json['identity_number']),
      identityType: parseString(json['identity_type'], defaultValue: 'unknown'),
      dateOfBirth: parseNullableString(json['date_of_birth']),
      gender: parseNullableString(json['gender']),
      phone: parseNullableString(json['phone']),
      email: parseNullableString(json['email']),
      address: parseNullableString(json['address']),
      createdAt: parseDateTime(json['created_at']),
      updatedAt: parseDateTime(json['updated_at']),
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