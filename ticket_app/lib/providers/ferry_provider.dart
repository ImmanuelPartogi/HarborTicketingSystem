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

  // List of ongoing request cancellation tokens
  final List<Completer> _cancellationTokens = [];

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
    // Cancel all active requests
    for (var token in _cancellationTokens) {
      if (!token.isCompleted) {
        token.complete();
      }
    }
    _cancellationTokens.clear();
    super.dispose();
  }

  // Fetch ferries
  Future<void> fetchFerries({bool activeOnly = true, String? type}) async {
    if (_isLoadingFerries) return; // Prevent duplicate requests

    _isLoadingFerries = true;
    _ferryError = null;
    notifyListeners();

    final cancelToken = Completer();
    _cancellationTokens.add(cancelToken);

    try {
      final result = await _ferryService.getFerries(
        activeOnly: activeOnly,
        type: type,
      );

      if (!cancelToken.isCompleted) {
        _ferries = result;
        _isLoadingFerries = false;
        notifyListeners();
      }
    } catch (e) {
      if (!cancelToken.isCompleted) {
        _ferryError = 'Failed to load ferries: ${e.toString()}';
        _isLoadingFerries = false;
        notifyListeners();
      }
    } finally {
      _cancellationTokens.remove(cancelToken);
    }
  }

  // Fetch routes
  Future<void> fetchRoutes({
    bool activeOnly = true,
    String? departurePort,
    String? arrivalPort,
  }) async {
    if (_isLoadingRoutes) return; // Prevent duplicate requests

    _isLoadingRoutes = true;
    _routeError = null;
    notifyListeners();

    final cancelToken = Completer();
    _cancellationTokens.add(cancelToken);

    try {
      final result = await _ferryService.getRoutes(
        activeOnly: activeOnly,
        departurePort: departurePort,
        arrivalPort: arrivalPort,
      );

      if (!cancelToken.isCompleted) {
        _routes = result;
        _isLoadingRoutes = false;
        notifyListeners();
      }
    } catch (e) {
      if (!cancelToken.isCompleted) {
        _routeError = 'Failed to load routes: ${e.toString()}';
        _isLoadingRoutes = false;
        notifyListeners();
      }
    } finally {
      _cancellationTokens.remove(cancelToken);
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
    if (_isLoadingSchedules) return; // Prevent duplicate requests

    _isLoadingSchedules = true;
    _scheduleError = null;
    _selectedDeparturePort = departurePort;
    _selectedArrivalPort = arrivalPort;
    _selectedDate = departureDate;
    notifyListeners();

    final cancelToken = Completer();
    _cancellationTokens.add(cancelToken);

    try {
      final result = await _ferryService.getSchedules(
        departurePort: departurePort,
        arrivalPort: arrivalPort,
        departureDate: departureDate,
        ferryId: ferryId,
        includeFullyBooked: includeFullyBooked,
      );

      if (!cancelToken.isCompleted) {
        _schedules = result;
        _sortSchedules();
        _isLoadingSchedules = false;
        notifyListeners();

        // Save recent search to storage
        _saveRecentSearch(departurePort, arrivalPort, departureDate);
      }
    } catch (e) {
      if (!cancelToken.isCompleted) {
        _scheduleError = 'Failed to load schedules: ${e.toString()}';
        _isLoadingSchedules = false;
        notifyListeners();
      }
    } finally {
      _cancellationTokens.remove(cancelToken);
    }
  }

  // Fetch schedule detail
  Future<void> fetchScheduleDetail(int id) async {
    if (_isLoadingScheduleDetail) return;

    // First set loading state without notifying
    _isLoadingScheduleDetail = true;

    // Create a variable to track if we need to notify afterward
    bool shouldNotify = true;

    final cancelToken = Completer();
    _cancellationTokens.add(cancelToken);

    try {
      // First notify AFTER the current build frame
      await Future.microtask(() {
        if (!cancelToken.isCompleted) {
          notifyListeners();
        } else {
          shouldNotify = false;
        }
      });

      final schedule = await _ferryService.getScheduleDetail(id);

      if (!cancelToken.isCompleted) {
        _selectedSchedule = schedule;
        _isLoadingScheduleDetail = false;
        if (shouldNotify) notifyListeners();
      }
    } catch (e) {
      if (!cancelToken.isCompleted) {
        _scheduleError = 'Failed to load schedule details: ${e.toString()}';
        _isLoadingScheduleDetail = false;
        if (shouldNotify) notifyListeners();
      }
    } finally {
      _cancellationTokens.remove(cancelToken);
    }
  }

  // Set selected schedule from the list
  void setSelectedSchedule(int scheduleId) {
    final schedule = _schedules.firstWhere(
      (schedule) => schedule.id == scheduleId,
      orElse: () => throw Exception('Schedule not found'),
    );

    _selectedSchedule = schedule;
    notifyListeners();
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
