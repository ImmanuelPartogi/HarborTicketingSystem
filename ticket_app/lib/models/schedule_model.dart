import 'ferry_model.dart';
import 'route_model.dart';

class Schedule {
  final int id;
  final int routeId;
  final int ferryId;
  final DateTime departureTime;
  final DateTime? arrivalTime;
  final String status; // 'scheduled', 'delayed', 'cancelled', 'departed', 'arrived'
  final int availableSeats;
  final int availableCars;
  final int availableMotorcycles;
  final int availableTrucks;
  final int availableBuses;
  final double price;
  final double? discountPercentage;
  final DateTime createdAt;
  final DateTime updatedAt;
  final RouteModel? route;
  final Ferry? ferry;

  Schedule({
    required this.id,
    required this.routeId,
    required this.ferryId,
    required this.departureTime,
    this.arrivalTime,
    required this.status,
    required this.availableSeats,
    required this.availableCars,
    required this.availableMotorcycles,
    required this.availableTrucks,
    required this.availableBuses,
    required this.price,
    this.discountPercentage,
    required this.createdAt,
    required this.updatedAt,
    this.route,
    this.ferry,
  });

  factory Schedule.fromJson(Map<String, dynamic> json) {
    return Schedule(
      id: json['id'],
      routeId: json['route_id'],
      ferryId: json['ferry_id'],
      departureTime: DateTime.parse(json['departure_time']),
      arrivalTime: json['arrival_time'] != null ? DateTime.parse(json['arrival_time']) : null,
      status: json['status'],
      availableSeats: json['available_seats'],
      availableCars: json['available_cars'],
      availableMotorcycles: json['available_motorcycles'],
      availableTrucks: json['available_trucks'],
      availableBuses: json['available_buses'],
      price: json['price'].toDouble(),
      discountPercentage: json['discount_percentage']?.toDouble(),
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      route: json['route'] != null ? RouteModel.fromJson(json['route']) : null,
      ferry: json['ferry'] != null ? Ferry.fromJson(json['ferry']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'route_id': routeId,
      'ferry_id': ferryId,
      'departure_time': departureTime.toIso8601String(),
      'arrival_time': arrivalTime?.toIso8601String(),
      'status': status,
      'available_seats': availableSeats,
      'available_cars': availableCars,
      'available_motorcycles': availableMotorcycles,
      'available_trucks': availableTrucks,
      'available_buses': availableBuses,
      'price': price,
      'discount_percentage': discountPercentage,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'route': route?.toJson(),
      'ferry': ferry?.toJson(),
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
    switch (status) {
      case 'scheduled':
        return 'On Schedule';
      case 'delayed':
        return 'Delayed';
      case 'cancelled':
        return 'Cancelled';
      case 'departed':
        return 'Departed';
      case 'arrived':
        return 'Arrived';
      default:
        return 'Unknown';
    }
  }

  double get finalPrice {
    if (discountPercentage != null && discountPercentage! > 0) {
      return price - (price * discountPercentage! / 100);
    }
    return price;
  }

  bool get isAvailable {
    return status != 'cancelled' && departureTime.isAfter(DateTime.now());
  }
}