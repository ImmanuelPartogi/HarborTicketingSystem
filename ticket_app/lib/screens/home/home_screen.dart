import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
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
import '../../models/schedule_model.dart';

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
    final theme = Theme.of(context);

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: [
          HomeTab(
            onTicketsTap: () => _setCurrentIndex(2),
            onProfileTap: () => _setCurrentIndex(3),
            isInitialized: _isInitialized,
            onRefresh: _onRefresh,
            onNavigateToTab: _setCurrentIndex,
            ticketsTabKey: _ticketsTabKey, // Tambahkan key di sini
          ),
          const RoutesScreen(),
          MyTicketsTab(key: _ticketsTabKey),
          const ProfileTab(),
        ],
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, -5),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: _setCurrentIndex,
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          elevation: 0,
          selectedItemColor: theme.primaryColor,
          unselectedItemColor: Colors.grey,
          selectedLabelStyle: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 12,
          ),
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home),
              label: 'Home',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.map_outlined),
              activeIcon: Icon(Icons.map),
              label: 'Routes',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.confirmation_number_outlined),
              activeIcon: Icon(Icons.confirmation_number),
              label: 'My Tickets',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_outline),
              activeIcon: Icon(Icons.person),
              label: 'Profile',
            ),
          ],
        ),
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
  final Function(int) onNavigateToTab;
  final GlobalKey<_MyTicketsTabState>? ticketsTabKey; // Tambahkan parameter ini

  const HomeTab({
    Key? key,
    required this.onTicketsTap,
    required this.onProfileTap,
    required this.onRefresh,
    required this.isInitialized,
    required this.onNavigateToTab,
    this.ticketsTabKey, // Terima parameter dari parent
  }) : super(key: key);

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  DateTime? _lastRefreshTime;
  bool _isLoading = false;
  bool _scheduleLoaded = false;
  Timer? _refreshTimer;

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
    if (_isLoading || _scheduleLoaded) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final ferryProvider = Provider.of<FerryProvider>(context, listen: false);

      // Reset state untuk memastikan data baru
      ferryProvider.clearSchedules();

      // Memuat rute jika diperlukan
      await ferryProvider.fetchRoutes(forceRefresh: true);

      // Panggil langsung untuk rute Ajibata-Ambarita yang diketahui memiliki jadwal
      debugPrint('DIRECT: Fetching schedules for Ajibata-Ambarita directly');

      final today = DateTime.now();
      final ferryService = ferryProvider.getFerryService();

      final schedules = await ferryService.getSchedules(
        departurePort: 'Ajibata',
        arrivalPort: 'Ambarita',
        departureDate: today,
        includeFullyBooked: false,
      );

      // Perbarui data jadwal langsung
      ferryProvider.updateSchedulesDirectly(schedules);

      // Tunggu sebentar untuk memastikan perubahan diterapkan ke UI
      setState(() {
        _scheduleLoaded = true;
        _isLoading = false;
      });

      // Pastikan UI diperbarui dengan menunggu sedikit dan memicu rebuild jika perlu
      if (schedules.isNotEmpty && mounted) {
        _refreshTimer = Timer(Duration(milliseconds: 500), () {
          if (mounted) {
            setState(() {});
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

  // Metode untuk memuat ulang jadwal
  Future<void> _reloadSchedules() async {
    setState(() {
      _scheduleLoaded = false;
    });
    _loadSchedulesDirectly();
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
                      // Add settings button
                      IconButton(
                        onPressed: () {
                          Navigator.pushNamed(context, AppRoutes.settings);
                        },
                        icon: const Icon(Icons.settings_outlined),
                        style: IconButton.styleFrom(
                          backgroundColor: theme.colorScheme.surface,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      IconButton(
                        onPressed: () {
                          // Navigate to notifications
                        },
                        icon: const Icon(Icons.notifications_outlined),
                        style: IconButton.styleFrom(
                          backgroundColor: theme.colorScheme.surface,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
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
                              backgroundColor: theme.primaryColor,
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
                const Padding(
                  padding: EdgeInsets.symmetric(
                    horizontal: AppTheme.paddingMedium,
                  ),
                  child: SearchForm(),
                ),

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
                        icon: const Icon(Icons.map, size: 18),
                        label: const Text('See All'),
                        style: TextButton.styleFrom(
                          foregroundColor: theme.primaryColor,
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingSmall,
                            vertical: 4,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // Upcoming Schedules List
                SizedBox(
                  height: 240,
                  child: Consumer<FerryProvider>(
                    builder: (context, ferryProvider, _) {
                      // Start loading schedules
                      if (!_scheduleLoaded &&
                          !_isLoading &&
                          ferryProvider.schedules.isEmpty) {
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
                        return _buildEmptySchedulesView(theme);
                      }

                      return ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: schedules.length > 5 ? 5 : schedules.length,
                        padding: const EdgeInsets.only(
                          left: AppTheme.paddingMedium,
                        ),
                        itemBuilder: (context, index) {
                          final schedule = schedules[index];
                          return Padding(
                            padding: const EdgeInsets.only(
                              right: AppTheme.paddingMedium,
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
                      const SizedBox(height: AppTheme.paddingMedium),
                      Row(
                        children: [
                          _buildQuickLinkCard(
                            context,
                            icon: Icons.history,
                            title: 'History',
                            onTap: () {
                              widget.onNavigateToTab(2);
                              // Delay to allow tab to load, then switch to history tab
                              if (widget.ticketsTabKey?.currentState != null) {
                                Future.delayed(Duration(milliseconds: 300), () {
                                  widget
                                      .ticketsTabKey
                                      ?.currentState
                                      ?._tabController
                                      .animateTo(1);
                                });
                              }
                            },
                          ),
                          const SizedBox(width: AppTheme.paddingMedium),
                          _buildQuickLinkCard(
                            context,
                            icon: Icons.help_outline,
                            title: 'Help & Info',
                            onTap: () {
                              // Show help dialog
                              showModalBottomSheet(
                                context: context,
                                isScrollControlled: true,
                                shape: const RoundedRectangleBorder(
                                  borderRadius: BorderRadius.vertical(
                                    top: Radius.circular(20),
                                  ),
                                ),
                                builder:
                                    (context) => Container(
                                      padding: const EdgeInsets.all(20),
                                      child: Column(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Text(
                                            'Help & Information',
                                            style: TextStyle(
                                              fontSize: 18,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                          const SizedBox(height: 16),
                                          ListTile(
                                            leading: Icon(Icons.support_agent),
                                            title: Text('Customer Support'),
                                            subtitle: Text(
                                              'Contact our support team',
                                            ),
                                            onTap: () {
                                              Navigator.pop(context);
                                            },
                                          ),
                                          ListTile(
                                            leading: Icon(Icons.info_outline),
                                            title: Text('About Ferry App'),
                                            subtitle: Text(
                                              'Learn more about our services',
                                            ),
                                            onTap: () {
                                              Navigator.pop(context);
                                            },
                                          ),
                                          ListTile(
                                            leading: Icon(Icons.help_outline),
                                            title: Text('FAQ'),
                                            subtitle: Text(
                                              'Frequently asked questions',
                                            ),
                                            onTap: () {
                                              Navigator.pop(context);
                                            },
                                          ),
                                          const SizedBox(height: 16),
                                          ElevatedButton(
                                            onPressed:
                                                () => Navigator.pop(context),
                                            child: Text('Close'),
                                            style: ElevatedButton.styleFrom(
                                              minimumSize: Size(
                                                double.infinity,
                                                45,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                              );
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

  Widget _buildEmptySchedulesView(ThemeData theme) {
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
          const SizedBox(height: 12),
          ElevatedButton.icon(
            onPressed: _reloadSchedules,
            style: ElevatedButton.styleFrom(
              backgroundColor: theme.primaryColor,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(
                horizontal: AppTheme.paddingMedium,
                vertical: AppTheme.paddingSmall,
              ),
            ),
            icon: Icon(_isLoading ? Icons.hourglass_empty : Icons.refresh),
            label: Text(_isLoading ? "Loading..." : "Check Again"),
          ),
        ],
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

// MyTicketsTab yang diperbaharui
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
  Timer? _expirationTimer;

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

    // Timer untuk memeriksa tiket kadaluarsa setiap 1 menit
    _expirationTimer = Timer.periodic(const Duration(minutes: 1), (timer) {
      if (mounted) {
        final ticketProvider = Provider.of<TicketProvider>(
          context,
          listen: false,
        );
        ticketProvider.checkAndMoveExpiredTickets();
      }
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _debounceTimer?.cancel();
    _expirationTimer?.cancel();
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

      // Check and move expired tickets
      ticketProvider.checkAndMoveExpiredTickets();

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
        title: const Text('Tiket Saya'),
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          tabs: const [Tab(text: 'Aktif'), Tab(text: 'Riwayat')],
          labelStyle: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: AppTheme.fontSizeRegular,
          ),
          indicatorWeight: 3,
          indicatorColor: theme.primaryColor,
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
                        ? _buildEmptyTicketsView(
                          'Tidak Ada Tiket Aktif',
                          'Anda belum memiliki tiket aktif. Pesan tiket kapal untuk lihat disini.',
                          Icons.confirmation_number_outlined,
                          'Pesan Tiket Kapal',
                          () {
                            Navigator.pushReplacementNamed(
                              context,
                              AppRoutes.home,
                            );
                          },
                        )
                        : _buildTicketList(
                          ticketProvider.activeTickets,
                          ticketProvider,
                          isActive: true,
                        ),
              ),

              // History tickets tab
              RefreshIndicator(
                onRefresh: _handleRefresh,
                child:
                    ticketProvider.isLoadingTicketHistory
                        ? const Center(child: LoadingIndicator())
                        : ticketProvider.ticketHistory.isEmpty
                        ? _buildEmptyTicketsView(
                          'Tidak Ada Riwayat Tiket',
                          'Tiket yang sudah digunakan, kadaluarsa, atau dibatalkan akan muncul disini.',
                          Icons.history,
                          null,
                          null,
                        )
                        : _buildTicketList(
                          ticketProvider.ticketHistory,
                          ticketProvider,
                          isActive: false,
                        ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildEmptyTicketsView(
    String title,
    String subtitle,
    IconData icon,
    String? buttonText,
    VoidCallback? onButtonPressed,
  ) {
    final theme = Theme.of(context);

    return Center(
      child: ListView(
        shrinkWrap: true,
        padding: const EdgeInsets.all(AppTheme.paddingLarge),
        physics: const AlwaysScrollableScrollPhysics(), // Allow pull to refresh
        children: [
          Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 80, color: theme.hintColor),
              const SizedBox(height: AppTheme.paddingMedium),
              Text(
                title,
                style: TextStyle(
                  fontSize: AppTheme.fontSizeLarge,
                  fontWeight: FontWeight.bold,
                  color: theme.textTheme.bodyLarge?.color,
                ),
              ),
              const SizedBox(height: AppTheme.paddingSmall),
              Text(
                subtitle,
                style: TextStyle(
                  fontSize: AppTheme.fontSizeRegular,
                  color: theme.textTheme.bodyMedium?.color,
                ),
                textAlign: TextAlign.center,
              ),
              if (buttonText != null && onButtonPressed != null) ...[
                const SizedBox(height: AppTheme.paddingLarge),
                ElevatedButton.icon(
                  onPressed: onButtonPressed,
                  icon: const Icon(Icons.search),
                  label: Text(buttonText),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: theme.primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingLarge,
                      vertical: AppTheme.paddingRegular,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTicketList(
    List<dynamic> tickets,
    TicketProvider ticketProvider, {
    bool isActive = true,
  }) {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(AppTheme.paddingMedium),
      itemCount: tickets.length,
      itemBuilder: (context, index) {
        final ticket = tickets[index];
        return _buildTicketCard(ticket, ticketProvider, isActive: isActive);
      },
    );
  }

  Widget _buildTicketCard(
    dynamic ticket,
    TicketProvider ticketProvider, {
    bool isActive = true,
  }) {
    final theme = Theme.of(context);
    final hasSchedule = ticket.schedule != null;

    // Format date
    String departureDate = '';
    String departureTime = '';
    if (hasSchedule) {
      final formatter = DateFormat('EEE, dd MMM yyyy');
      departureDate = formatter.format(ticket.schedule!.departureTime);
      departureTime = ticket.schedule!.formattedDepartureTime;
    }

    // Route name
    String routeName = '';
    if (hasSchedule && ticket.schedule!.route != null) {
      routeName = ticket.schedule!.route!.routeName;
    }

    // Jenis tiket (penumpang atau kendaraan)
    final bool isVehicleTicket = ticket.vehicle != null;
    final String ticketInfo =
        isVehicleTicket
            ? '${ticket.vehicle!.typeText} - ${ticket.vehicle!.licensePlate}'
            : ticket.passenger != null
            ? ticket.passenger!.name
            : 'Passenger Ticket';

    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: AppTheme.paddingMedium),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
      ),
      child: InkWell(
        onTap: () {
          ticketProvider.setSelectedTicket(ticket.id);
          Navigator.pushNamed(
            context,
            AppRoutes.ticketDetail,
            arguments: {'ticketId': ticket.id},
          );
        },
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
        child: Column(
          children: [
            // Header dengan informasi rute
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              decoration: BoxDecoration(
                color: theme.primaryColor.withOpacity(0.1),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(AppTheme.borderRadiusMedium),
                  topRight: Radius.circular(AppTheme.borderRadiusMedium),
                ),
              ),
              child: Row(
                children: [
                  // Ferry icon
                  Container(
                    padding: const EdgeInsets.all(AppTheme.paddingSmall),
                    decoration: BoxDecoration(
                      color: theme.primaryColor.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(
                        AppTheme.borderRadiusRegular,
                      ),
                    ),
                    child: Icon(
                      Icons.directions_boat,
                      color: theme.primaryColor,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: AppTheme.paddingMedium),

                  // Route info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          routeName.isEmpty ? 'Unknown Route' : routeName,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: AppTheme.fontSizeMedium,
                          ),
                        ),
                        if (hasSchedule)
                          Text(
                            '$departureDate · $departureTime',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeSmall,
                              color: theme.textTheme.bodyMedium?.color,
                            ),
                          ),
                      ],
                    ),
                  ),

                  // Status badge
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingRegular,
                      vertical: AppTheme.paddingXSmall,
                    ),
                    decoration: BoxDecoration(
                      color: ticket.statusColor,
                      borderRadius: BorderRadius.circular(
                        AppTheme.borderRadiusRound,
                      ),
                    ),
                    child: Text(
                      ticket.statusText,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: AppTheme.fontSizeSmall,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Divider with dotted line
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: AppTheme.paddingSmall,
              ),
              child: Row(
                children: List.generate(40, (index) {
                  return Expanded(
                    child: Container(
                      height: 1,
                      margin: const EdgeInsets.symmetric(horizontal: 1),
                      color:
                          index % 2 == 0
                              ? theme.dividerColor
                              : Colors.transparent,
                    ),
                  );
                }),
              ),
            ),

            // Ticket body
            Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Row(
                children: [
                  // Icon based on ticket type (passenger or vehicle)
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: ticket.statusColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(
                        AppTheme.borderRadiusRegular,
                      ),
                    ),
                    child: Icon(
                      isVehicleTicket ? Icons.directions_car : Icons.person,
                      color: ticket.statusColor,
                    ),
                  ),
                  const SizedBox(width: AppTheme.paddingMedium),

                  // Ticket info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          ticketInfo,
                          style: const TextStyle(
                            fontWeight: FontWeight.w500,
                            fontSize: AppTheme.fontSizeRegular,
                          ),
                        ),
                        Text(
                          'Tiket: ${ticket.ticketNumber}',
                          style: TextStyle(
                            color: theme.textTheme.bodyMedium?.color,
                            fontSize: AppTheme.fontSizeSmall,
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Action button based on status
                  isActive && ticket.isActive
                      ? ElevatedButton(
                        onPressed: () {
                          ticketProvider.setSelectedTicket(ticket.id);
                          Navigator.pushNamed(
                            context,
                            AppRoutes.ticketDetail,
                            arguments: {'ticketId': ticket.id},
                          );
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: theme.primaryColor,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingRegular,
                            vertical: AppTheme.paddingXSmall,
                          ),
                          minimumSize: const Size(0, 0),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusRegular,
                            ),
                          ),
                        ),
                        child: const Text('Lihat'),
                      )
                      : OutlinedButton(
                        onPressed: () {
                          ticketProvider.setSelectedTicket(ticket.id);
                          Navigator.pushNamed(
                            context,
                            AppRoutes.ticketDetail,
                            arguments: {'ticketId': ticket.id},
                          );
                        },
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingRegular,
                            vertical: AppTheme.paddingXSmall,
                          ),
                          minimumSize: const Size(0, 0),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusRegular,
                            ),
                          ),
                        ),
                        child: const Text('Detail'),
                      ),
                ],
              ),
            ),

            // Ferry info for active tickets
            if (isActive &&
                ticket.isActive &&
                hasSchedule &&
                ticket.schedule!.ferry != null)
              Padding(
                padding: const EdgeInsets.only(
                  left: AppTheme.paddingMedium,
                  right: AppTheme.paddingMedium,
                  bottom: AppTheme.paddingMedium,
                ),
                child: Container(
                  padding: const EdgeInsets.all(AppTheme.paddingSmall),
                  decoration: BoxDecoration(
                    color: Colors.grey.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusRegular,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.info_outline,
                        size: 14,
                        color: theme.textTheme.bodyMedium?.color,
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          'Kapal: ${ticket.schedule!.ferry!.name}',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeSmall,
                            color: theme.textTheme.bodyMedium?.color,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
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

// Fixed ScheduleCard implementation with improved design
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

            // Book button
            Padding(
              padding: const EdgeInsets.only(
                left: AppTheme.paddingSmall,
                right: AppTheme.paddingSmall,
                bottom: AppTheme.paddingSmall,
              ),
              child: ElevatedButton(
                onPressed: onTap,
                style: ElevatedButton.styleFrom(
                  backgroundColor: theme.primaryColor,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusRegular,
                    ),
                  ),
                  minimumSize: Size(double.infinity, 36),
                ),
                child: Text(
                  'Book Now',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: AppTheme.fontSizeSmall,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
