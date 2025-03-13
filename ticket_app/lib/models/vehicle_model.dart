class Vehicle {
  final int id;
  final int bookingId;
  final String type; // 'car', 'motorcycle', 'bus', 'truck'
  final String licensePlate;
  final double? weight;
  final String? brand;
  final String? model;
  final double price;
  final DateTime createdAt;
  final DateTime updatedAt;

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
  });

  factory Vehicle.fromJson(Map<String, dynamic> json) {
    return Vehicle(
      id: json['id'],
      bookingId: json['booking_id'],
      type: json['type'],
      licensePlate: json['license_plate'],
      weight: json['weight']?.toDouble(),
      brand: json['brand'],
      model: json['model'],
      price: json['price'].toDouble(),
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
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