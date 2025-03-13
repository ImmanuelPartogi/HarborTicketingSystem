class Ferry {
  final int id;
  final String name;
  final String type;
  final int capacity;
  final int carCapacity;
  final int motorcycleCapacity;
  final int truckCapacity;
  final int busCapacity;
  final String? photoUrl;
  final String? description;
  final bool isActive;
  final DateTime createdAt;
  final DateTime updatedAt;

  Ferry({
    required this.id,
    required this.name,
    required this.type,
    required this.capacity,
    required this.carCapacity,
    required this.motorcycleCapacity,
    required this.truckCapacity,
    required this.busCapacity,
    this.photoUrl,
    this.description,
    required this.isActive,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Ferry.fromJson(Map<String, dynamic> json) {
    return Ferry(
      id: json['id'],
      name: json['name'],
      type: json['type'],
      capacity: json['capacity'],
      carCapacity: json['car_capacity'],
      motorcycleCapacity: json['motorcycle_capacity'],
      truckCapacity: json['truck_capacity'],
      busCapacity: json['bus_capacity'],
      photoUrl: json['photo_url'],
      description: json['description'],
      isActive: json['is_active'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'type': type,
      'capacity': capacity,
      'car_capacity': carCapacity,
      'motorcycle_capacity': motorcycleCapacity,
      'truck_capacity': truckCapacity,
      'bus_capacity': busCapacity,
      'photo_url': photoUrl,
      'description': description,
      'is_active': isActive,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get vehicleCapacityText {
    List<String> capacities = [];
    
    if (carCapacity > 0) {
      capacities.add('$carCapacity Cars');
    }
    if (motorcycleCapacity > 0) {
      capacities.add('$motorcycleCapacity Motorcycles');
    }
    if (truckCapacity > 0) {
      capacities.add('$truckCapacity Trucks');
    }
    if (busCapacity > 0) {
      capacities.add('$busCapacity Buses');
    }
    
    return capacities.join(', ');
  }
}