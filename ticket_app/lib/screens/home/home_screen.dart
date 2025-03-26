import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

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

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;
  bool _isInitialized = false;
  bool _isLoadingData = false;
  DateTime?
  _lastDataLoad; // Tambahkan variable untuk tracking waktu load terakhir

  @override
  void initState() {
    super.initState();
    // Use WidgetsBinding to call after build is complete
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _loadInitialData();
      }
    });
  }

  @override
  void dispose() {
    // Clean up any resources
    _isInitialized = false;
    super.dispose();
  }

  Future<void> _loadInitialData() async {
    if (!mounted || _isLoadingData) return;

    // Tambahkan throttling untuk mencegah reload terlalu sering
    if (_lastDataLoad != null &&
        DateTime.now().difference(_lastDataLoad!).inSeconds < 10) {
      return;
    }

    setState(() {
      _isLoadingData = true;
    });

    try {
      // Load data sequentially to avoid overwhelming the API
      if (mounted) {
        // Load popular routes
        final ferryProvider = Provider.of<FerryProvider>(
          context,
          listen: false,
        );
        await ferryProvider.fetchRoutes();
      }

      if (mounted) {
        // Load active tickets
        final ticketProvider = Provider.of<TicketProvider>(
          context,
          listen: false,
        );
        await ticketProvider.fetchActiveTickets();
      }

      if (mounted) {
        setState(() {
          _isInitialized = true;
          _isLoadingData = false;
          _lastDataLoad = DateTime.now(); // Update waktu load terakhir
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoadingData = false;
        });
        // Show error message if needed
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error loading data: $e')));
      }
    }
  }

  void _setCurrentIndex(int index) {
    if (mounted) {
      setState(() {
        _currentIndex = index;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: [
          HomeTab(
            onTicketsTap: () => _setCurrentIndex(2), // Switch to tickets tab
            onProfileTap: () => _setCurrentIndex(3), // Switch to profile tab
            isInitialized: _isInitialized,
            onRefresh: _loadInitialData,
          ),
          const RoutesScreen(), // Add Routes screen as 2nd tab
          const MyTicketsTab(),
          const ProfileTab(),
        ],
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: _setCurrentIndex,
        type: BottomNavigationBarType.fixed, // Important for more than 3 items
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
}

class HomeTab extends StatelessWidget {
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
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: onRefresh,
          child: Selector<FerryProvider, bool>(
            selector: (_, provider) => provider.isLoadingRoutes,
            builder: (context, isLoadingRoutes, child) {
              return SingleChildScrollView(
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
                                Selector<AuthProvider, String?>(
                                  selector:
                                      (_, provider) => provider.user?.name,
                                  builder: (context, userName, _) {
                                    return Text(
                                      userName ?? 'Guest',
                                      style: TextStyle(
                                        fontSize: AppTheme.fontSizeXLarge,
                                        fontWeight: FontWeight.bold,
                                        color:
                                            theme.textTheme.displaySmall?.color,
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
                          Selector<AuthProvider, String>(
                            selector:
                                (_, provider) =>
                                    provider.user?.name.isNotEmpty == true
                                        ? provider.user!.name.substring(0, 1)
                                        : 'G',
                            builder: (context, initial, _) {
                              return GestureDetector(
                                onTap: onProfileTap,
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
                            onPressed:
                                () => _setCurrentIndex(
                                  context,
                                  1,
                                ), // Navigate to Routes tab
                            icon: const Icon(Icons.map),
                            label: const Text('See All'),
                            style: TextButton.styleFrom(
                              foregroundColor: AppTheme.primaryColor,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Popular Routes List
                    SizedBox(
                      height: 190,
                      child:
                          isLoadingRoutes
                              ? const Center(child: LoadingIndicator())
                              : Selector<FerryProvider, List<dynamic>>(
                                selector: (_, provider) => provider.routes,
                                builder: (context, routes, _) {
                                  if (routes.isEmpty) {
                                    return Center(
                                      child: Text(
                                        'No routes available',
                                        style: TextStyle(
                                          color: theme.hintColor,
                                        ),
                                      ),
                                    );
                                  }
                                  return ListView.builder(
                                    scrollDirection: Axis.horizontal,
                                    itemCount:
                                        routes.length > 5 ? 5 : routes.length,
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
                                              'departurePort':
                                                  route.departurePort,
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

                    // Upcoming Trips Section
                    Padding(
                      padding: const EdgeInsets.all(AppTheme.paddingMedium),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Your Upcoming Trips',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeLarge,
                              fontWeight: FontWeight.bold,
                              color: theme.textTheme.displaySmall?.color,
                            ),
                          ),
                          TextButton(
                            onPressed: onTicketsTap,
                            child: const Text('See All'),
                          ),
                        ],
                      ),
                    ),

                    // Upcoming Trips List - Menggunakan Selector untuk mengoptimalkan performa
                    Selector<TicketProvider, bool>(
                      selector:
                          (_, provider) => provider.isLoadingActiveTickets,
                      builder: (context, isLoadingActiveTickets, _) {
                        if (isLoadingActiveTickets) {
                          return const Padding(
                            padding: EdgeInsets.all(AppTheme.paddingMedium),
                            child: Center(child: LoadingIndicator()),
                          );
                        }

                        return Selector<TicketProvider, List<dynamic>>(
                          selector: (_, provider) => provider.activeTickets,
                          builder: (context, activeTickets, _) {
                            if (activeTickets.isEmpty) {
                              return Container(
                                padding: const EdgeInsets.all(
                                  AppTheme.paddingLarge,
                                ),
                                alignment: Alignment.center,
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(
                                      Icons.directions_boat_outlined,
                                      size: 64,
                                      color: theme.hintColor,
                                    ),
                                    const SizedBox(
                                      height: AppTheme.paddingMedium,
                                    ),
                                    Text(
                                      'No upcoming trips',
                                      style: TextStyle(
                                        color: theme.textTheme.bodyLarge?.color,
                                        fontSize: AppTheme.fontSizeMedium,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                    const SizedBox(
                                      height: AppTheme.paddingSmall,
                                    ),
                                    Text(
                                      'Book a ferry ticket to see your upcoming trips here',
                                      style: TextStyle(
                                        color:
                                            theme.textTheme.bodyMedium?.color,
                                        fontSize: AppTheme.fontSizeRegular,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                    const SizedBox(
                                      height: AppTheme.paddingMedium,
                                    ),
                                    ElevatedButton.icon(
                                      onPressed: () {
                                        // Scroll to search form
                                      },
                                      icon: const Icon(Icons.search),
                                      label: const Text('Find Tickets'),
                                    ),
                                  ],
                                ),
                              );
                            }

                            // Display the first 3 active tickets
                            final tickets = activeTickets;
                            final displayTickets =
                                tickets.length > 3
                                    ? tickets.sublist(0, 3)
                                    : tickets;

                            // Use a Column with direct children to avoid overflow
                            return Column(
                              children:
                                  displayTickets.map((ticket) {
                                    return GestureDetector(
                                      onTap: () {
                                        Provider.of<TicketProvider>(
                                          context,
                                          listen: false,
                                        ).setSelectedTicket(ticket.id);
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
                                        padding: const EdgeInsets.all(
                                          AppTheme.paddingRegular,
                                        ),
                                        decoration: BoxDecoration(
                                          color: theme.cardColor,
                                          borderRadius: BorderRadius.circular(
                                            AppTheme.borderRadiusRegular,
                                          ),
                                          boxShadow: [
                                            BoxShadow(
                                              color: Colors.black.withOpacity(
                                                0.05,
                                              ),
                                              blurRadius: 4,
                                              offset: const Offset(0, 2),
                                            ),
                                          ],
                                        ),
                                        child: Row(
                                          children: [
                                            Container(
                                              width: 60,
                                              height: 60,
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
                                                size: 30,
                                              ),
                                            ),
                                            const SizedBox(
                                              width: AppTheme.paddingRegular,
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
                                                              .fontSizeRegular,
                                                    ),
                                                    maxLines: 1,
                                                    overflow:
                                                        TextOverflow.ellipsis,
                                                  ),
                                                  const SizedBox(height: 4),
                                                  if (ticket.schedule != null)
                                                    Text(
                                                      'Departure: ${ticket.schedule!.formattedDepartureDate}, ${ticket.schedule!.formattedDepartureTime}',
                                                      style: TextStyle(
                                                        color:
                                                            theme
                                                                .textTheme
                                                                .bodyMedium
                                                                ?.color,
                                                        fontSize:
                                                            AppTheme
                                                                .fontSizeSmall,
                                                      ),
                                                      maxLines: 1,
                                                      overflow:
                                                          TextOverflow.ellipsis,
                                                    ),
                                                ],
                                              ),
                                            ),
                                            const Icon(
                                              Icons.arrow_forward_ios,
                                              size: 14,
                                              color: AppTheme.primaryColor,
                                            ),
                                          ],
                                        ),
                                      ),
                                    );
                                  }).toList(),
                            );
                          },
                        );
                      },
                    ),

                    const SizedBox(height: AppTheme.paddingLarge),
                  ],
                ),
              );
            },
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

// MyTicketsTab & ProfileTab remain largely the same, with mounted checks added
class MyTicketsTab extends StatefulWidget {
  const MyTicketsTab({Key? key}) : super(key: key);

  @override
  State<MyTicketsTab> createState() => _MyTicketsTabState();
}

class _MyTicketsTabState extends State<MyTicketsTab>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);

    // Use addPostFrameCallback to call _loadTickets
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _loadTickets();
      }
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadTickets() async {
    if (!mounted || _isLoading) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final ticketProvider = Provider.of<TicketProvider>(
        context,
        listen: false,
      );
      await ticketProvider.fetchActiveTickets();

      if (mounted) {
        await ticketProvider.fetchTicketHistory();
      }

      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    } catch (e) {
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
                onRefresh: _loadTickets,
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
                          // Enable physics for refresh indicator
                          physics: const AlwaysScrollableScrollPhysics(),
                          itemCount: ticketProvider.activeTickets.length,
                          itemBuilder: (context, index) {
                            // Item builder for active tickets
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
                              // Rest of ticket item rendering code
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
                                  // Rest of the card implementation...
                                  child: Padding(
                                    padding: const EdgeInsets.all(
                                      AppTheme.paddingMedium,
                                    ),
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        // Content of ticket card
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
                                                  // Other content...
                                                ],
                                              ),
                                            ),
                                          ],
                                        ),
                                        // Rest of card content
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
              ),

              // History tab with similar updates...
              RefreshIndicator(
                onRefresh: _loadTickets,
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
                            // History ticket item builder
                            // Similar structure to active tickets
                            return Container(); // Replace with actual widget
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

// Note: ProfileTab remains the same but should have similar mounted checks
class ProfileTab extends StatelessWidget {
  const ProfileTab({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return const ProfileScreen();
  }
}
