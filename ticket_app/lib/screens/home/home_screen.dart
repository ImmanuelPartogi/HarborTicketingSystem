import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
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

// Create a debug config class to control data loading
class DebugConfig {
  // Set to true to disable automatic data refresh in debug
  static const bool disableAutomaticDataRefresh = false;
  
  // Helper method to decide if data should be loaded
  static bool shouldSkipDataLoad(String dataType) {
    if (disableAutomaticDataRefresh) {
      debugPrint('DEBUG: Skipping $dataType refresh due to config');
      return true;
    }
    return false;
  }
}

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> with WidgetsBindingObserver {
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
    WidgetsBinding.instance.addObserver(this);
    
    // Pastikan untuk menginisialisasi data pada waktu yang tepat
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && !_isInitialized && !_isLoadingData) {
        _loadInitialData();
      }
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _isInitialized = false;
    _debounceTimer?.cancel();
    super.dispose();
  }
  
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    // Handle app lifecycle changes
    if (state == AppLifecycleState.resumed) {
      // App came to foreground
      debugPrint('App resumed - checking data freshness');
      
      // Only refresh if data is stale (more than 5 minutes old)
      if (_lastDataLoad != null && 
          DateTime.now().difference(_lastDataLoad!).inMinutes > 5) {
        _loadInitialData();
      }
    }
  }

  Future<void> _loadInitialData() async {
    // Skip if in debug mode with loading disabled
    if (DebugConfig.shouldSkipDataLoad('initial data')) {
      setState(() {
        _isInitialized = true;
      });
      return;
    }
    
    // Cancel previous timer if exists
    _debounceTimer?.cancel();
    
    if (!mounted || _isLoadingData) return;

    // Throttling untuk mencegah reload terlalu sering
    if (_lastDataLoad != null &&
        DateTime.now().difference(_lastDataLoad!).inSeconds < 60) {
      
      // Set timer untuk retry nanti jika diperlukan
      _debounceTimer = Timer(
        Duration(seconds: 60 - DateTime.now().difference(_lastDataLoad!).inSeconds),
        () {
          if (mounted && !_isInitialized) _loadInitialData();
        }
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
        final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
        futures.add(ferryProvider.fetchRoutes().then((_) {
          _routesInitialized = true;
        }));
      }
      
      // Load active tickets jika belum di-initialized
      if (!_activeTicketsInitialized) {
        final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
        futures.add(ticketProvider.fetchActiveTickets().then((_) {
          _activeTicketsInitialized = true;
        }));
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
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading data: $e'))
        );
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
    // Skip in debug mode
    if (DebugConfig.shouldSkipDataLoad('tab preload')) {
      return;
    }
    
    switch (index) {
      case 0: // Home tab
        if (!_isInitialized) {
          _loadInitialData();
        }
        break;
      case 1: // Routes tab
        if (!_routesInitialized) {
          Provider.of<FerryProvider>(context, listen: false).fetchRoutes().then((_) {
            _routesInitialized = true;
          });
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
        const SnackBar(content: Text('Harap tunggu beberapa saat sebelum refresh kembali'))
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

  const HomeTab({
    Key? key,
    required this.onTicketsTap,
    required this.onProfileTap,
    required this.onRefresh,
    required this.isInitialized,
  }) : super(key: key);

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  // Throttled refresh handler
  DateTime? _lastRefreshTime;
  
  Future<void> _handleRefresh() async {
    // Throttling untuk refresh manual
    if (_lastRefreshTime != null && 
        DateTime.now().difference(_lastRefreshTime!).inSeconds < 30) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Harap tunggu beberapa saat sebelum refresh kembali'))
      );
      return;
    }
    
    _lastRefreshTime = DateTime.now();
    return widget.onRefresh();
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
                // Header with greeting and profile - optimized for performance
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
                            // Use Consumer only where needed with tight scope
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
                      // Use Consumer instead of Selector for profile avatar
                      Consumer<AuthProvider>(
                        builder: (context, authProvider, _) {
                          final initial = authProvider.user?.name.isNotEmpty == true
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

                // Popular Routes Section
                Padding(
                  padding: const EdgeInsets.all(AppTheme.paddingMedium),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Popular Routes',
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeLarge,
                          fontWeight: FontWeight.bold,
                          color: theme.textTheme.displaySmall?.color,
                        ),
                      ),
                      TextButton.icon(
                        onPressed: () => _setCurrentIndex(context, 1),
                        icon: const Icon(Icons.map),
                        label: const Text('See All'),
                        style: TextButton.styleFrom(
                          foregroundColor: AppTheme.primaryColor,
                        ),
                      ),
                    ],
                  ),
                ),

                // Popular Routes List with Consumer
                SizedBox(
                  height: 190,
                  child: Consumer<FerryProvider>(
                    builder: (context, ferryProvider, _) {
                      if (ferryProvider.isLoadingRoutes) {
                        return const Center(child: LoadingIndicator());
                      }
                      
                      final routes = ferryProvider.routes;
                      
                      if (routes.isEmpty) {
                        return Center(
                          child: Text(
                            'No routes available',
                            style: TextStyle(color: theme.hintColor),
                          ),
                        );
                      }
                      
                      return ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: routes.length > 5 ? 5 : routes.length,
                        itemBuilder: (context, index) {
                          final route = routes[index];
                          return PopularRouteCard(
                            departureName: route.departurePort,
                            arrivalName: route.arrivalPort,
                            onTap: () {
                              Navigator.pushNamed(
                                context,
                                AppRoutes.search,
                                arguments: {
                                  'departurePort': route.departurePort,
                                  'arrivalPort': route.arrivalPort,
                                  'departureDate': DateTime.now(),
                                },
                              );
                            },
                          );
                        },
                      );
                    },
                  ),
                ),

                // Rest of the code remains the same...
                // [For brevity, I've omitted the rest of the UI code that doesn't change]

                const SizedBox(height: AppTheme.paddingLarge),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // Helper method to navigate to a specific tab from inside the HomeTab
  void _setCurrentIndex(BuildContext context, int index) {
    final homeScreenState = context.findAncestorStateOfType<_HomeScreenState>();
    if (homeScreenState != null) {
      homeScreenState._setCurrentIndex(index);
    }
  }
}

