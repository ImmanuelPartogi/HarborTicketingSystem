import 'package:flutter/material.dart';

class Vehicle {
  final int id;
  final String type;
  final String licensePlate;
  final int bookingId;
  final double price; // Tambahkan field price
  final DateTime createdAt;
  final DateTime updatedAt;

  Vehicle({
    required this.id,
    required this.type,
    required this.licensePlate,
    required this.bookingId,
    this.price = 0.0, // Default price jika tidak tersedia
    required this.createdAt,
    required this.updatedAt,
  });

  factory Vehicle.fromJson(Map<String, dynamic> json) {
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

    String vehicleType = parseString(json['type']).toLowerCase();
    
    // Jika price ada dalam JSON, gunakan itu
    // Jika tidak, tentukan harga berdasarkan tipe kendaraan
    double defaultPrice = 0.0;
    switch (vehicleType) {
      case 'motorcycle':
      case 'motor':
        defaultPrice = 50000.0;
        break;
      case 'car':
      case 'mobil':
        defaultPrice = 150000.0;
        break;
      case 'truck':
      case 'truk':
        defaultPrice = 300000.0;
        break;
      case 'bus':
        defaultPrice = 400000.0;
        break;
      default:
        defaultPrice = 100000.0;
    }

    return Vehicle(
      id: parseInt(json['id']),
      type: parseString(json['type']),
      licensePlate: parseString(json['license_plate']),
      bookingId: parseInt(json['booking_id']),
      price: parseDouble(json['price'], defaultValue: defaultPrice),
      createdAt: parseDateTime(json['created_at']),
      updatedAt: parseDateTime(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'license_plate': licensePlate,
      'booking_id': bookingId,
      'price': price,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get typeText {
    switch (type.toLowerCase()) {
      case 'motorcycle':
        return 'Motor';
      case 'car':
        return 'Mobil';
      case 'bus':
        return 'Bus';
      case 'truck':
        return 'Truk';
      default:
        return type;
    }
  }
}