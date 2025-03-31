import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart'; // Added missing import for DateFormat
import 'dart:async';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';
import '../../providers/ferry_provider.dart';
import '../../providers/ticket_provider.dart';
import '../../widgets/home/search_form.dart';
import '../../widgets/home/route_card.dart';
import '../../widgets/common/loading_indicator.dart';
import '../profile/profile_screen.dart';
import '../route/routes_screen.dart';
import '../../models/schedule_model.dart'; // Added import for ScheduleModel

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;
  bool _isInitialized = false;
  bool _isLoadingData = false;
  DateTime? _lastDataLoad;

  // Flag untuk setiap jenis data
  bool _routesInitialized = false;
  bool _activeTicketsInitialized = false;
  bool _ticketHistoryInitialized = false;

  // Debouncing
  Timer? _debounceTimer;

  // Key untuk IndexedStack untuk mencegah rebuild saat ganti tab
  final GlobalKey<_MyTicketsTabState> _ticketsTabKey = GlobalKey();

  @override
  void initState() {
    super.initState();

    // Pastikan untuk menginisialisasi data pada waktu yang tepat
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _loadInitialData();
      }
    });
  }

  @override
  void dispose() {
    _isInitialized = false;
    _debounceTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadInitialData() async {
    // Batalkan timer sebelumnya jika ada
    _debounceTimer?.cancel();

    if (!mounted || _isLoadingData) return;

    // Throttling untuk mencegah reload terlalu sering
    if (_lastDataLoad != null &&
        DateTime.now().difference(_lastDataLoad!).inSeconds < 60) {
      // Set timer untuk retry nanti jika diperlukan
      _debounceTimer = Timer(
        Duration(
          seconds: 60 - DateTime.now().difference(_lastDataLoad!).inSeconds,
        ),
        () {
          if (mounted && !_isInitialized) _loadInitialData();
        },
      );

      debugPrint('THROTTLED: _loadInitialData called too frequently');
      return;
    }

    setState(() {
      _isLoadingData = true;
    });

    try {
      // Gunakan future.wait untuk menangani error dengan lebih baik
      List<Future> futures = [];

      // Load routes jika belum di-initialized
      if (!_routesInitialized) {
        final ferryProvider = Provider.of<FerryProvider>(
          context,
          listen: false,
        );
        futures.add(
          ferryProvider.fetchRoutes().then((_) {
            _routesInitialized = true;
          }),
        );
      }

      // Load active tickets jika belum di-initialized
      if (!_activeTicketsInitialized) {
        final ticketProvider = Provider.of<TicketProvider>(
          context,
          listen: false,
        );
        futures.add(
          ticketProvider.fetchActiveTickets().then((_) {
            _activeTicketsInitialized = true;
          }),
        );
      }

      // Execute all futures only if needed
      if (futures.isNotEmpty) {
        await Future.wait(futures);
      }

      if (mounted) {
        setState(() {
          _isInitialized = true;
          _isLoadingData = false;
          _lastDataLoad = DateTime.now();
        });
      }
    } catch (e) {
      debugPrint('ERROR in _loadInitialData: $e');
      if (mounted) {
        setState(() {
          _isLoadingData = false;
        });
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error loading data: $e')));
      }
    }
  }

  void _setCurrentIndex(int index) {
    if (!mounted) return;

    if (_currentIndex != index) {
      setState(() {
        _currentIndex = index;
      });

      // Pre-load data for the selected tab
      _preloadDataForTab(index);
    }
  }

  // Pre-load data for a specific tab
  void _preloadDataForTab(int index) {
    switch (index) {
      case 0: // Home tab
        if (!_isInitialized) {
          _loadInitialData();
        }
        break;
      case 1: // Routes tab
        if (!_routesInitialized) {
          Provider.of<FerryProvider>(context, listen: false).fetchRoutes().then(
            (_) {
              _routesInitialized = true;
            },
          );
        }
        break;
      case 2: // My Tickets tab
        // Let the tab handle its own data loading
        break;
      case 3: // Profile tab
        // No special data needed
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: [
          HomeTab(
            onTicketsTap: () => _setCurrentIndex(2),
            onProfileTap: () => _setCurrentIndex(3),
            isInitialized: _isInitialized,
            onRefresh: _onRefresh,
            onNavigateToTab:
                _setCurrentIndex, // Added callback for tab navigation
          ),
          const RoutesScreen(), // Add Routes screen as 2nd tab
          MyTicketsTab(key: _ticketsTabKey),
          const ProfileTab(),
        ],
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: _setCurrentIndex,
        type: BottomNavigationBarType.fixed,
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          BottomNavigationBarItem(icon: Icon(Icons.map), label: 'Routes'),
          BottomNavigationBarItem(
            icon: Icon(Icons.confirmation_number),
            label: 'My Tickets',
          ),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profile'),
        ],
      ),
    );
  }

  // Throttled refresh handler
  DateTime? _lastRefreshTime;

  Future<void> _onRefresh() async {
    // Throttling untuk refresh manual
    if (_lastRefreshTime != null &&
        DateTime.now().difference(_lastRefreshTime!).inSeconds < 30) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Harap tunggu beberapa saat sebelum refresh kembali'),
        ),
      );
      return;
    }

    _lastRefreshTime = DateTime.now();

    // Reset initialized state
    setState(() {
      _routesInitialized = false;
      _activeTicketsInitialized = false;
      _lastDataLoad = null; // Allow immediate reload
    });

    // Reload data
    await _loadInitialData();
  }
}

