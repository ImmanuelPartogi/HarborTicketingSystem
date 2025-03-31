import 'dart:convert';
import 'package:intl/intl.dart';
import '../models/ferry_model.dart';
import '../models/route_model.dart';
import '../models/schedule_model.dart';
import '../config/api_config.dart';
import 'api_service.dart';

class FerryService {
  final ApiService _apiService;

  FerryService(this._apiService);

  // Get all ferries with optional filters
  Future<List<FerryModel>> getFerries({
    bool activeOnly = true,
    String? type,
  }) async {
    try {
      // Build query parameters
      final Map<String, dynamic> queryParams = {};
      if (activeOnly) {
        queryParams['status'] = 'ACTIVE';
      }
      if (type != null && type.isNotEmpty) {
        queryParams['type'] = type;
      }

      // Make the API request
      final response = await _apiService.get(
        '/api/v1/ferries',
        queryParams: queryParams,
      );

      // Parse response
      if (response.containsKey('success') && response['success'] == true) {
        final List<dynamic> ferriesJson = response['data']['ferries'];
        return ferriesJson.map((json) => FerryModel.fromJson(json)).toList();
      } else {
        throw Exception(
          'Failed to load ferries: ${response['message'] ?? 'Unknown error'}',
        );
      }
    } catch (e) {
      print('Error in getFerries: $e');
      rethrow;
    }
  }

  // Get all routes with optional filters
  Future<List<RouteModel>> getRoutes({
    bool activeOnly = true,
    String? departurePort,
    String? arrivalPort,
  }) async {
    try {
      // Build query parameters
      final Map<String, dynamic> queryParams = {};
      if (activeOnly) {
        queryParams['status'] = 'ACTIVE';
      }
      if (departurePort != null && departurePort.isNotEmpty) {
        queryParams['origin'] = departurePort;
      }
      if (arrivalPort != null && arrivalPort.isNotEmpty) {
        queryParams['destination'] = arrivalPort;
      }

      // Make the API request
      final response = await _apiService.get(
        '/api/v1/routes',
        queryParams: queryParams,
      );

      // Parse response - debug log response structure
      print('Routes API response structure: ${response.keys.toList()}');

      if (response.containsKey('success') && response['success'] == true) {
        List<dynamic> routesJson;
        
        // Handle different API response structures
        if (response['data'] is Map && response['data'].containsKey('routes')) {
          routesJson = response['data']['routes'];
        } else if (response['data'] is List) {
          routesJson = response['data'];
        } else {
          routesJson = [];
        }
        
        print('Extracted routes data: ${routesJson.length} items');
        return routesJson.map((json) => RouteModel.fromJson(json)).toList();
      } else {
        throw Exception(
          'Failed to load routes: ${response['message'] ?? 'Unknown error'}',
        );
      }
    } catch (e) {
      print('Error in getRoutes: $e');
      rethrow;
    }
  }

  // Get schedules based on search parameters
  Future<List<ScheduleModel>> getSchedules({
    required String departurePort,
    required String arrivalPort,
    required DateTime departureDate,
    int? ferryId,
    bool includeFullyBooked = false,
  }) async {
    try {
      // Format date to YYYY-MM-DD
      final formattedDate = DateFormat('yyyy-MM-dd').format(departureDate);

      // Build query parameters
      final Map<String, dynamic> queryParams = {
        'departure_port': departurePort,
        'arrival_port': arrivalPort,
        'departure_date': formattedDate,
        'status': 'ACTIVE',
      };

      if (ferryId != null) {
        queryParams['ferry_id'] = ferryId.toString();
      }

      // Make the API request
      final response = await _apiService.get(
        '/api/v1/schedules',
        queryParams: queryParams,
      );

      // Debug logging
      print('Schedules API response: ${response.keys.toList()}');

      // Parse response
      if (response.containsKey('success') && response['success'] == true) {
        List<dynamic> schedulesJson;
        
        // Handle different API response structures
        if (response['data'] is Map && response['data'].containsKey('schedules')) {
          schedulesJson = response['data']['schedules'];
        } else if (response['data'] is List) {
          schedulesJson = response['data'];
        } else {
          schedulesJson = [];
        }
        
        print('Extracted schedules data: ${schedulesJson.length} items');

        final schedules = <ScheduleModel>[];
        for (var json in schedulesJson) {
          try {
            // Skip schedules that are not available if includeFullyBooked is false
            if (!includeFullyBooked && 
                json.containsKey('is_available') && 
                json['is_available'] == false) {
              continue;
            }
            
            schedules.add(ScheduleModel.fromJson(json));
          } catch (e) {
            print('Error parsing schedule: $e');
          }
        }

        print('Successfully parsed ${schedules.length} schedules');
        return schedules;
      } else {
        throw Exception(
          'Failed to load schedules: ${response['message'] ?? 'Unknown error'}',
        );
      }
    } catch (e) {
      print('Error in getSchedules: $e');
      rethrow;
    }
  }

