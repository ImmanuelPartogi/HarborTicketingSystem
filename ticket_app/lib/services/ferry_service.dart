import '../models/ferry_model.dart';
import '../models/route_model.dart';
import '../models/schedule_model.dart';
import 'api_service.dart';
import 'package:intl/intl.dart';

class FerryService {
  final ApiService _apiService;

  FerryService(this._apiService);

  Future<List<Ferry>> getFerries({
    bool activeOnly = true,
    String? type,
  }) async {
    try {
      final Map<String, dynamic> queryParams = {};
      
      if (activeOnly) {
        queryParams['active_only'] = '1';
      }
      
      if (type != null) {
        queryParams['type'] = type;
      }
      
      final response = await _apiService.getFerries(
        queryParams: queryParams.isNotEmpty ? queryParams : null,
      );
      
      final List<dynamic> ferriesData = response['data'];
      return ferriesData.map((json) => Ferry.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to fetch ferries: ${e.toString()}');
    }
  }

  Future<List<RouteModel>> getRoutes({
    bool activeOnly = true,
    String? departurePort,
    String? arrivalPort,
  }) async {
    try {
      final Map<String, dynamic> queryParams = {};
      
      if (activeOnly) {
        queryParams['active_only'] = '1';
      }
      
      if (departurePort != null) {
        queryParams['departure_port'] = departurePort;
      }
      
      if (arrivalPort != null) {
        queryParams['arrival_port'] = arrivalPort;
      }
      
      final response = await _apiService.getRoutes(
        queryParams: queryParams.isNotEmpty ? queryParams : null,
      );
      
      final List<dynamic> routesData = response['data'];
      return routesData.map((json) => RouteModel.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to fetch routes: ${e.toString()}');
    }
  }

  Future<List<Schedule>> getSchedules({
    required String departurePort,
    required String arrivalPort,
    required DateTime departureDate,
    int? ferryId,
    bool includeFullyBooked = false,
  }) async {
    try {
      final Map<String, dynamic> queryParams = {
        'departure_port': departurePort,
        'arrival_port': arrivalPort,
        'departure_date': DateFormat('yyyy-MM-dd').format(departureDate),
      };
      
      if (ferryId != null) {
        queryParams['ferry_id'] = ferryId.toString();
      }
      
      if (includeFullyBooked) {
        queryParams['include_fully_booked'] = '1';
      }
      
      final response = await _apiService.getSchedules(queryParams: queryParams);
      
      final List<dynamic> schedulesData = response['data'];
      return schedulesData.map((json) => Schedule.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to fetch schedules: ${e.toString()}');
    }
  }

  Future<Schedule> getScheduleDetail(int id) async {
    try {
      final response = await _apiService.get('/api/schedules/$id');
      return Schedule.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to fetch schedule details: ${e.toString()}');
    }
  }

  // Utility methods
  List<String> getUniqueDeparturePorts(List<RouteModel> routes) {
    final Set<String> ports = {};
    for (var route in routes) {
      ports.add(route.departurePort);
    }
    return ports.toList()..sort();
  }

  List<String> getUniqueArrivalPorts(List<RouteModel> routes, String departurePort) {
    final Set<String> ports = {};
    for (var route in routes) {
      if (route.departurePort == departurePort) {
        ports.add(route.arrivalPort);
      }
    }
    return ports.toList()..sort();
  }

  // Filter schedules by vehicle capacity requirements
  List<Schedule> filterSchedulesByVehicleRequirements(
    List<Schedule> schedules, {
    int? carsNeeded = 0,
    int? motorcyclesNeeded = 0,
    int? busesNeeded = 0,
    int? trucksNeeded = 0,
  }) {
    return schedules.where((schedule) {
      bool hasCapacity = true;
      
      if (carsNeeded != null && carsNeeded > 0) {
        hasCapacity = hasCapacity && schedule.availableCars >= carsNeeded;
      }
      
      if (motorcyclesNeeded != null && motorcyclesNeeded > 0) {
        hasCapacity = hasCapacity && schedule.availableMotorcycles >= motorcyclesNeeded;
      }
      
      if (busesNeeded != null && busesNeeded > 0) {
        hasCapacity = hasCapacity && schedule.availableBuses >= busesNeeded;
      }
      
      if (trucksNeeded != null && trucksNeeded > 0) {
        hasCapacity = hasCapacity && schedule.availableTrucks >= trucksNeeded;
      }
      
      return hasCapacity;
    }).toList();
  }

  // Sort schedules by different criteria
  List<Schedule> sortSchedules(List<Schedule> schedules, String sortBy) {
    switch (sortBy) {
      case 'departure_time_asc':
        schedules.sort((a, b) => a.departureTime.compareTo(b.departureTime));
        break;
      case 'departure_time_desc':
        schedules.sort((a, b) => b.departureTime.compareTo(a.departureTime));
        break;
      case 'price_asc':
        schedules.sort((a, b) => a.finalPrice.compareTo(b.finalPrice));
        break;
      case 'price_desc':
        schedules.sort((a, b) => b.finalPrice.compareTo(a.finalPrice));
        break;
      default:
        schedules.sort((a, b) => a.departureTime.compareTo(b.departureTime));
    }
    
    return schedules;
  }
}