class HomeTab extends StatefulWidget {
  final VoidCallback onTicketsTap;
  final VoidCallback onProfileTap;
  final Future<void> Function() onRefresh;
  final bool isInitialized;
  final Function(int) onNavigateToTab; // Added callback for tab navigation

  const HomeTab({
    Key? key,
    required this.onTicketsTap,
    required this.onProfileTap,
    required this.onRefresh,
    required this.isInitialized,
    required this.onNavigateToTab, // Added parameter
  }) : super(key: key);

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  DateTime? _lastRefreshTime;
  bool _isLoading = false;
  bool _scheduleLoaded =
      false; // Flag untuk memastikan jadwal hanya dimuat sekali
  Timer? _refreshTimer; // Timer untuk memperbarui UI secara berkala

  @override
  void initState() {
    super.initState();

    // Langsung muat jadwal setelah build selesai
    Future.microtask(() {
      if (mounted && !_scheduleLoaded) {
        _loadSchedulesDirectly();
      }
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  // Metode khusus untuk memuat jadwal secara langsung
  Future<void> _loadSchedulesDirectly() async {
    if (_isLoading || _scheduleLoaded) return; // Hindari pemuatan ganda

    setState(() {
      _isLoading = true;
    });

    try {
      final ferryProvider = Provider.of<FerryProvider>(context, listen: false);

      // 1. Reset state untuk memastikan data baru
      ferryProvider.clearSchedules();

      // 2. Memuat rute jika diperlukan
      await ferryProvider.fetchRoutes(forceRefresh: true);

      // 3. Panggil langsung untuk rute Ajibata-Ambarita yang diketahui memiliki jadwal
      debugPrint('DIRECT: Fetching schedules for Ajibata-Ambarita directly');

      final today = DateTime.now();
      final ferryService = ferryProvider.getFerryService();

      final schedules = await ferryService.getSchedules(
        departurePort: 'Ajibata',
        arrivalPort: 'Ambarita',
        departureDate: today,
        includeFullyBooked: false,
      );

      // 4. Perbarui data jadwal langsung
      ferryProvider.updateSchedulesDirectly(schedules);

      // 5. Tunggu sebentar untuk memastikan perubahan diterapkan ke UI
      setState(() {
        _scheduleLoaded = true;
        _isLoading = false;
      });

      // 6. Pastikan UI diperbarui dengan menunggu sedikit dan memicu rebuild jika perlu
      if (schedules.isNotEmpty && mounted) {
        _refreshTimer = Timer(Duration(milliseconds: 500), () {
          if (mounted) {
            setState(
              () {},
            ); // Trigger UI rebuild untuk memastikan jadwal muncul
          }
        });
      }
    } catch (e) {
      debugPrint('Error loading schedules directly: $e');

      // Fallback ke metode standar jika gagal
      try {
        final ferryProvider = Provider.of<FerryProvider>(
          context,
          listen: false,
        );
        await ferryProvider.fetchPopularSchedules(forceRefresh: true);
      } catch (e) {
        debugPrint('Error in fallback method: $e');
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  // Metode untuk memuat ulang jadwal (digunakan oleh tombol Check Again)
  Future<void> _reloadSchedules() async {
    setState(() {
      _scheduleLoaded = false; // Reset flag
    });
    _loadSchedulesDirectly(); // Muat ulang
  }

  Future<void> _handleRefresh() async {
    // Throttling untuk refresh
    if (_lastRefreshTime != null &&
        DateTime.now().difference(_lastRefreshTime!).inSeconds < 30) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please wait before refreshing again')),
      );
      return;
    }

    _lastRefreshTime = DateTime.now();
    _reloadSchedules();
    return;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _handleRefresh,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header with greeting and profile
                Padding(
                  padding: const EdgeInsets.all(AppTheme.paddingMedium),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Hello,',
                              style: TextStyle(
                                fontSize: AppTheme.fontSizeRegular,
                                color: theme.textTheme.bodyMedium?.color,
                              ),
                            ),
                            Consumer<AuthProvider>(
                              builder: (context, authProvider, _) {
                                return Text(
                                  authProvider.user?.name ?? 'Guest',
                                  style: TextStyle(
                                    fontSize: AppTheme.fontSizeXLarge,
                                    fontWeight: FontWeight.bold,
                                    color: theme.textTheme.displaySmall?.color,
                                  ),
                                );
                              },
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () {
                          // Navigate to notifications
                        },
                        icon: const Icon(Icons.notifications),
                      ),
                      Consumer<AuthProvider>(
                        builder: (context, authProvider, _) {
                          final initial =
                              authProvider.user?.name.isNotEmpty == true
                                  ? authProvider.user!.name.substring(0, 1)
                                  : 'G';

                          return GestureDetector(
                            onTap: widget.onProfileTap,
                            child: CircleAvatar(
                              radius: 24,
                              backgroundColor: AppTheme.primaryColor,
                              child: Text(
                                initial,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: AppTheme.fontSizeLarge,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),

                // Search Form
                const SearchForm(),

                // Upcoming Departures Section
                Padding(
                  padding: const EdgeInsets.all(AppTheme.paddingMedium),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Upcoming Departures',
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeLarge,
                          fontWeight: FontWeight.bold,
                          color: theme.textTheme.displaySmall?.color,
                        ),
                      ),
                      TextButton.icon(
                        onPressed: () => widget.onNavigateToTab(1),
                        icon: const Icon(Icons.map),
                        label: const Text('See All'),
                        style: TextButton.styleFrom(
                          foregroundColor: AppTheme.primaryColor,
                        ),
                      ),
                    ],
                  ),
                ),

                // Upcoming Schedules List dengan build strategy kustom
                SizedBox(
                  height: 230,
                  child: Consumer<FerryProvider>(
                    builder: (context, ferryProvider, _) {
                      // Selalu mulai proses pemuatan jadwal jika belum dilakukan
                      if (!_scheduleLoaded &&
                          !_isLoading &&
                          ferryProvider.schedules.isEmpty) {
                        // Gunakan microtask untuk menghindari setState during build
                        Future.microtask(() {
                          if (mounted) {
                            _loadSchedulesDirectly();
                          }
                        });
                      }

                      if (_isLoading) {
                        return const Center(child: LoadingIndicator());
                      }

                      final schedules = ferryProvider.schedules;

                      if (schedules.isEmpty) {
                        return Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.directions_boat_outlined,
                                size: 48,
                                color: theme.hintColor,
                              ),
                              const SizedBox(height: 12),
                              Text(
                                'No upcoming departures available',
                                style: TextStyle(color: theme.hintColor),
                              ),
                              const SizedBox(height: 8),
                              ElevatedButton(
                                onPressed: _reloadSchedules,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: theme.primaryColor,
                                  foregroundColor: Colors.white,
                                ),
                                child: Text(
                                  _isLoading ? "Loading..." : "Check Again",
                                ),
                              ),
                            ],
                          ),
                        );
                      }

                      return ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: schedules.length > 5 ? 5 : schedules.length,
                        itemBuilder: (context, index) {
                          final schedule = schedules[index];
                          return Padding(
                            padding: const EdgeInsets.only(
                              left: AppTheme.paddingMedium,
                              right: 4,
                              bottom: AppTheme.paddingMedium,
                            ),
                            child: ScheduleCard(
                              schedule: schedule,
                              onTap: () {
                                ferryProvider.setSelectedSchedule(schedule.id);
                                Navigator.pushNamed(
                                  context,
                                  AppRoutes.ferryDetails,
                                  arguments: {'scheduleId': schedule.id},
                                );
                              },
                            ),
                          );
                        },
                      );
                    },
                  ),
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Quick Links Section
                Padding(
                  padding: const EdgeInsets.all(AppTheme.paddingMedium),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Quick Actions',
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeLarge,
                          fontWeight: FontWeight.bold,
                          color: theme.textTheme.displaySmall?.color,
                        ),
                      ),
                      const SizedBox(height: AppTheme.paddingMedium),
                      Row(
                        children: [
                          _buildQuickLinkCard(
                            context,
                            icon: Icons.confirmation_number,
                            title: 'My Tickets',
                            onTap: widget.onTicketsTap,
                          ),
                          const SizedBox(width: AppTheme.paddingMedium),
                          _buildQuickLinkCard(
                            context,
                            icon: Icons.search,
                            title: 'Find Routes',
                            onTap: () {
                              Navigator.pushNamed(context, AppRoutes.routes);
                            },
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: AppTheme.paddingLarge),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildQuickLinkCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required VoidCallback onTap,
  }) {
    final theme = Theme.of(context);

    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
        child: Container(
          padding: const EdgeInsets.all(AppTheme.paddingMedium),
          decoration: BoxDecoration(
            color: theme.cardColor,
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 4,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(AppTheme.paddingSmall),
                decoration: BoxDecoration(
                  color: theme.primaryColor.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: theme.primaryColor, size: 24),
              ),
              const SizedBox(height: AppTheme.paddingSmall),
              Text(
                title,
                style: const TextStyle(
                  fontWeight: FontWeight.w500,
                  fontSize: AppTheme.fontSizeRegular,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// Fixed ScheduleCard implementation
class ScheduleCard extends StatelessWidget {
  final ScheduleModel schedule;
  final VoidCallback onTap;

  const ScheduleCard({Key? key, required this.schedule, required this.onTap})
    : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final currencyFormat = NumberFormat.currency(
      locale: 'id',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    // Check if route data exists
    final hasRouteData = schedule.route != null;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 220,
        decoration: BoxDecoration(
          color: theme.cardColor,
          borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header with route info
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingSmall),
              decoration: BoxDecoration(
                color: theme.primaryColor.withOpacity(0.1),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(AppTheme.borderRadiusMedium),
                  topRight: Radius.circular(AppTheme.borderRadiusMedium),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.directions_boat,
                    color: theme.primaryColor,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      hasRouteData
                          ? schedule.route!.routeName
                          : 'Unknown Route',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: AppTheme.fontSizeSmall,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),

            // Date and Time
            Padding(
              padding: const EdgeInsets.all(AppTheme.paddingSmall),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    DateFormat(
                      'EEE, dd MMM yyyy',
                    ).format(schedule.departureTime),
                    style: TextStyle(
                      fontSize: AppTheme.fontSizeSmall,
                      color: theme.textTheme.bodyMedium?.color,
                    ),
                  ),
                  Row(
                    children: [
                      Icon(
                        Icons.access_time,
                        size: 14,
                        color: theme.textTheme.bodyMedium?.color,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        schedule.formattedDepartureTime,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: AppTheme.fontSizeMedium,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: AppTheme.paddingSmall),

                  // Ferry info
                  if (schedule.ferry != null)
                    Text(
                      'Ferry: ${schedule.ferry!.name}',
                      style: TextStyle(
                        fontSize: AppTheme.fontSizeSmall,
                        color: theme.textTheme.bodyMedium?.color,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),

                  // Available Seats
                  Row(
                    children: [
                      Text(
                        'Available: ',
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeSmall,
                          color: theme.textTheme.bodyMedium?.color,
                        ),
                      ),
                      Text(
                        '${schedule.remainingPassengerCapacity} seats',
                        style: TextStyle(
                          fontWeight: FontWeight.w500,
                          fontSize: AppTheme.fontSizeSmall,
                          color:
                              schedule.remainingPassengerCapacity > 10
                                  ? Colors.green
                                  : Colors.orange,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: AppTheme.paddingSmall),

                  // Price and status
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        currencyFormat.format(schedule.finalPrice),
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: AppTheme.fontSizeRegular,
                          color: theme.primaryColor,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppTheme.paddingXSmall,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color:
                              schedule.isAvailable
                                  ? Colors.green.withOpacity(0.1)
                                  : Colors.red.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(
                            AppTheme.borderRadiusRound,
                          ),
                        ),
                        child: Text(
                          schedule.statusText,
                          style: TextStyle(
                            color:
                                schedule.isAvailable
                                    ? Colors.green
                                    : Colors.red,
                            fontWeight: FontWeight.w600,
                            fontSize: AppTheme.fontSizeXSmall,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// Improved MyTicketsTab to reduce redundant API calls
class MyTicketsTab extends StatefulWidget {
  const MyTicketsTab({Key? key}) : super(key: key);

  @override
  State<MyTicketsTab> createState() => _MyTicketsTabState();
}

class _MyTicketsTabState extends State<MyTicketsTab>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = false;
  bool _isInitialized = false;
  DateTime? _lastDataLoad;
  Timer? _debounceTimer;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);

    // Only load tickets if not already loaded elsewhere
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && !_isInitialized) {
        _loadTickets();
      }
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _debounceTimer?.cancel();
    super.dispose();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();

    // If this tab becomes visible and data isn't initialized or loaded for a while,
    // schedule loading with proper debouncing
    if (!_isInitialized && !_isLoading && mounted) {
      // Cancel any previous timer
      _debounceTimer?.cancel();

      // Set a short delay to prevent multiple calls during navigation
      _debounceTimer = Timer(const Duration(milliseconds: 300), () {
        if (mounted && !_isInitialized && !_isLoading) {
          _loadTickets();
        }
      });
    }
  }

  Future<void> _loadTickets() async {
    // Cancel any previous debounce timer
    _debounceTimer?.cancel();

    if (!mounted || _isLoading) return;

    // Throttling untuk mencegah reload terlalu sering (60 detik)
    if (_lastDataLoad != null &&
        DateTime.now().difference(_lastDataLoad!).inSeconds < 60) {
      // Set timer untuk retry nanti jika diperlukan
      _debounceTimer = Timer(
        Duration(
          seconds: 60 - DateTime.now().difference(_lastDataLoad!).inSeconds,
        ),
        () {
          if (mounted && !_isInitialized) _loadTickets();
        },
      );

      debugPrint('THROTTLED: Ticket load throttled');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final ticketProvider = Provider.of<TicketProvider>(
        context,
        listen: false,
      );

      // Load secara berurutan untuk mengurangi beban server
      await ticketProvider.fetchActiveTickets();

      if (mounted) {
        await ticketProvider.fetchTicketHistory();
      }

      if (mounted) {
        setState(() {
          _isLoading = false;
          _isInitialized = true;
          _lastDataLoad = DateTime.now();
        });
      }
    } catch (e) {
      debugPrint('ERROR in _loadTickets: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error loading tickets: $e')));
      }
    }
  }

  // Throttled refresh handler
  DateTime? _lastRefreshTime;

  Future<void> _handleRefresh() async {
    // Throttling untuk refresh manual
    if (_lastRefreshTime != null &&
        DateTime.now().difference(_lastRefreshTime!).inSeconds < 30) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Harap tunggu beberapa saat sebelum refresh kembali'),
        ),
      );
      return;
    }

    _lastRefreshTime = DateTime.now();
    _lastDataLoad = null; // Reset untuk force refresh
    return _loadTickets();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Tickets'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [Tab(text: 'Active'), Tab(text: 'History')],
        ),
      ),
      body: Consumer<TicketProvider>(
        builder: (context, ticketProvider, _) {
          return TabBarView(
            controller: _tabController,
            children: [
              // Active tickets tab
              RefreshIndicator(
                onRefresh: _handleRefresh,
                child:
                    ticketProvider.isLoadingActiveTickets
                        ? const Center(child: LoadingIndicator())
                        : ticketProvider.activeTickets.isEmpty
                        ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.confirmation_number_outlined,
                                size: 64,
                                color: theme.hintColor,
                              ),
                              const SizedBox(height: AppTheme.paddingMedium),
                              Text(
                                'No active tickets',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeMedium,
                                  fontWeight: FontWeight.w500,
                                  color: theme.textTheme.bodyLarge?.color,
                                ),
                              ),
                              const SizedBox(height: AppTheme.paddingSmall),
                              Text(
                                'Book a ferry ticket to see your active tickets here',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                  color: theme.textTheme.bodyMedium?.color,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ],
                          ),
                        )
                        : ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          itemCount: ticketProvider.activeTickets.length,
                          itemBuilder: (context, index) {
                            final ticket = ticketProvider.activeTickets[index];
                            return GestureDetector(
                              onTap: () {
                                ticketProvider.setSelectedTicket(ticket.id);
                                Navigator.pushNamed(
                                  context,
                                  AppRoutes.ticketDetail,
                                  arguments: {'ticketId': ticket.id},
                                );
                              },
                              child: Container(
                                margin: const EdgeInsets.symmetric(
                                  horizontal: AppTheme.paddingMedium,
                                  vertical: AppTheme.paddingSmall,
                                ),
                                child: Card(
                                  elevation: 2,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(
                                      AppTheme.borderRadiusMedium,
                                    ),
                                  ),
                                  child: Padding(
                                    padding: const EdgeInsets.all(
                                      AppTheme.paddingMedium,
                                    ),
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Container(
                                              padding: const EdgeInsets.all(
                                                AppTheme.paddingSmall,
                                              ),
                                              decoration: BoxDecoration(
                                                color: AppTheme.primaryColor
                                                    .withOpacity(0.1),
                                                borderRadius:
                                                    BorderRadius.circular(
                                                      AppTheme
                                                          .borderRadiusRegular,
                                                    ),
                                              ),
                                              child: const Icon(
                                                Icons.directions_boat,
                                                color: AppTheme.primaryColor,
                                              ),
                                            ),
                                            const SizedBox(
                                              width: AppTheme.paddingMedium,
                                            ),
                                            Expanded(
                                              child: Column(
                                                crossAxisAlignment:
                                                    CrossAxisAlignment.start,
                                                children: [
                                                  Text(
                                                    ticket
                                                            .schedule
                                                            ?.route
                                                            ?.routeName ??
                                                        'Unknown Route',
                                                    style: const TextStyle(
                                                      fontWeight:
                                                          FontWeight.bold,
                                                      fontSize:
                                                          AppTheme
                                                              .fontSizeMedium,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
              ),

              // History tab with similar implementation
              RefreshIndicator(
                onRefresh: _handleRefresh,
                child:
                    ticketProvider.isLoadingTicketHistory
                        ? const Center(child: LoadingIndicator())
                        : ticketProvider.ticketHistory.isEmpty
                        ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.history,
                                size: 64,
                                color: theme.hintColor,
                              ),
                              const SizedBox(height: AppTheme.paddingMedium),
                              Text(
                                'No ticket history',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeMedium,
                                  fontWeight: FontWeight.w500,
                                  color: theme.textTheme.bodyLarge?.color,
                                ),
                              ),
                              const SizedBox(height: AppTheme.paddingSmall),
                              Text(
                                'Your completed trips will appear here',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                  color: theme.textTheme.bodyMedium?.color,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ],
                          ),
                        )
                        : ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          itemCount: ticketProvider.ticketHistory.length,
                          itemBuilder: (context, index) {
                            final ticket = ticketProvider.ticketHistory[index];
                            return GestureDetector(
                              onTap: () {
                                ticketProvider.setSelectedTicket(ticket.id);
                                Navigator.pushNamed(
                                  context,
                                  AppRoutes.ticketDetail,
                                  arguments: {'ticketId': ticket.id},
                                );
                              },
                              child: Container(
                                margin: const EdgeInsets.symmetric(
                                  horizontal: AppTheme.paddingMedium,
                                  vertical: AppTheme.paddingSmall,
                                ),
                                child: Card(
                                  elevation: 2,
                                  child: Padding(
                                    padding: const EdgeInsets.all(
                                      AppTheme.paddingMedium,
                                    ),
                                    child: Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(
                                            AppTheme.paddingSmall,
                                          ),
                                          child: const Icon(Icons.history),
                                        ),
                                        const SizedBox(
                                          width: AppTheme.paddingMedium,
                                        ),
                                        Expanded(
                                          child: Text(
                                            ticket.schedule?.route?.routeName ??
                                                'Unknown Route',
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
              ),
            ],
          );
        },
      ),
    );
  }
}

// Simple ProfileTab that uses ProfileScreen
class ProfileTab extends StatelessWidget {
  const ProfileTab({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return const ProfileScreen();
  }
}
