import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/ticket_model.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../services/ticket_service.dart';

class TicketProvider extends ChangeNotifier {
  late ApiService _apiService;
  late TicketService _ticketService;
  late StorageService _storageService;
  
  List<Ticket> _activeTickets = [];
  List<Ticket> _ticketHistory = [];
  Ticket? _selectedTicket;
  
  bool _isLoadingActiveTickets = false;
  bool _isLoadingTicketHistory = false;
  bool _isLoadingTicketDetail = false;
  
  String? _ticketError;
  
  // Getters
  List<Ticket> get activeTickets => _activeTickets;
  List<Ticket> get ticketHistory => _ticketHistory;
  Ticket? get selectedTicket => _selectedTicket;
  
  bool get isLoadingActiveTickets => _isLoadingActiveTickets;
  bool get isLoadingTicketHistory => _isLoadingTicketHistory;
  bool get isLoadingTicketDetail => _isLoadingTicketDetail;
  
  String? get ticketError => _ticketError;
  
  // Constructor
  TicketProvider() {
    _initServices();
  }
  
  // Initialize services
  Future<void> _initServices() async {
    final prefs = await SharedPreferences.getInstance();
    _storageService = StorageService(prefs);
    _apiService = ApiService(_storageService);
    _ticketService = TicketService(_apiService);
  }
  
  // External initialization
  void initialize(ApiService apiService, StorageService storageService) {
    _apiService = apiService;
    _storageService = storageService;
    _ticketService = TicketService(_apiService);
  }
  
  // Fetch active tickets
  Future<void> fetchActiveTickets() async {
    _isLoadingActiveTickets = true;
    _ticketError = null;
    notifyListeners();
    
    try {
      _activeTickets = await _ticketService.getActiveTickets();
      _isLoadingActiveTickets = false;
      notifyListeners();
    } catch (e) {
      _ticketError = 'Failed to load active tickets: ${e.toString()}';
      _isLoadingActiveTickets = false;
      notifyListeners();
    }
  }
  
  // Fetch ticket history
  Future<void> fetchTicketHistory() async {
    _isLoadingTicketHistory = true;
    _ticketError = null;
    notifyListeners();
    
    try {
      _ticketHistory = await _ticketService.getTicketHistory();
      _isLoadingTicketHistory = false;
      notifyListeners();
    } catch (e) {
      _ticketError = 'Failed to load ticket history: ${e.toString()}';
      _isLoadingTicketHistory = false;
      notifyListeners();
    }
  }
  
  // Fetch ticket detail
  Future<void> fetchTicketDetail(int id) async {
    _isLoadingTicketDetail = true;
    _ticketError = null;
    notifyListeners();
    
    try {
      _selectedTicket = await _ticketService.getTicketDetail(id);
      _isLoadingTicketDetail = false;
      notifyListeners();
    } catch (e) {
      _ticketError = 'Failed to load ticket details: ${e.toString()}';
      _isLoadingTicketDetail = false;
      notifyListeners();
    }
  }
  
  // Set selected ticket
  void setSelectedTicket(int id) {
    // Try to find in active tickets first
    _selectedTicket = _activeTickets.firstWhere(
      (ticket) => ticket.id == id,
      orElse: () => _ticketHistory.firstWhere(
        (ticket) => ticket.id == id,
        orElse: () => null as Ticket,
      ),
    );
    
    if (_selectedTicket == null) {
      // If not found in local lists, fetch from API
      fetchTicketDetail(id);
    } else {
      notifyListeners();
    }
  }
  
  // Clear selected ticket
  void clearSelectedTicket() {
    _selectedTicket = null;
    notifyListeners();
  }
  
  // Group tickets by schedule
  Map<int, List<Ticket>> getTicketsGroupedBySchedule() {
    return _ticketService.groupTicketsBySchedule(_activeTickets);
  }
  
  // Check if ticket is valid
  bool isTicketValid(Ticket ticket) {
    return _ticketService.isTicketValid(ticket);
  }
  
  // Generate QR code for selected ticket
  Widget generateTicketQR() {
    if (_selectedTicket == null) {
      return const SizedBox(
        width: 200,
        height: 200,
        child: Center(
          child: Text('No ticket selected'),
        ),
      );
    }
    
    return _ticketService.generateTicketQR(
      _selectedTicket!.ticketNumber,
      _selectedTicket!.id,
      _selectedTicket!.schedule!.departureTime.add(const Duration(hours: 1)),
    );
  }
  
  // Generate watermark data for dynamic ticket
  Map<String, dynamic> generateWatermarkData() {
    if (_selectedTicket == null || _selectedTicket!.schedule == null) {
      return {};
    }
    
    final routeName = _selectedTicket!.schedule!.route?.routeName ?? 'Unknown Route';
    
    return _ticketService.generateWatermarkData(
      _selectedTicket!.ticketNumber,
      _selectedTicket!.schedule!.departureTime,
      routeName,
    );
  }
}