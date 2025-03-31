import 'package:flutter/material.dart';
import 'dart:async';
import '../models/ferry_model.dart';
import '../models/route_model.dart';
import '../models/schedule_model.dart';
import '../services/api_service.dart';
import '../services/ferry_service.dart';
import '../services/storage_service.dart';

class FerryProvider extends ChangeNotifier {
  late ApiService _apiService;
  late FerryService _ferryService;

  List<FerryModel> _ferries = [];
  List<RouteModel> _routes = [];
  List<ScheduleModel> _schedules = [];
  ScheduleModel? _selectedSchedule;

  bool _isInitialized = false;
  bool _isLoadingFerries = false;
  bool _isLoadingRoutes = false;
  bool _isLoadingSchedules = false;
  bool _isLoadingScheduleDetail = false;

  String? _ferryError;
  String? _routeError;
  String? _scheduleError;

  // Search parameters
  String? _selectedDeparturePort;
  String? _selectedArrivalPort;
  DateTime? _selectedDate;
  String _sortBy = 'departure_time_asc';

  // Throttling parameters
  DateTime? _lastRoutesRequest;
  DateTime? _lastFerriesRequest;
  DateTime? _lastSchedulesRequest;

  // Flag untuk mencegah multiple fetch
  bool _routesInitialized = false;
  bool _ferriesInitialized = false;
  bool _schedulesInitialized = false;

  // Getters
  List<FerryModel> get ferries => _ferries;
  List<RouteModel> get routes => _routes;
  List<ScheduleModel> get schedules => _schedules;
  ScheduleModel? get selectedSchedule => _selectedSchedule;

  bool get isLoadingFerries => _isLoadingFerries;
  bool get isLoadingRoutes => _isLoadingRoutes;
  bool get isLoadingSchedules => _isLoadingSchedules;
  bool get isLoadingScheduleDetail => _isLoadingScheduleDetail;

  String? get ferryError => _ferryError;
  String? get routeError => _routeError;
  String? get scheduleError => _scheduleError;

  String? get selectedDeparturePort => _selectedDeparturePort;
  String? get selectedArrivalPort => _selectedArrivalPort;
  DateTime? get selectedDate => _selectedDate;
  String get sortBy => _sortBy;

  // Constructor
  FerryProvider(StorageService storageService) {
    _apiService = ApiService(storageService);
    _ferryService = FerryService(_apiService);
  }

  FerryService getFerryService() {
    return _ferryService;
  }

  @override
  void dispose() {
    super.dispose();
  }

  // Fetch ferries with throttling
  Future<void> fetchFerries({
    bool activeOnly = true,
    String? type,
    bool forceRefresh = false,
  }) async {
    // Skip if already initialized and no force refresh
    if (!forceRefresh && _ferriesInitialized && _ferries.isNotEmpty) {
      debugPrint('SKIP FETCH: Ferries already initialized');
      return;
    }

    if (_isLoadingFerries) {
      debugPrint('SKIP FETCH: Ferries already loading');
      return;
    }

    // Throttling - minimum 30 second interval between requests
    if (!forceRefresh &&
        _lastFerriesRequest != null &&
        DateTime.now().difference(_lastFerriesRequest!).inSeconds < 30) {
      debugPrint('THROTTLED: Please wait before refreshing ferries again');
      return;
    }

    _isLoadingFerries = true;
    _ferryError = null;
    notifyListeners();

    try {
      final result = await _ferryService.getFerries(
        activeOnly: activeOnly,
        type: type,
      );

      _ferries = result;
      _isLoadingFerries = false;
      _ferriesInitialized = true;
      _lastFerriesRequest = DateTime.now();
      notifyListeners();
    } catch (e) {
      _ferryError = 'Failed to load ferries: ${e.toString()}';
      _isLoadingFerries = false;
      notifyListeners();
    }
  }

