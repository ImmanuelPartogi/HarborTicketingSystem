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
      
      print('API Response for getFerries:');
      print('Response type: ${response.runtimeType}');
      if (response['data'] != null) {
        print('Data type: ${response['data'].runtimeType}');
      } else {
        print('Data is null in response');
        return [];
      }
      
      // Handle different response formats
      final dynamic data = response['data'];
      List<dynamic> ferriesData;
      
      if (data is Map<String, dynamic>) {
        print('Data is Map in getFerries with keys: ${data.keys.toList()}');
        // Jika data adalah Map, coba temukan array yang mungkin berisi data ferries
        if (data.containsKey('ferries')) {
          ferriesData = data['ferries'] as List<dynamic>;
        } else if (data.containsKey('items')) {
          ferriesData = data['items'] as List<dynamic>;
        } else {
          // Jika tidak ditemukan, buat list dengan satu item
          print('Converting Map to single-item List in getFerries');
          ferriesData = [data];
        }
      } else if (data is List<dynamic>) {
        // Format yang diharapkan
        print('Data is List in getFerries with length: ${data.length}');
        ferriesData = data;
      } else {
        // Jika format tak terduga, kembalikan list kosong
        print('Error: Unexpected data format in getFerries: ${data?.runtimeType}');
        return [];
      }
      
      try {
        final result = ferriesData.map((json) => Ferry.fromJson(json)).toList();
        print('Successfully parsed ${result.length} ferries');
        return result;
      } catch (e) {
        print('Error converting JSON to Ferry objects: $e');
        // Tampilkan data pertama untuk debug
        if (ferriesData.isNotEmpty) {
          print('First ferry data: ${ferriesData.first}');
        }
        return []; // Return empty list rather than throwing exception
      }
    } catch (e) {
      print('Error in getFerries: ${e.toString()}');
      return []; // Return empty list rather than throwing exception
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
      
      print('Fetching routes with params: $queryParams');
      final response = await _apiService.getRoutes(
        queryParams: queryParams.isNotEmpty ? queryParams : null,
      );
      
      print('API Response for getRoutes:');
      print('Response type: ${response.runtimeType}');
      print('Response keys: ${response.keys.toList()}');
      
      if (response['data'] != null) {
        print('Data type: ${response['data'].runtimeType}');
      } else {
        print('Data is null in response');
        return [];
      }
      
      // Handle different response formats
      final dynamic data = response['data'];
      List<dynamic> routesData;
      
      if (data is Map<String, dynamic>) {
        print('Data is Map in getRoutes with keys: ${data.keys.toList()}');
        // Jika data adalah Map, coba temukan array yang mungkin berisi data routes
        if (data.containsKey('routes')) {
          routesData = data['routes'] as List<dynamic>;
        } else if (data.containsKey('items')) {
          routesData = data['items'] as List<dynamic>;
        } else {
          // Jika tidak ditemukan, buat list dengan satu item
          print('Converting Map to single-item List in getRoutes');
          routesData = [data];
        }
      } else if (data is List<dynamic>) {
        // Format yang diharapkan
        print('Data is List in getRoutes with length: ${data.length}');
        routesData = data;
      } else {
        // Jika format tak terduga, kembalikan list kosong
        print('Error: Unexpected data format in getRoutes: ${data?.runtimeType}');
        return [];
      }
      
      try {
        if (routesData.isEmpty) {
          print('No routes data available');
          return [];
        }
        
        // Print sample of first route data to debug
        print('First route data sample: ${routesData.first}');
        
        final routes = <RouteModel>[];
        for (var i = 0; i < routesData.length; i++) {
          try {
            final route = RouteModel.fromJson(routesData[i]);
            routes.add(route);
          } catch (e) {
            print('Error parsing route at index $i: $e');
            print('Problematic route data: ${routesData[i]}');
            // Continue with next route instead of failing completely
          }
        }
        
        print('Successfully parsed ${routes.length} routes out of ${routesData.length}');
        return routes;
      } catch (e) {
        print('Error converting JSON to RouteModel objects: $e');
        return []; // Return empty list rather than throwing exception
      }
    } catch (e) {
      print('Error in getRoutes: ${e.toString()}');
      return []; // Return empty list rather than throwing exception
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
      
      print('API Response for getSchedules:');
      print('Response type: ${response.runtimeType}');
      if (response['data'] != null) {
        print('Data type: ${response['data'].runtimeType}');
      } else {
        print('Data is null in response');
        return [];
      }
      
      // Handle different response formats
      final dynamic data = response['data'];
      List<dynamic> schedulesData;
      
      if (data is Map<String, dynamic>) {
        print('Data is Map in getSchedules with keys: ${data.keys.toList()}');
        // Jika data adalah Map, coba temukan array yang mungkin berisi data schedules
        if (data.containsKey('schedules')) {
          schedulesData = data['schedules'] as List<dynamic>;
        } else if (data.containsKey('items')) {
          schedulesData = data['items'] as List<dynamic>;
        } else {
          // Jika tidak ditemukan, buat list dengan satu item
          print('Converting Map to single-item List in getSchedules');
          schedulesData = [data];
        }
      } else if (data is List<dynamic>) {
        // Format yang diharapkan
        print('Data is List in getSchedules with length: ${data.length}');
        schedulesData = data;
      } else {
        // Jika format tak terduga, kembalikan list kosong
        print('Error: Unexpected data format in getSchedules: ${data?.runtimeType}');
        return [];
      }
      
      try {
        final schedules = <Schedule>[];
        for (var i = 0; i < schedulesData.length; i++) {
          try {
            final schedule = Schedule.fromJson(schedulesData[i]);
            schedules.add(schedule);
          } catch (e) {
            print('Error parsing schedule at index $i: $e');
            // Continue with next schedule instead of failing completely
          }
        }
        
        print('Successfully parsed ${schedules.length} schedules out of ${schedulesData.length}');
        return schedules;
      } catch (e) {
        print('Error converting JSON to Schedule objects: $e');
        return []; // Return empty list rather than throwing exception
      }
    } catch (e) {
      print('Error in getSchedules: ${e.toString()}');
      return []; // Return empty list rather than throwing exception
    }
  }

  Future<Schedule?> getScheduleDetail(int id) async {
    try {
      final response = await _apiService.get('/api/schedules/$id');
      if (response['data'] == null) {
        print('Schedule data is null for id: $id');
        return null;
      }
      
      try {
        return Schedule.fromJson(response['data']);
      } catch (e) {
        print('Error converting JSON to Schedule object: $e');
        print('Schedule data: ${response['data']}');
        return null;
      }
    } catch (e) {
      print('Error in getScheduleDetail: ${e.toString()}');
      return null; // Return null rather than throwing exception
    }
  }

  // Utility methods
  List<String> getUniqueDeparturePorts(List<RouteModel> routes) {
    final Set<String> ports = {};
    for (var route in routes) {
      if (route.departurePort.isNotEmpty) {
        ports.add(route.departurePort);
      }
    }
    return ports.toList()..sort();
  }

  List<String> getUniqueArrivalPorts(List<RouteModel> routes, String departurePort) {
    final Set<String> ports = {};
    for (var route in routes) {
      if (route.departurePort == departurePort && route.arrivalPort.isNotEmpty) {
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
    if (schedules.isEmpty) {
      return schedules;
    }
    
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