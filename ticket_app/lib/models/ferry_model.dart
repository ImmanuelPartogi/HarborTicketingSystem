class FerryModel {
  final int id;
  final String name;
  final String type;
  final String status;
  final int capacityPassenger;
  final int capacityVehicleMotorcycle;
  final int capacityVehicleCar;
  final int capacityVehicleBus;
  final int capacityVehicleTruck;
  final String? photoUrl;
  final String? description;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  FerryModel({
    required this.id,
    required this.name,
    this.type = '',
    required this.status,
    required this.capacityPassenger,
    required this.capacityVehicleMotorcycle,
    required this.capacityVehicleCar,
    required this.capacityVehicleBus,
    required this.capacityVehicleTruck,
    this.photoUrl,
    this.description,
    this.createdAt,
    this.updatedAt,
  });

  factory FerryModel.fromJson(Map<String, dynamic> json) {
    try {
      return FerryModel(
        id: json['id'] ?? 0,
        name: json['name'] ?? '',
        type: json['type'] ?? '',
        status: json['status'] ?? 'INACTIVE',
        capacityPassenger: json['capacity_passenger'] ?? 0,
        capacityVehicleMotorcycle: json['capacity_vehicle_motorcycle'] ?? 0,
        capacityVehicleCar: json['capacity_vehicle_car'] ?? 0,
        capacityVehicleBus: json['capacity_vehicle_bus'] ?? 0,
        capacityVehicleTruck: json['capacity_vehicle_truck'] ?? 0,
        photoUrl: json['photo_url'],
        description: json['description'],
        createdAt: json['created_at'] != null ? DateTime.parse(json['created_at']) : null,
        updatedAt: json['updated_at'] != null ? DateTime.parse(json['updated_at']) : null,
      );
    } catch (e) {
      print('Error parsing ferry: $e');
      print('JSON data: $json');
      rethrow;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'type': type,
      'status': status,
      'capacity_passenger': capacityPassenger,
      'capacity_vehicle_motorcycle': capacityVehicleMotorcycle,
      'capacity_vehicle_car': capacityVehicleCar,
      'capacity_vehicle_bus': capacityVehicleBus,
      'capacity_vehicle_truck': capacityVehicleTruck,
      'photo_url': photoUrl,
      'description': description,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  String get statusText {
    switch (status.toUpperCase()) {
      case 'ACTIVE':
        return 'Active';
      case 'INACTIVE':
        return 'Inactive';
      case 'MAINTENANCE':
        return 'Under Maintenance';
      default:
        return status;
    }
  }

  bool get isActive {
    return status.toUpperCase() == 'ACTIVE';
  }

  String get vehicleCapacityText {
    List<String> capacities = [];
    
    if (capacityVehicleCar > 0) {
      capacities.add('$capacityVehicleCar Cars');
    }
    if (capacityVehicleMotorcycle > 0) {
      capacities.add('$capacityVehicleMotorcycle Motorcycles');
    }
    if (capacityVehicleTruck > 0) {
      capacities.add('$capacityVehicleTruck Trucks');
    }
    if (capacityVehicleBus > 0) {
      capacities.add('$capacityVehicleBus Buses');
    }
    
    return capacities.join(', ');
  }

  // For compatibility with old code
  int get capacity => capacityPassenger;
  int get carCapacity => capacityVehicleCar;
  int get motorcycleCapacity => capacityVehicleMotorcycle;
  int get truckCapacity => capacityVehicleTruck;
  int get busCapacity => capacityVehicleBus;
}