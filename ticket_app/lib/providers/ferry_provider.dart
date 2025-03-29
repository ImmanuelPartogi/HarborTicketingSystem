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

  @override
  void dispose() {
    super.dispose();
  }

  // Fetch ferries dengan throttling sederhana
  Future<void> fetchFerries({
    bool activeOnly = true, 
    String? type, 
    bool forceRefresh = false
  }) async {
    // Cek jika sudah di-initialized dan tidak diminta force refresh
    if (!forceRefresh && _ferriesInitialized && _ferries.isNotEmpty) {
      debugPrint('SKIP FETCH: Ferries already initialized');
      return;
    }
    
    if (_isLoadingFerries) {
      debugPrint('SKIP FETCH: Ferries already loading');
      return;
    }
    
    // Throttling sederhana - minimal jeda 30 detik antara request
    if (!forceRefresh && _lastFerriesRequest != null && 
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

  // Fetch routes dengan throttling sederhana
  Future<void> fetchRoutes({
    bool activeOnly = true,
    String? departurePort,
    String? arrivalPort,
    bool forceRefresh = false,
  }) async {
    // Cek jika sudah di-initialized dan tidak diminta force refresh
    if (!forceRefresh && _routesInitialized && _routes.isNotEmpty) {
      debugPrint('SKIP FETCH: Routes already initialized');
      return;
    }
    
    if (_isLoadingRoutes) {
      debugPrint('SKIP FETCH: Routes already loading');
      return;
    }
    
    // Throttling sederhana - minimal jeda 30 detik antara request
    if (!forceRefresh && _lastRoutesRequest != null && 
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

  // Fetch schedules dengan throttling sederhana
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

    // Jika data sudah ada dan sama, dan tidak diminta untuk force refresh, gunakan cache
    bool sameSearch = _selectedDeparturePort == departurePort && 
                      _selectedArrivalPort == arrivalPort && 
                      _selectedDate?.day == departureDate.day &&
                      _selectedDate?.month == departureDate.month &&
                      _selectedDate?.year == departureDate.year;
                      
    if (!forceRefresh && sameSearch && _schedulesInitialized && _schedules.isNotEmpty) {
      debugPrint('SKIP FETCH: Schedules already initialized with same search parameters');
      return;
    }
    
    // Throttling sederhana - minimal jeda 30 detik antara request
    if (!forceRefresh && _lastSchedulesRequest != null && 
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

  // Fetch schedule detail
  Future<void> fetchScheduleDetail(int id, {bool forceRefresh = false}) async {
    if (_isLoadingScheduleDetail) {
      debugPrint('SKIP FETCH: Schedule detail already loading');
      return;
    }

    // Skip fetch jika schedule detail sudah ada
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
    ScheduleModel? schedule = _schedules.firstWhere(
      (s) => s.id == scheduleId,
      orElse: () => null as ScheduleModel,
    );
    
    if (schedule != null) {
      _selectedSchedule = schedule;
      notifyListeners();
    } else {
      // If not found in schedules list, fetch from API
      fetchScheduleDetail(scheduleId);
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
  }
}