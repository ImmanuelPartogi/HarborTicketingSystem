import 'package:flutter/material.dart';
import '../models/ferry_model.dart';
import '../models/route_model.dart';
import '../models/schedule_model.dart';
import '../services/api_service.dart';
import '../services/ferry_service.dart';
import '../services/storage_service.dart';

class FerryProvider extends ChangeNotifier {
  late ApiService _apiService;
  late FerryService _ferryService;

  List<Ferry> _ferries = [];
  List<RouteModel> _routes = [];
  List<Schedule> _schedules = [];
  Schedule? _selectedSchedule;

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

  // Getters
  List<Ferry> get ferries => _ferries;
  List<RouteModel> get routes => _routes;
  List<Schedule> get schedules => _schedules;
  Schedule? get selectedSchedule => _selectedSchedule;

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

  // Constructor menerima StorageService yang sudah diinisialisasi
  FerryProvider(StorageService storageService) {
    _apiService = ApiService(storageService);
    _ferryService = FerryService(_apiService);
  }

  // External initialization
  void initialize(ApiService apiService) {
    _apiService = apiService;
    _ferryService = FerryService(_apiService);
  }

  // Fetch ferries - Metode dimodifikasi untuk menghindari notifyListeners() di awal
  Future<void> fetchFerries({bool activeOnly = true, String? type}) async {
    // Hanya set loading state jika belum loading
    if (!_isLoadingFerries) {
      _isLoadingFerries = true;
      _ferryError = null;
      notifyListeners();
    }

    try {
      final result = await _ferryService.getFerries(
        activeOnly: activeOnly,
        type: type,
      );

      _ferries = result;
      _isLoadingFerries = false;
      notifyListeners();
    } catch (e) {
      _ferryError = 'Failed to load ferries: ${e.toString()}';
      _isLoadingFerries = false;
      notifyListeners();
    }
  }

  // Fetch routes - Metode dimodifikasi untuk menghindari notifyListeners() di awal
  Future<void> fetchRoutes({
    bool activeOnly = true,
    String? departurePort,
    String? arrivalPort,
  }) async {
    // Set loading state terlebih dahulu tanpa notifyListeners
    _isLoadingRoutes = true;
    _routeError = null;
    // TIDAK memanggil notifyListeners() di sini!

    try {
      final result = await _ferryService.getRoutes(
        activeOnly: activeOnly,
        departurePort: departurePort,
        arrivalPort: arrivalPort,
      );

      // Update state dan beri tahu listener setelah operasi async selesai
      _routes = result;
      _isLoadingRoutes = false;
      notifyListeners();
    } catch (e) {
      _routeError = 'Failed to load routes: ${e.toString()}';
      _isLoadingRoutes = false;
      notifyListeners();
    }
  }

  // Fetch schedules based on search criteria
  Future<void> fetchSchedules({
    required String departurePort,
    required String arrivalPort,
    required DateTime departureDate,
    int? ferryId,
    bool includeFullyBooked = false,
  }) async {
    _isLoadingSchedules = true;
    _scheduleError = null;
    _selectedDeparturePort = departurePort;
    _selectedArrivalPort = arrivalPort;
    _selectedDate = departureDate;
    notifyListeners();

    try {
      _schedules = await _ferryService.getSchedules(
        departurePort: departurePort,
        arrivalPort: arrivalPort,
        departureDate: departureDate,
        ferryId: ferryId,
        includeFullyBooked: includeFullyBooked,
      );

      // Sort schedules
      _sortSchedules();

      _isLoadingSchedules = false;
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
  Future<void> fetchScheduleDetail(int id) async {
    _isLoadingScheduleDetail = true;
    _scheduleError = null;
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
    _selectedSchedule = _schedules.firstWhere(
      (schedule) => schedule.id == scheduleId,
      orElse: () => null as Schedule,
    );

    if (_selectedSchedule == null) {
      fetchScheduleDetail(scheduleId);
    } else {
      notifyListeners();
    }
  }

  // Clear selected schedule
  void clearSelectedSchedule() {
    _selectedSchedule = null;
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
  List<Schedule> filterSchedulesByVehicleRequirements({
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
    notifyListeners();
  }

  // Helper method to save recent search
  void _saveRecentSearch(
    String departurePort,
    String arrivalPort,
    DateTime departureDate,
  ) async {
    // This would typically use the storage service, but we'll just notify listeners for now
    // as this is handled in the StorageService implementation
  }
}