  // Get detailed information for a specific schedule
  Future<ScheduleModel> getScheduleDetail(int id) async {
    try {
      // Make the API request
      final response = await _apiService.get('/api/v1/schedules/$id');

      // Parse response
      if (response.containsKey('success') && response['success'] == true) {
        Map<String, dynamic> scheduleJson;
        
        // Handle different API response structures
        if (response['data'] is Map && response['data'].containsKey('schedule')) {
          scheduleJson = response['data']['schedule'];
        } else {
          scheduleJson = response['data'];
        }
        
        return ScheduleModel.fromJson(scheduleJson);
      } else {
        throw Exception(
          'Failed to load schedule details: ${response['message'] ?? 'Unknown error'}',
        );
      }
    } catch (e) {
      print('Error in getScheduleDetail: $e');
      rethrow;
    }
  }

  // Sort schedules based on given criteria
  List<ScheduleModel> sortSchedules(
    List<ScheduleModel> schedules,
    String sortBy,
  ) {
    final sortedSchedules = List<ScheduleModel>.from(schedules);

    switch (sortBy) {
      case 'departure_time_asc':
        sortedSchedules.sort(
          (a, b) => a.departureTime.compareTo(b.departureTime),
        );
        break;
      case 'departure_time_desc':
        sortedSchedules.sort(
          (a, b) => b.departureTime.compareTo(a.departureTime),
        );
        break;
      case 'price_asc':
        sortedSchedules.sort((a, b) => a.price.compareTo(b.price));
        break;
      case 'price_desc':
        sortedSchedules.sort((a, b) => b.price.compareTo(a.price));
        break;
      default:
        // Default to departure time ascending
        sortedSchedules.sort(
          (a, b) => a.departureTime.compareTo(b.departureTime),
        );
    }

    return sortedSchedules;
  }

  // Filter schedules by vehicle requirements
  List<ScheduleModel> filterSchedulesByVehicleRequirements(
    List<ScheduleModel> schedules, {
    int? carsNeeded,
    int? motorcyclesNeeded,
    int? busesNeeded,
    int? trucksNeeded,
  }) {
    return schedules.where((schedule) {
      // If no requirements, include all schedules
      if (carsNeeded == null &&
          motorcyclesNeeded == null &&
          busesNeeded == null &&
          trucksNeeded == null) {
        return true;
      }

      // Check if schedule meets all vehicle requirements
      bool meetsRequirements = true;

      if (carsNeeded != null && carsNeeded > 0) {
        meetsRequirements =
            meetsRequirements && schedule.availableCars >= carsNeeded;
      }

      if (motorcyclesNeeded != null && motorcyclesNeeded > 0) {
        meetsRequirements =
            meetsRequirements &&
            schedule.availableMotorcycles >= motorcyclesNeeded;
      }

      if (busesNeeded != null && busesNeeded > 0) {
        meetsRequirements =
            meetsRequirements && schedule.availableBuses >= busesNeeded;
      }

      if (trucksNeeded != null && trucksNeeded > 0) {
        meetsRequirements =
            meetsRequirements && schedule.availableTrucks >= trucksNeeded;
      }

      return meetsRequirements;
    }).toList();
  }

  // Get unique departure ports from routes
  List<String> getUniqueDeparturePorts(List<RouteModel> routes) {
    final Set<String> uniquePorts = {};

    for (var route in routes) {
      uniquePorts.add(route.origin);
    }

    final List<String> sortedPorts = uniquePorts.toList()..sort();
    return sortedPorts;
  }

  // Get unique arrival ports for a given departure port
  List<String> getUniqueArrivalPorts(
    List<RouteModel> routes,
    String departurePort,
  ) {
    final Set<String> uniquePorts = {};

    for (var route in routes) {
      if (route.origin == departurePort) {
        uniquePorts.add(route.destination);
      }
    }

    final List<String> sortedPorts = uniquePorts.toList()..sort();
    return sortedPorts;
  }
}