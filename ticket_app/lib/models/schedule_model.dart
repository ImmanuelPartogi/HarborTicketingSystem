import 'ferry_model.dart';
import 'route_model.dart';

class ScheduleModel {
  final int id;
  final int routeId;
  final int ferryId;
  final DateTime departureTime;
  final DateTime? arrivalTime;
  final List<int> days;
  final String status;
  final String? statusReason;
  final DateTime? statusUpdatedAt;
  final DateTime? statusExpiryDate;
  final int? lastAdjustmentId;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  final RouteModel? route;
  final FerryModel? ferry;
  final bool isAvailable;
  final int remainingPassengerCapacity;
  final int availableSeats;
  final int availableCars;
  final int availableMotorcycles;
  final int availableTrucks;
  final int availableBuses;
  final double price;
  final double? discountPercentage;
  final String? unavailabilityReason;

  ScheduleModel({
    required this.id,
    required this.routeId,
    required this.ferryId,
    required this.departureTime,
    this.arrivalTime,
    required this.days,
    required this.status,
    this.statusReason,
    this.statusUpdatedAt,
    this.statusExpiryDate,
    this.lastAdjustmentId,
    this.createdAt,
    this.updatedAt,
    this.route,
    this.ferry,
    this.isAvailable = true,
    this.remainingPassengerCapacity = 0,
    this.availableSeats = 0,
    this.availableCars = 0,
    this.availableMotorcycles = 0,
    this.availableTrucks = 0,
    this.availableBuses = 0,
    this.price = 0.0,
    this.discountPercentage,
    this.unavailabilityReason,
  });

  factory ScheduleModel.fromJson(Map<String, dynamic> json) {
    try {
      // Parse days string to list of integers
      List<int> daysList = [];
      if (json['days'] != null) {
        daysList = json['days']
            .toString()
            .split(',')
            .map((day) => int.tryParse(day.trim()) ?? 0)
            .toList();
      }

      // Basic parsing helpers for safety
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

      // Parse departure time
      DateTime parseDepartureTime() {
        if (json['departure_time'] == null) return DateTime.now();
        
        try {
          // Try parsing as full ISO date
          return DateTime.parse(json['departure_time']);
        } catch (e) {
          try {
            // Try parsing as time only (HH:MM format)
            final timeStr = json['departure_time'].toString();
            final parts = timeStr.split(':');
            final now = DateTime.now();
            
            return DateTime(
              now.year,
              now.month,
              now.day,
              int.parse(parts[0]),
              parts.length > 1 ? int.parse(parts[1]) : 0,
            );
          } catch (e) {
            print('Error parsing departure time: ${json['departure_time']}');
            return DateTime.now();
          }
        }
      }

      return ScheduleModel(
        id: parseInt(json['id']),
        routeId: parseInt(json['route_id']),
        ferryId: parseInt(json['ferry_id']),
        departureTime: parseDepartureTime(),
        arrivalTime: json['arrival_time'] != null 
            ? DateTime.parse(json['arrival_time']) 
            : null,
        days: daysList,
        status: json['status'] ?? 'INACTIVE',
        statusReason: json['status_reason'],
        statusUpdatedAt: json['status_updated_at'] != null 
            ? DateTime.parse(json['status_updated_at']) 
            : null,
        statusExpiryDate: json['status_expiry_date'] != null 
            ? DateTime.parse(json['status_expiry_date']) 
            : null,
        lastAdjustmentId: json['last_adjustment_id'],
        createdAt: json['created_at'] != null 
            ? DateTime.parse(json['created_at']) 
            : null,
        updatedAt: json['updated_at'] != null 
            ? DateTime.parse(json['updated_at']) 
            : null,
        route: json['route'] != null ? RouteModel.fromJson(json['route']) : null,
        ferry: json['ferry'] != null ? FerryModel.fromJson(json['ferry']) : null,
        isAvailable: json['is_available'] ?? true,
        remainingPassengerCapacity: parseInt(json['remaining_passenger_capacity']),
        availableSeats: parseInt(json['available_seats'], defaultValue: json['remaining_passenger_capacity'] ?? 0),
        availableCars: parseInt(json['available_cars'], defaultValue: 0),
        availableMotorcycles: parseInt(json['available_motorcycles'], defaultValue: 0),
        availableTrucks: parseInt(json['available_trucks'], defaultValue: 0),
        availableBuses: parseInt(json['available_buses'], defaultValue: 0),
        price: parseDouble(json['price'], defaultValue: json['route']?['base_price'] != null ? parseDouble(json['route']['base_price']) : 0.0),
        discountPercentage: json['discount_percentage'] != null ? parseDouble(json['discount_percentage']) : null,
        unavailabilityReason: json['unavailability_reason'],
      );
    } catch (e) {
      print('Error parsing schedule: $e');
      print('JSON data: $json');
      rethrow; // Rethrow to see the exact error in logs
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'route_id': routeId,
      'ferry_id': ferryId,
      'departure_time': departureTime.toIso8601String(),
      'arrival_time': arrivalTime?.toIso8601String(),
      'days': days.join(','),
      'status': status,
      'status_reason': statusReason,
      'status_updated_at': statusUpdatedAt?.toIso8601String(),
      'status_expiry_date': statusExpiryDate?.toIso8601String(),
      'last_adjustment_id': lastAdjustmentId,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
      'is_available': isAvailable,
      'remaining_passenger_capacity': remainingPassengerCapacity,
      'available_seats': availableSeats,
      'available_cars': availableCars,
      'available_motorcycles': availableMotorcycles,
      'available_trucks': availableTrucks,
      'available_buses': availableBuses,
      'price': price,
      'discount_percentage': discountPercentage,
      'unavailability_reason': unavailabilityReason,
    };
  }

  String get formattedDepartureTime {
    return '${departureTime.hour.toString().padLeft(2, '0')}:${departureTime.minute.toString().padLeft(2, '0')}';
  }

  String get formattedDepartureDate {
    final months = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    final day = departureTime.day;
    final month = months[departureTime.month - 1];
    final year = departureTime.year;
    
    return '$day $month $year';
  }

  String get statusText {
    switch (status.toUpperCase()) {
      case 'ACTIVE':
        return 'Active';
      case 'INACTIVE':
        return 'Inactive';
      case 'DELAYED':
        return 'Delayed';
      case 'CANCELLED':
        return 'Cancelled';
      case 'DEPARTED':
        return 'Departed';
      case 'ARRIVED':
        return 'Arrived';
      default:
        return status;
    }
  }

  double get finalPrice {
    if (discountPercentage != null && discountPercentage! > 0) {
      return price - (price * discountPercentage! / 100);
    }
    return price;
  }

  bool get hasCapacity {
    return remainingPassengerCapacity > 0;
  }
}