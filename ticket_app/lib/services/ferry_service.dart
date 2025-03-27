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
        queryParams['active_only'] = '1';
      }
      if (type != null && type.isNotEmpty) {
        queryParams['type'] = type;
      }

      // Use the correct endpoint path from ApiConfig
      final response = await _apiService.get(
        ApiConfig.ferries,
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
        queryParams['active_only'] = '1';
      }
      if (departurePort != null && departurePort.isNotEmpty) {
        queryParams['origin'] = departurePort;
      }
      if (arrivalPort != null && arrivalPort.isNotEmpty) {
        queryParams['destination'] = arrivalPort;
      }

      // Use the correct endpoint path from ApiConfig
      final response = await _apiService.get(
        ApiConfig.routes,
        queryParams: queryParams,
      );

      // Parse response
      if (response.containsKey('success') && response['success'] == true) {
        final List<dynamic> routesJson = response['data']['routes'];
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
      };

      if (ferryId != null) {
        queryParams['ferry_id'] = ferryId.toString();
      }

      if (includeFullyBooked) {
        queryParams['include_fully_booked'] = '1';
      }

      // Use the correct endpoint path from ApiConfig
      final response = await _apiService.get(
        ApiConfig.schedules,
        queryParams: queryParams,
      );

      // Parse response
      if (response.containsKey('success') && response['success'] == true) {
        final List<dynamic> schedulesJson = response['data']['schedules'];
        print('Parsed schedules JSON: $schedulesJson');

        final schedules = <ScheduleModel>[];
        for (var json in schedulesJson) {
          try {
            schedules.add(ScheduleModel.fromJson(json));
          } catch (e) {
            print(
              'Error parsing schedule at index ${schedulesJson.indexOf(json)}: $e',
            );
          }
        }

        print(
          'Successfully parsed ${schedules.length} schedules out of ${schedulesJson.length}',
        );
        return schedules;
      } else {
        throw Exception(
          'Failed to load schedules: ${response['message'] ?? 'Unknown error'}',
        );
      }
    } catch (e) {
      print('Error in getSchedules: Exception: $e');
      throw Exception('Unexpected error: $e');
    }
  }

  // Get detailed information for a specific schedule
  Future<ScheduleModel> getScheduleDetail(int id) async {
    try {
      // Use the correct path for getting a specific schedule
      // Don't build the endpoint manually - use the correct string format with interpolation
      // Also ensure we're using the API endpoint, not the web admin endpoint
      final endpoint =
          '${ApiConfig.schedules.substring(0, ApiConfig.schedules.length)}/$id';

      // Make API request
      final response = await _apiService.get(endpoint);

      // Parse response
      if (response.containsKey('success') && response['success'] == true) {
        final Map<String, dynamic> scheduleJson = response['data']['schedule'];
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
      // Use correct property to match RouteModel
      uniquePorts.add(route.departure);
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
      // Use correct properties to match RouteModel
      if (route.departure == departurePort) {
        uniquePorts.add(route.arrival);
      }
    }

    final List<String> sortedPorts = uniquePorts.toList()..sort();
    return sortedPorts;
  }
}
