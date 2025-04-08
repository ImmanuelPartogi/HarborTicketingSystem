import 'package:flutter/material.dart';
import 'dart:async';
import '../models/ticket_model.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import '../services/ticket_service.dart';
import '../config/debug_config.dart';
import 'package:intl/intl.dart';

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

  // Consolidated setState helper to reduce notifyListeners() calls
  void setState(VoidCallback updateFunction) {
    updateFunction();
    notifyListeners();
  }

  // Fetch active tickets dengan throttling sederhana
  Future<void> fetchActiveTickets({bool forceReload = false}) async {
    // Skip if in debug mode
    if (!forceReload && DebugConfig.shouldSkipDataLoad('active tickets')) {
      return;
    }

    // 1. Prevent multiple concurrent fetches
    if (_isLoadingActiveTickets) {
      debugPrint('SKIP FETCH: Active tickets already loading');
      return;
    }

    // 2. Skip if already initialized and not forcing reload
    if (!forceReload &&
        _activeTicketsInitialized &&
        _activeTickets.isNotEmpty) {
      debugPrint('SKIP FETCH: Active tickets already initialized');
      return;
    }

    // 3. Implement proper throttling
    if (!forceReload && _lastActiveTicketsRequest != null) {
      final difference =
          DateTime.now().difference(_lastActiveTicketsRequest!).inSeconds;
      if (difference < 30) {
        debugPrint(
          'THROTTLED: Please wait before refreshing active tickets again (${30 - difference}s remaining)',
        );
        return;
      }
    }

    // 4. Set state once at the beginning
    setState(() {
      _isLoadingActiveTickets = true;
      _ticketError = null;
    });

    try {
      final tickets = await _ticketService.getActiveTickets();

      // 5. Batch state changes at the end
      setState(() {
        _activeTickets = tickets;
        _isLoadingActiveTickets = false;
        _activeTicketsInitialized = true;
        _lastActiveTicketsRequest = DateTime.now();
      });
    } catch (e) {
      setState(() {
        _ticketError = 'Failed to load active tickets: ${e.toString()}';
        _isLoadingActiveTickets = false;
      });
    }
  }

  // Fetch ticket history dengan throttling sederhana
  Future<void> fetchTicketHistory({bool forceReload = false}) async {
    // Skip if in debug mode
    if (!forceReload && DebugConfig.shouldSkipDataLoad('ticket history')) {
      return;
    }

    // 1. Prevent multiple concurrent fetches
    if (_isLoadingTicketHistory) {
      debugPrint('SKIP FETCH: Ticket history already loading');
      return;
    }

    // 2. Skip if already initialized and not forcing reload
    if (!forceReload &&
        _ticketHistoryInitialized &&
        _ticketHistory.isNotEmpty) {
      debugPrint('SKIP FETCH: Ticket history already initialized');
      return;
    }

    // 3. Implement proper throttling
    if (!forceReload && _lastTicketHistoryRequest != null) {
      final difference =
          DateTime.now().difference(_lastTicketHistoryRequest!).inSeconds;
      if (difference < 30) {
        debugPrint(
          'THROTTLED: Please wait before refreshing ticket history again (${30 - difference}s remaining)',
        );
        return;
      }
    }

    // 4. Set state once at the beginning
    setState(() {
      _isLoadingTicketHistory = true;
      _ticketError = null;
    });

    try {
      final tickets = await _ticketService.getTicketHistory();

      // 5. Batch state changes at the end
      setState(() {
        _ticketHistory = tickets;
        _isLoadingTicketHistory = false;
        _ticketHistoryInitialized = true;
        _lastTicketHistoryRequest = DateTime.now();
      });
    } catch (e) {
      setState(() {
        _ticketError = 'Failed to load ticket history: ${e.toString()}';
        _isLoadingTicketHistory = false;
      });
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

    setState(() {
      _isLoadingTicketDetail = true;
      _ticketError = null;
    });

    try {
      _selectedTicket = await _ticketService.getTicketDetail(id);
      setState(() {
        _isLoadingTicketDetail = false;
      });
    } catch (e) {
      setState(() {
        _ticketError = 'Failed to load ticket details: ${e.toString()}';
        _isLoadingTicketDetail = false;
      });
    }
  }

  // Fungsi untuk meminta pembuatan tiket
  Future<bool> generateTickets(int bookingId) async {
    setState(() {
      _isGeneratingTickets = true;
      _ticketError = null;
    });

    try {
      // Gunakan method helper khusus di ApiService
      await _apiService.generateTicketsForBooking(bookingId);

      // Refresh active tickets setelah generate
      await fetchActiveTickets(forceReload: true);

      setState(() {
        _isGeneratingTickets = false;
      });
      return true;
    } catch (e) {
      setState(() {
        _ticketError = 'Failed to generate tickets: ${e.toString()}';
        _isGeneratingTickets = false;
      });
      return false;
    }
  }

  Future<List<Ticket>> fetchTicketsByBookingId(int bookingId) async {
    // Lihat jika sudah memiliki di cache
    final existingTickets = getTicketsByBookingId(bookingId);
    if (existingTickets.isNotEmpty) {
      return existingTickets;
    }

    setState(() {
      _isLoadingActiveTickets = true;
      _ticketError = null;
    });

    try {
      final tickets = await _ticketService.getTicketsByBookingId(bookingId);

      // Tambahkan tiket ke activeTickets jika belum ada
      final updatedTickets = List<Ticket>.from(_activeTickets);
      for (var ticket in tickets) {
        if (!updatedTickets.any((t) => t.id == ticket.id)) {
          updatedTickets.add(ticket);
        }
      }

      setState(() {
        _activeTickets = updatedTickets;
        _isLoadingActiveTickets = false;
      });

      return tickets;
    } catch (e) {
      setState(() {
        _ticketError = 'Failed to load tickets: ${e.toString()}';
        _isLoadingActiveTickets = false;
      });
      return [];
    }
  }

  // Force refresh tiket setelah pembayaran berhasil
  Future<void> refreshAfterPayment(int bookingId) async {
    // Batalkan throttling untuk memastikan refresh selalu dilakukan
    _lastActiveTicketsRequest = null;
    _lastTicketHistoryRequest = null;

    // Tunggu sebentar untuk memberi waktu server membuat tiket
    await Future.delayed(Duration(seconds: 3));

    // Fetch tiket dari booking
    await fetchTicketsByBookingId(bookingId);

    // Refresh semua tiket aktif
    await fetchActiveTickets(forceReload: true);
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
      setState(() {
        _selectedTicket = foundTicket;
      });
    } else {
      // If not found in local lists, fetch from API
      fetchTicketDetail(id);
    }
  }

  // Clear selected ticket
  void clearSelectedTicket() {
    setState(() {
      _selectedTicket = null;
    });
  }

  // Reset state (usually called on logout)
  void reset() {
    setState(() {
      _activeTickets = [];
      _ticketHistory = [];
      _selectedTicket = null;
      _activeTicketsInitialized = false;
      _ticketHistoryInitialized = false;
      _ticketError = null;
    });
  }

  // Group tickets by schedule
  // Group tickets by schedule
  Map<String, List<dynamic>> getTicketsGroupedBySchedule() {
    // Versi paling sederhana yang mengelompokkan tiket berdasarkan rute saja
    final groupedTickets = <String, List<dynamic>>{};

    // Log debug
    print('DEBUG: Grouping ${_activeTickets.length} tickets');

    // Kelompok tiket berdasarkan rute
    for (var ticket in _activeTickets) {
      if (ticket.schedule == null) {
        print('DEBUG: Ticket ${ticket.id} has no schedule, skipping');
        continue;
      }

      try {
        // Gunakan HANYA nama rute sebagai kunci (bukan ID)
        final routeName = ticket.schedule!.route?.routeName ?? 'Unknown Route';

        // Log untuk debugging
        print('DEBUG: Ticket ${ticket.id} - Route name: $routeName');

        if (!groupedTickets.containsKey(routeName)) {
          groupedTickets[routeName] = [];
          print('DEBUG: Created new group for route: $routeName');
        }

        groupedTickets[routeName]!.add(ticket);
        print('DEBUG: Added ticket ${ticket.id} to group "$routeName"');
      } catch (e) {
        print('DEBUG: Error grouping ticket ID ${ticket.id}: $e');
        continue;
      }
    }

    // Debug summary
    print('DEBUG: Grouped into ${groupedTickets.length} groups:');
    groupedTickets.forEach((key, tickets) {
      print('DEBUG: Group "$key" has ${tickets.length} tickets');
    });

    return groupedTickets;
  }

  void checkAndMoveExpiredTickets() {
    final now = DateTime.now();
    final expiredTickets =
        _activeTickets.where((ticket) {
          return ticket.isExpired ||
              ticket.isUsed ||
              ticket.isCancelled ||
              (ticket.schedule?.departureTime.isBefore(now) ?? false);
        }).toList();

    if (expiredTickets.isNotEmpty) {
      setState(() {
        for (var ticket in expiredTickets) {
          _activeTickets.remove(ticket);
          _ticketHistory.add(ticket);
        }
      });
    }
  }

  // Panggil method ini secara periodik atau saat aplikasi dibuka
  void initializeWithCleanup() {
    fetchActiveTickets();
    fetchTicketHistory();
    checkAndMoveExpiredTickets();
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
