import 'package:flutter/material.dart';
import 'dart:async';
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
  bool _isGeneratingTickets = false;

  String? _ticketError;
  
  // Tracking waktu terakhir request untuk throttling sederhana
  DateTime? _lastActiveTicketsRequest;
  DateTime? _lastTicketHistoryRequest;
  
  // Flag untuk mencegah multiple fetch pada initState
  bool _activeTicketsInitialized = false;
  bool _ticketHistoryInitialized = false;

  // Getters
  List<Ticket> get activeTickets => _activeTickets;
  List<Ticket> get ticketHistory => _ticketHistory;
  Ticket? get selectedTicket => _selectedTicket;

  bool get isLoadingActiveTickets => _isLoadingActiveTickets;
  bool get isLoadingTicketHistory => _isLoadingTicketHistory;
  bool get isLoadingTicketDetail => _isLoadingTicketDetail;
  bool get isGeneratingTickets => _isGeneratingTickets;

  String? get ticketError => _ticketError;

  // Constructor
  TicketProvider(StorageService storageService) {
    _storageService = storageService;
    _apiService = ApiService(storageService);
    _ticketService = TicketService(_apiService);
  }

  // External initialization
  void initialize(ApiService apiService, StorageService storageService) {
    _apiService = apiService;
    _storageService = storageService;
    _ticketService = TicketService(_apiService);
  }

  @override
  void dispose() {
    super.dispose();
  }

  // Fetch active tickets dengan throttling sederhana
  Future<void> fetchActiveTickets({bool forceReload = false}) async {
    // Cek jika sudah di-initialized dan tidak diminta force refresh
    if (!forceReload && _activeTicketsInitialized && _activeTickets.isNotEmpty) {
      debugPrint('SKIP FETCH: Active tickets already initialized');
      return;
    }
    
    if (_isLoadingActiveTickets) {
      debugPrint('SKIP FETCH: Active tickets already loading');
      return;
    }
    
    // Throttling sederhana - minimal jeda 30 detik antara request
    if (!forceReload && _lastActiveTicketsRequest != null && 
        DateTime.now().difference(_lastActiveTicketsRequest!).inSeconds < 30) {
      debugPrint('THROTTLED: Please wait before refreshing active tickets again');
      return;
    }

    _isLoadingActiveTickets = true;
    _ticketError = null;
    notifyListeners();

    try {
      _activeTickets = await _ticketService.getActiveTickets();
      _isLoadingActiveTickets = false;
      _activeTicketsInitialized = true;
      _lastActiveTicketsRequest = DateTime.now();
      notifyListeners();
    } catch (e) {
      _ticketError = 'Failed to load active tickets: ${e.toString()}';
      _isLoadingActiveTickets = false;
      notifyListeners();
    }
  }

  // Fetch ticket history dengan throttling sederhana
  Future<void> fetchTicketHistory({bool forceReload = false}) async {
    // Cek jika sudah di-initialized dan tidak diminta force refresh
    if (!forceReload && _ticketHistoryInitialized && _ticketHistory.isNotEmpty) {
      debugPrint('SKIP FETCH: Ticket history already initialized');
      return;
    }
    
    if (_isLoadingTicketHistory) {
      debugPrint('SKIP FETCH: Ticket history already loading');
      return;
    }
    
    // Throttling sederhana - minimal jeda 30 detik antara request
    if (!forceReload && _lastTicketHistoryRequest != null && 
        DateTime.now().difference(_lastTicketHistoryRequest!).inSeconds < 30) {
      debugPrint('THROTTLED: Please wait before refreshing ticket history again');
      return;
    }

    _isLoadingTicketHistory = true;
    _ticketError = null;
    notifyListeners();

    try {
      _ticketHistory = await _ticketService.getTicketHistory();
      _isLoadingTicketHistory = false;
      _ticketHistoryInitialized = true;
      _lastTicketHistoryRequest = DateTime.now();
      notifyListeners();
    } catch (e) {
      _ticketError = 'Failed to load ticket history: ${e.toString()}';
      _isLoadingTicketHistory = false;
      notifyListeners();
    }
  }

  // Fetch ticket detail
  Future<void> fetchTicketDetail(int id) async {
    if (_isLoadingTicketDetail) return;
    
    // Skip fetch jika ticket sudah ada
    if (_selectedTicket != null && _selectedTicket!.id == id) {
      debugPrint('SKIP FETCH: Selected ticket already loaded (id: $id)');
      return;
    }

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

  // Fungsi untuk meminta pembuatan tiket
  Future<bool> generateTickets(int bookingId) async {
    _isGeneratingTickets = true;
    _ticketError = null;
    notifyListeners();

    try {
      // Gunakan method helper khusus di ApiService
      await _apiService.generateTicketsForBooking(bookingId);

      // Refresh active tickets setelah generate
      await fetchActiveTickets(forceReload: true);

      _isGeneratingTickets = false;
      notifyListeners();
      return true;
    } catch (e) {
      _ticketError = 'Failed to generate tickets: ${e.toString()}';
      _isGeneratingTickets = false;
      notifyListeners();
      return false;
    }
  }

  Future<List<Ticket>> fetchTicketsByBookingId(int bookingId) async {
    // Lihat jika sudah memiliki di cache
    final existingTickets = getTicketsByBookingId(bookingId);
    if (existingTickets.isNotEmpty) {
      return existingTickets;
    }
    
    _isLoadingActiveTickets = true;
    _ticketError = null;
    notifyListeners();

    try {
      final tickets = await _ticketService.getTicketsByBookingId(bookingId);

      // Tambahkan tiket ke activeTickets jika belum ada
      for (var ticket in tickets) {
        if (!_activeTickets.any((t) => t.id == ticket.id)) {
          _activeTickets.add(ticket);
        }
      }

      _isLoadingActiveTickets = false;
      notifyListeners();
      return tickets;
    } catch (e) {
      _ticketError = 'Failed to load tickets: ${e.toString()}';
      _isLoadingActiveTickets = false;
      notifyListeners();
      return [];
    }
  }

  // Mendapatkan tiket berdasarkan booking ID
  List<Ticket> getTicketsByBookingId(int bookingId) {
    return _activeTickets
        .where((ticket) => ticket.bookingId == bookingId)
        .toList();
  }

  // Set selected ticket
  void setSelectedTicket(int id) {
    // Try to find in active tickets first
    Ticket? foundTicket;

    for (var ticket in _activeTickets) {
      if (ticket.id == id) {
        foundTicket = ticket;
        break;
      }
    }

    // If not found in active tickets, check history
    if (foundTicket == null) {
      for (var ticket in _ticketHistory) {
        if (ticket.id == id) {
          foundTicket = ticket;
          break;
        }
      }
    }

    if (foundTicket != null) {
      _selectedTicket = foundTicket;
      notifyListeners();
    } else {
      // If not found in local lists, fetch from API
      fetchTicketDetail(id);
    }
  }

  // Clear selected ticket
  void clearSelectedTicket() {
    _selectedTicket = null;
    notifyListeners();
  }

  // Reset state (usually called on logout)
  void reset() {
    _activeTickets = [];
    _ticketHistory = [];
    _selectedTicket = null;
    _activeTicketsInitialized = false;
    _ticketHistoryInitialized = false;
    _ticketError = null;
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
        child: Center(child: Text('No ticket selected')),
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

    final routeName =
        _selectedTicket!.schedule!.route?.routeName ?? 'Unknown Route';

    return _ticketService.generateWatermarkData(
      _selectedTicket!.ticketNumber,
      _selectedTicket!.schedule!.departureTime,
      routeName,
    );
  }
}