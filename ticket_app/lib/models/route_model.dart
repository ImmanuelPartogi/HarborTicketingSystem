class RouteModel {
  final int id;
  final String departurePort;
  final String arrivalPort;
  final double distance;
  final int estimatedDuration; // In minutes
  final double basePrice;
  final double motorcyclePrice;
  final double carPrice;
  final double busPrice;
  final double truckPrice;
  final bool isActive;
  final DateTime createdAt;
  final DateTime updatedAt;

  RouteModel({
    required this.id,
    required this.departurePort,
    required this.arrivalPort,
    required this.distance,
    required this.estimatedDuration,
    required this.basePrice,
    required this.motorcyclePrice,
    required this.carPrice,
    required this.busPrice,
    required this.truckPrice,
    required this.isActive,
    required this.createdAt,
    required this.updatedAt,
  });

  factory RouteModel.fromJson(Map<String, dynamic> json) {
    return RouteModel(
      id: json['id'],
      departurePort: json['departure_port'],
      arrivalPort: json['arrival_port'],
      distance: json['distance'].toDouble(),
      estimatedDuration: json['estimated_duration'],
      basePrice: json['base_price'].toDouble(),
      motorcyclePrice: json['motorcycle_price'].toDouble(),
      carPrice: json['car_price'].toDouble(),
      busPrice: json['bus_price'].toDouble(),
      truckPrice: json['truck_price'].toDouble(),
      isActive: json['is_active'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'departure_port': departurePort,
      'arrival_port': arrivalPort,
      'distance': distance,
      'estimated_duration': estimatedDuration,
      'base_price': basePrice,
      'motorcycle_price': motorcyclePrice,
      'car_price': carPrice,
      'bus_price': busPrice,
      'truck_price': truckPrice,
      'is_active': isActive,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get formattedDuration {
    final hours = estimatedDuration ~/ 60;
    final minutes = estimatedDuration % 60;
    
    if (hours > 0) {
      return '$hours h ${minutes > 0 ? '$minutes min' : ''}';
    } else {
      return '$minutes min';
    }
  }

  String get routeName {
    return '$departurePort → $arrivalPort';
  }
}