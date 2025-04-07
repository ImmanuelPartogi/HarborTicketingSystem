class Vehicle {
  final int id;
  final int bookingId;
  final String type;
  final String licensePlate;
  final double? weight;
  final String? brand;
  final String? model;
  final double price;
  final DateTime createdAt;
  final DateTime updatedAt;
  final int? ownerPassengerId; // Tambahkan field ini

  Vehicle({
    required this.id,
    required this.bookingId,
    required this.type,
    required this.licensePlate,
    this.weight,
    this.brand,
    this.model,
    required this.price,
    required this.createdAt,
    required this.updatedAt,
    this.ownerPassengerId, // Tambahkan di constructor
  });

  factory Vehicle.fromJson(Map<String, dynamic> json) {
    // Helper function untuk parsing double
    double? parseDoubleValue(dynamic value) {
      if (value == null) return null;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) {
        try {
          return double.parse(value);
        } catch (e) {
          print('Error parsing double from string: $value');
          return null;
        }
      }
      return null;
    }

    return Vehicle(
      id: json['id'],
      bookingId: json['booking_id'],
      type: json['type'],
      licensePlate: json['license_plate'],
      weight: parseDoubleValue(json['weight']),
      brand: json['brand'],
      model: json['model'],
      price: parseDoubleValue(json['price']) ?? 0.0,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      ownerPassengerId: json['owner_passenger_id'], // Ambil dari JSON
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'booking_id': bookingId,
      'type': type,
      'license_plate': licensePlate,
      'weight': weight,
      'brand': brand,
      'model': model,
      'price': price,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'owner_passenger_id': ownerPassengerId, // Sertakan dalam JSON
    };
  }

  String get typeText {
    switch (type.toLowerCase()) {
      case 'car':
        return 'Car';
      case 'motorcycle':
        return 'Motorcycle';
      case 'bus':
        return 'Bus';
      case 'truck':
        return 'Truck';
      default:
        return type;
    }
  }

  String get vehicleInfo {
    List<String> info = [];

    if (brand != null && model != null) {
      info.add('$brand $model');
    } else if (brand != null) {
      info.add(brand!);
    } else if (model != null) {
      info.add(model!);
    }

    info.add(licensePlate);

    if (weight != null) {
      info.add('$weight kg');
    }

    return info.join(' • ');
  }
}