  // Fetch routes with throttling
  Future<void> fetchRoutes({
    bool activeOnly = true,
    String? departurePort,
    String? arrivalPort,
    bool forceRefresh = false,
  }) async {
    // Skip if already initialized and no force refresh
    if (!forceRefresh && _routesInitialized && _routes.isNotEmpty) {
      debugPrint('SKIP FETCH: Routes already initialized');
      return;
    }

    if (_isLoadingRoutes) {
      debugPrint('SKIP FETCH: Routes already loading');
      return;
    }

    // Throttling - minimum 30 second interval between requests
    if (!forceRefresh &&
        _lastRoutesRequest != null &&
        DateTime.now().difference(_lastRoutesRequest!).inSeconds < 30) {
      debugPrint('THROTTLED: Please wait before refreshing routes again');
      return;
    }

    _isLoadingRoutes = true;
    _routeError = null;
    notifyListeners();

    try {
      final result = await _ferryService.getRoutes(
        activeOnly: activeOnly,
        departurePort: departurePort,
        arrivalPort: arrivalPort,
      );

      // Log for debugging
      print('Fetched ${result.length} routes');
      if (result.isNotEmpty) {
        print(
          'Sample route: origin=${result[0].origin}, destination=${result[0].destination}',
        );
      } else {
        print('No routes fetched from API');
      }

      _routes = result;
      _isLoadingRoutes = false;
      _routesInitialized = true;
      _lastRoutesRequest = DateTime.now();
      notifyListeners();
    } catch (e) {
      _routeError = 'Failed to load routes: ${e.toString()}';
      _isLoadingRoutes = false;
      notifyListeners();
    }
  }

  // Fetch schedules with throttling
  Future<void> fetchSchedules({
    required String departurePort,
    required String arrivalPort,
    required DateTime departureDate,
    int? ferryId,
    bool includeFullyBooked = false,
    bool forceRefresh = false,
  }) async {
    if (_isLoadingSchedules) {
      debugPrint('SKIP FETCH: Schedules already loading');
      return;
    }

    // If data exists and is the same, use cache unless force refresh
    bool sameSearch =
        _selectedDeparturePort == departurePort &&
        _selectedArrivalPort == arrivalPort &&
        _selectedDate?.day == departureDate.day &&
        _selectedDate?.month == departureDate.month &&
        _selectedDate?.year == departureDate.year;

    if (!forceRefresh &&
        sameSearch &&
        _schedulesInitialized &&
        _schedules.isNotEmpty) {
      debugPrint(
        'SKIP FETCH: Schedules already initialized with same search parameters',
      );
      return;
    }

    // Throttling - minimum 30 second interval between requests
    if (!forceRefresh &&
        _lastSchedulesRequest != null &&
        DateTime.now().difference(_lastSchedulesRequest!).inSeconds < 30) {
      debugPrint('THROTTLED: Please wait before refreshing schedules again');
      return;
    }

    _isLoadingSchedules = true;
    _scheduleError = null;
    _selectedDeparturePort = departurePort;
    _selectedArrivalPort = arrivalPort;
    _selectedDate = departureDate;
    notifyListeners();

    try {
      final result = await _ferryService.getSchedules(
        departurePort: departurePort,
        arrivalPort: arrivalPort,
        departureDate: departureDate,
        ferryId: ferryId,
        includeFullyBooked: includeFullyBooked,
      );

      _schedules = result;
      _sortSchedules();
      _isLoadingSchedules = false;
      _schedulesInitialized = true;
      _lastSchedulesRequest = DateTime.now();
      notifyListeners();

      // Save recent search to storage
      _saveRecentSearch(departurePort, arrivalPort, departureDate);
    } catch (e) {
      _scheduleError = 'Failed to load schedules: ${e.toString()}';
      _isLoadingSchedules = false;
      notifyListeners();
    }
  }