// Improved MyTicketsTab to reduce redundant API calls
class MyTicketsTab extends StatefulWidget {
  const MyTicketsTab({Key? key}) : super(key: key);

  @override
  State<MyTicketsTab> createState() => _MyTicketsTabState();
}

class _MyTicketsTabState extends State<MyTicketsTab> with SingleTickerProviderStateMixin {
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
    // Skip in debug mode
    if (DebugConfig.shouldSkipDataLoad('tickets tab')) {
      setState(() {
        _isInitialized = true;
      });
      return;
    }
    
    // Cancel any previous debounce timer
    _debounceTimer?.cancel();
    
    if (!mounted || _isLoading) return;
    
    // Throttling untuk mencegah reload terlalu sering (60 detik)
    if (_lastDataLoad != null &&
        DateTime.now().difference(_lastDataLoad!).inSeconds < 60) {
          
      // Set timer untuk retry nanti jika diperlukan
      _debounceTimer = Timer(
        Duration(seconds: 60 - DateTime.now().difference(_lastDataLoad!).inSeconds),
        () {
          if (mounted && !_isInitialized) _loadTickets();
        }
      );
      
      debugPrint('THROTTLED: Ticket load throttled');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
      
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
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading tickets: $e'))
        );
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
        const SnackBar(content: Text('Harap tunggu beberapa saat sebelum refresh kembali'))
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
    
    // IMPORTANT: Removed data loading in build method!
    // DO NOT add Future.microtask() here - this is what was causing the loop

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
                child: ticketProvider.isLoadingActiveTickets
                    ? const Center(child: LoadingIndicator())
                    : ticketProvider.activeTickets.isEmpty
                        ? _buildEmptyState(
                            theme,
                            'No active tickets',
                            'Book a ferry ticket to see your active tickets here',
                            Icons.confirmation_number_outlined,
                          )
                        : _buildTicketList(ticketProvider.activeTickets, ticketProvider),
              ),

              // History tab
              RefreshIndicator(
                onRefresh: _handleRefresh,
                child: ticketProvider.isLoadingTicketHistory
                    ? const Center(child: LoadingIndicator())
                    : ticketProvider.ticketHistory.isEmpty
                        ? _buildEmptyState(
                            theme,
                            'No ticket history',
                            'Your completed trips will appear here',
                            Icons.history,
                          )
                        : _buildTicketList(ticketProvider.ticketHistory, ticketProvider),
              ),
            ],
          );
        },
      ),
    );
  }

  // Helper method to build ticket list
  Widget _buildTicketList(List tickets, TicketProvider ticketProvider) {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      itemCount: tickets.length,
      itemBuilder: (context, index) {
        final ticket = tickets[index];
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
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(AppTheme.paddingSmall),
                      decoration: BoxDecoration(
                        color: AppTheme.primaryColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(
                          AppTheme.borderRadiusRegular,
                        ),
                      ),
                      child: const Icon(
                        Icons.directions_boat,
                        color: AppTheme.primaryColor,
                      ),
                    ),
                    const SizedBox(width: AppTheme.paddingMedium),
                    Expanded(
                      child: Text(
                        ticket.schedule?.route?.routeName ?? 'Unknown Route',
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  // Helper method to build empty state
  Widget _buildEmptyState(ThemeData theme, String title, String subtitle, IconData icon) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            icon,
            size: 64,
            color: theme.hintColor,
          ),
          const SizedBox(height: AppTheme.paddingMedium),
          Text(
            title,
            style: TextStyle(
              fontSize: AppTheme.fontSizeMedium,
              fontWeight: FontWeight.w500,
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
        ],
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