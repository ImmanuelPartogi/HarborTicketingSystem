class RouteModel {
  final int id;
  final String origin;      // This field comes from the API
  final String destination; // This field comes from the API
  final double distance;
  final int duration;
  final double basePrice;
  final double motorcyclePrice;
  final double carPrice;
  final double busPrice;
  final double truckPrice;
  final String status;
  final String? statusReason;
  final DateTime? statusUpdatedAt;
  final DateTime? statusExpiryDate;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  // Added these getters for compatibility with FerryService
  String get departure => origin;
  String get arrival => destination;
  
  // Combine the origin/destination for display
  String get routeName => '$origin → $destination';
  
  // Alias for compatibility
  String get departurePort => origin;
  String get arrivalPort => destination;

  RouteModel({
    required this.id,
    required this.origin,
    required this.destination,
    required this.distance,
    required this.duration,
    required this.basePrice,
    required this.motorcyclePrice,
    required this.carPrice,
    required this.busPrice,
    required this.truckPrice,
    required this.status,
    this.statusReason,
    this.statusUpdatedAt,
    this.statusExpiryDate,
    this.createdAt,
    this.updatedAt,
  });

  factory RouteModel.fromJson(Map<String, dynamic> json) {
    try {
      return RouteModel(
        id: json['id'] ?? 0,
        origin: json['origin'] ?? '',
        destination: json['destination'] ?? '',
        distance: double.tryParse(json['distance'].toString()) ?? 0.0,
        duration: json['duration'] ?? 0,
        basePrice: double.tryParse(json['base_price'].toString()) ?? 0.0,
        motorcyclePrice: double.tryParse(json['motorcycle_price'].toString()) ?? 0.0,
        carPrice: double.tryParse(json['car_price'].toString()) ?? 0.0,
        busPrice: double.tryParse(json['bus_price'].toString()) ?? 0.0,
        truckPrice: double.tryParse(json['truck_price'].toString()) ?? 0.0,
        status: json['status'] ?? 'INACTIVE',
        statusReason: json['status_reason'],
        statusUpdatedAt: json['status_updated_at'] != null ? DateTime.parse(json['status_updated_at']) : null,
        statusExpiryDate: json['status_expiry_date'] != null ? DateTime.parse(json['status_expiry_date']) : null,
        createdAt: json['created_at'] != null ? DateTime.parse(json['created_at']) : null,
        updatedAt: json['updated_at'] != null ? DateTime.parse(json['updated_at']) : null,
      );
    } catch (e) {
      print('Error parsing route model: $e');
      print('JSON: $json');
      rethrow;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'origin': origin,
      'destination': destination,
      'distance': distance,
      'duration': duration,
      'base_price': basePrice,
      'motorcycle_price': motorcyclePrice,
      'car_price': carPrice,
      'bus_price': busPrice,
      'truck_price': truckPrice,
      'status': status,
      'status_reason': statusReason,
      'status_updated_at': statusUpdatedAt?.toIso8601String(),
      'status_expiry_date': statusExpiryDate?.toIso8601String(),
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  String get formattedDuration {
    final hours = duration ~/ 60;
    final minutes = duration % 60;
    
    if (hours > 0) {
      return '$hours h ${minutes > 0 ? '$minutes m' : ''}';
    } else {
      return '$minutes m';
    }
  }

  bool get isActive {
    return status.toUpperCase() == 'ACTIVE';
  }
}