  // NEW: Fetch popular schedules for the home screen
  Future<void> fetchPopularSchedules({bool forceRefresh = false}) async {
    if (_isLoadingSchedules && !forceRefresh) {
      debugPrint('SKIP FETCH: Popular schedules already loading');
      return;
    }

    // If schedules exist and not forced, use cache
    if (!forceRefresh && _schedulesInitialized && _schedules.isNotEmpty) {
      debugPrint('SKIP FETCH: Using cached schedules');
      return;
    }

    // Reset state jika forceRefresh = true
    if (forceRefresh) {
      _schedulesInitialized = false;
      _schedules =
          []; // Hapus data sebelumnya untuk memastikan data baru dimuat
    }

    _isLoadingSchedules = true;
    _scheduleError = null;
    notifyListeners();

    try {
      // Pastikan routes berhasil dimuat
      if (!_routesInitialized || _routes.isEmpty) {
        await fetchRoutes(activeOnly: true);
      }

      // Get all active routes
      final availableRoutes =
          _routes
              .where((route) => route.status.toUpperCase() == 'ACTIVE')
              .toList();

      if (availableRoutes.isEmpty) {
        _scheduleError = 'No routes available to search for schedules';
        _isLoadingSchedules = false;
        notifyListeners();
        return;
      }

      // Variabel untuk menyimpan jadwal yang ditemukan
      List<ScheduleModel> result = [];

      // PRIORITAS RUTE: Cari rute Ajibata → Ambarita terlebih dahulu
      // Berdasarkan logs, ini rute yang diketahui memiliki jadwal
      final today = DateTime.now();

      try {
        debugPrint('PRIORITY: Trying Ajibata to Ambarita route first');

        // Cari rute Ajibata → Ambarita yang diketahui memiliki jadwal
        final ambaritaRoute = availableRoutes.firstWhere(
          (route) =>
              route.origin == 'Ajibata' && route.destination == 'Ambarita',
          orElse: () => null as RouteModel, // Fallback jika tidak ditemukan
        );

        if (ambaritaRoute != null) {
          result = await _ferryService.getSchedules(
            departurePort: 'Ajibata',
            arrivalPort: 'Ambarita',
            departureDate: today,
            includeFullyBooked: false,
          );

          debugPrint(
            'PRIORITY CHECK: Found ${result.length} schedules for Ajibata to Ambarita',
          );
        }
      } catch (e) {
        debugPrint('Error in priority route: ${e.toString()}');
      }

      // Jika tidak ditemukan, coba rute lain
      if (result.isEmpty) {
        // Coba rute populer lainnya
        final popularRoutes = [
          {'origin': 'Ambarita', 'destination': 'Ajibata'},
          {'origin': 'Tomok', 'destination': 'Ajibata'},
          {'origin': 'Ajibata', 'destination': 'Tomok'},
        ];

        for (var routeData in popularRoutes) {
          try {
            debugPrint(
              'Trying route ${routeData['origin']} to ${routeData['destination']}',
            );

            final schedules = await _ferryService.getSchedules(
              departurePort: routeData['origin']!,
              arrivalPort: routeData['destination']!,
              departureDate: today,
              includeFullyBooked: false,
            );

            if (schedules.isNotEmpty) {
              debugPrint('Found ${schedules.length} schedules!');
              result = schedules;
              break;
            }
          } catch (e) {
            debugPrint('Error trying route: ${e.toString()}');
            continue;
          }
        }
      }

      // Jika masih tidak ditemukan, coba semua rute yang tersedia
      if (result.isEmpty) {
        debugPrint('Trying all available routes as last resort');

        for (var route in availableRoutes) {
          try {
            final schedules = await _ferryService.getSchedules(
              departurePort: route.origin,
              arrivalPort: route.destination,
              departureDate: today,
              includeFullyBooked: true, // Include fully booked as last resort
            );

            if (schedules.isNotEmpty) {
              debugPrint(
                'Found ${schedules.length} schedules for ${route.routeName}',
              );
              result = schedules;
              break;
            }
          } catch (e) {
            continue;
          }
        }
      }

      // Update state dengan jadwal yang ditemukan
      _schedules = result;
      _sortSchedules();
      _isLoadingSchedules = false;
      _schedulesInitialized = true;
      _lastSchedulesRequest = DateTime.now();

      debugPrint('FINAL: Found ${_schedules.length} schedules');
      notifyListeners();
    } catch (e) {
      _scheduleError = 'Failed to load popular schedules: ${e.toString()}';
      _isLoadingSchedules = false;
      notifyListeners();
    }
  }

