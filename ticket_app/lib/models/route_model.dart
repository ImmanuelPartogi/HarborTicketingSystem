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
    // Debug log untuk melihat data yang diterima
    print('RouteModel.fromJson: ${json.toString()}');
    
    // Parse dates with null safety
    DateTime parseDateTime(dynamic value) {
      if (value == null) {
        return DateTime.now(); // Default value jika null
      }
      try {
        return DateTime.parse(value.toString());
      } catch (e) {
        print('Error parsing datetime: $value');
        return DateTime.now(); // Fallback ke waktu saat ini jika error
      }
    }
    
    // Parse numeric values with null safety
    double parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is int) return value.toDouble();
      if (value is double) return value;
      try {
        return double.parse(value.toString());
      } catch (e) {
        return 0.0;
      }
    }
    
    int parseInt(dynamic value) {
      if (value == null) return 0;
      if (value is int) return value;
      try {
        return int.parse(value.toString());
      } catch (e) {
        return 0;
      }
    }
    
    bool parseBool(dynamic value) {
      if (value == null) return false;
      if (value is bool) return value;
      return value.toString().toLowerCase() == 'true' || value.toString() == '1';
    }
    
    // Parse string values with null safety
    String parseString(dynamic value) {
      if (value == null) return '';
      return value.toString();
    }
    
    return RouteModel(
      id: parseInt(json['id']),
      departurePort: parseString(json['departure_port']),
      arrivalPort: parseString(json['arrival_port']),
      distance: parseDouble(json['distance']),
      estimatedDuration: parseInt(json['estimated_duration']),
      basePrice: parseDouble(json['base_price']),
      motorcyclePrice: parseDouble(json['motorcycle_price']),
      carPrice: parseDouble(json['car_price']),
      busPrice: parseDouble(json['bus_price']),
      truckPrice: parseDouble(json['truck_price']),
      isActive: parseBool(json['is_active']),
      createdAt: parseDateTime(json['created_at']),
      updatedAt: parseDateTime(json['updated_at']),
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