  // Get schedule detail
  Future<void> fetchScheduleDetail(int id, {bool forceRefresh = false}) async {
    if (_isLoadingScheduleDetail) {
      debugPrint('SKIP FETCH: Schedule detail already loading');
      return;
    }

    // Skip fetch if schedule detail already exists
    if (!forceRefresh && _selectedSchedule?.id == id) {
      debugPrint('SKIP FETCH: Selected schedule already loaded (id: $id)');
      return;
    }

    _isLoadingScheduleDetail = true;
    notifyListeners();

    try {
      final schedule = await _ferryService.getScheduleDetail(id);
      _selectedSchedule = schedule;
      _isLoadingScheduleDetail = false;
      notifyListeners();
    } catch (e) {
      _scheduleError = 'Failed to load schedule details: ${e.toString()}';
      _isLoadingScheduleDetail = false;
      notifyListeners();
    }
  }

  // Set selected schedule from the list
  void setSelectedSchedule(int scheduleId) {
    // Try to find in existing schedules first
    final schedule = _schedules.firstWhere(
      (s) => s.id == scheduleId,
      orElse: () => throw Exception('Schedule not found'),
    );

    _selectedSchedule = schedule;
    notifyListeners();

    // Also fetch full details to ensure we have complete data
    fetchScheduleDetail(scheduleId);
  }

  void updateSchedulesDirectly(List<ScheduleModel> schedules) {
    if (schedules.isNotEmpty) {
      _schedules = schedules;
      _sortSchedules();
      _schedulesInitialized = true;
      _lastSchedulesRequest = DateTime.now();
      debugPrint('Direct update: ${_schedules.length} schedules added');
      notifyListeners();
    }
  }

  // Clear selected schedule
  void clearSelectedSchedule() {
    _selectedSchedule = null;
    notifyListeners();
  }

  // Reset state (usually called on logout)
  void reset() {
    _routes = [];
    _ferries = [];
    _schedules = [];
    _selectedSchedule = null;
    _routesInitialized = false;
    _ferriesInitialized = false;
    _schedulesInitialized = false;
    _ferryError = null;
    _routeError = null;
    _scheduleError = null;
    notifyListeners();
  }

  // Set sort criteria and re-sort schedules
  void setSortBy(String sortBy) {
    _sortBy = sortBy;
    _sortSchedules();
    notifyListeners();
  }

  // Sort schedules based on the current sort criteria
  void _sortSchedules() {
    _schedules = _ferryService.sortSchedules(_schedules, _sortBy);
  }

  // Filter schedules by vehicle requirements
  List<ScheduleModel> filterSchedulesByVehicleRequirements({
    int? carsNeeded,
    int? motorcyclesNeeded,
    int? busesNeeded,
    int? trucksNeeded,
  }) {
    return _ferryService.filterSchedulesByVehicleRequirements(
      _schedules,
      carsNeeded: carsNeeded,
      motorcyclesNeeded: motorcyclesNeeded,
      busesNeeded: busesNeeded,
      trucksNeeded: trucksNeeded,
    );
  }

  // Get unique departure ports from routes
  List<String> getUniqueDeparturePorts() {
    return _ferryService.getUniqueDeparturePorts(_routes);
  }

  // Get unique arrival ports for a given departure port
  List<String> getUniqueArrivalPorts(String departurePort) {
    return _ferryService.getUniqueArrivalPorts(_routes, departurePort);
  }

  // Clear schedules
  void clearSchedules() {
    _schedules = [];
    _selectedSchedule = null;
    _schedulesInitialized = false;
    notifyListeners();
  }

  // Helper method to save recent search
  void _saveRecentSearch(
    String departurePort,
    String arrivalPort,
    DateTime departureDate,
  ) async {
    // This would typically use the storage service
    // Implement if needed
  }
}
