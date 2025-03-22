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

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;
  bool _isInitialized = false;

  @override
  void initState() {
    super.initState();
    // Gunakan WidgetsBinding untuk memanggil setelah build selesai
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadInitialData();
    });
  }

  Future<void> _loadInitialData() async {
    if (!mounted) return;

    // Load popular routes
    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
    await ferryProvider.fetchRoutes();

    // Load active tickets
    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    await ticketProvider.fetchActiveTickets();

    if (mounted) {
      setState(() {
        _isInitialized = true;
      });
    }
  }

  void _navigateToTickets() {
    setState(() {
      _currentIndex = 1; // Switch to tickets tab
    });
  }

  void _navigateToProfile() {
    setState(() {
      _currentIndex = 2; // Switch to profile tab
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: [
          HomeTab(
            onTicketsTap: _navigateToTickets,
            onProfileTap: _navigateToProfile,
          ),
          const MyTicketsTab(),
          const ProfileTab(),
        ],
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
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

  const HomeTab({
    Key? key,
    required this.onTicketsTap,
    required this.onProfileTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      body: Consumer<FerryProvider>(
        builder: (context, ferryProvider, _) {
          return SafeArea(
            child: RefreshIndicator(
              onRefresh: () async {
                await ferryProvider.fetchRoutes();
              },
              child: SingleChildScrollView(
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
                                    final user = authProvider.user;
                                    return Text(
                                      user?.name ?? 'Guest',
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
                          Consumer<AuthProvider>(
                            builder: (context, authProvider, _) {
                              return GestureDetector(
                                onTap: onProfileTap,
                                child: CircleAvatar(
                                  radius: 24,
                                  backgroundColor: AppTheme.primaryColor,
                                  child: Text(
                                    authProvider.user?.name?.isNotEmpty == true
                                        ? authProvider.user!.name.substring(
                                          0,
                                          1,
                                        )
                                        : 'G',
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
                          TextButton(
                            onPressed: () {
                              // Show all routes
                            },
                            child: const Text('See All'),
                          ),
                        ],
                      ),
                    ),

                    // Popular Routes List
                    SizedBox(
                      height: 190, // Adjust as needed for your card design
                      child:
                          ferryProvider.isLoadingRoutes
                              ? const Center(child: LoadingIndicator())
                              : ferryProvider.routes.isEmpty
                              ? Center(
                                child: Text(
                                  'No routes available',
                                  style: TextStyle(color: theme.hintColor),
                                ),
                              )
                              : ListView.builder(
                                scrollDirection: Axis.horizontal,
                                itemCount:
                                    ferryProvider.routes.length > 5
                                        ? 5 // Limit to 5 popular routes
                                        : ferryProvider.routes.length,
                                itemBuilder: (context, index) {
                                  final route = ferryProvider.routes[index];
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

                    // Upcoming Trips List
                    Consumer<TicketProvider>(
                      builder: (context, ticketProvider, _) {
                        if (ticketProvider.isLoadingActiveTickets) {
                          return const Padding(
                            padding: EdgeInsets.all(AppTheme.paddingMedium),
                            child: LoadingIndicator(),
                          );
                        }

                        if (ticketProvider.activeTickets.isEmpty) {
                          return Padding(
                            padding: const EdgeInsets.all(
                              AppTheme.paddingLarge,
                            ),
                            child: Center(
                              child: Column(
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
                                  const SizedBox(height: AppTheme.paddingSmall),
                                  Text(
                                    'Book a ferry ticket to see your upcoming trips here',
                                    style: TextStyle(
                                      color: theme.textTheme.bodyMedium?.color,
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
                            ),
                          );
                        }

                        // Display the first 3 active tickets
                        final tickets = ticketProvider.activeTickets;
                        final displayTickets =
                            tickets.length > 3
                                ? tickets.sublist(0, 3)
                                : tickets;

                        return ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: displayTickets.length,
                          itemBuilder: (context, index) {
                            final ticket = displayTickets[index];
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
                                      color: Colors.black.withOpacity(0.05),
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
                                        borderRadius: BorderRadius.circular(
                                          AppTheme.borderRadiusRegular,
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
                                            ticket.schedule?.route?.routeName ??
                                                'Unknown Route',
                                            style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize:
                                                  AppTheme.fontSizeRegular,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
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
                                                    AppTheme.fontSizeSmall,
                                              ),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
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
                          },
                        );
                      },
                    ),

                    const SizedBox(height: AppTheme.paddingLarge),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

class MyTicketsTab extends StatefulWidget {
  const MyTicketsTab({Key? key}) : super(key: key);

  @override
  State<MyTicketsTab> createState() => _MyTicketsTabState();
}

class _MyTicketsTabState extends State<MyTicketsTab>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);

    // Perbaikan: Gunakan addPostFrameCallback untuk memanggil _loadTickets
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadTickets();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadTickets() async {
    if (!mounted) return;

    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    await ticketProvider.fetchActiveTickets();
    await ticketProvider.fetchTicketHistory();
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
                onRefresh: () async {
                  await ticketProvider.fetchActiveTickets();
                },
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
                                                  if (ticket.passenger != null)
                                                    Text(
                                                      'Passenger: ${ticket.passenger!.name}',
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
                                                    ),
                                                ],
                                              ),
                                            ),
                                            Container(
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    horizontal:
                                                        AppTheme.paddingRegular,
                                                    vertical:
                                                        AppTheme.paddingXSmall,
                                                  ),
                                              decoration: BoxDecoration(
                                                color: ticket.statusColor
                                                    .withOpacity(0.1),
                                                borderRadius:
                                                    BorderRadius.circular(
                                                      AppTheme
                                                          .borderRadiusRound,
                                                    ),
                                              ),
                                              child: Text(
                                                ticket.statusText,
                                                style: TextStyle(
                                                  color: ticket.statusColor,
                                                  fontWeight: FontWeight.w500,
                                                  fontSize:
                                                      AppTheme.fontSizeSmall,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(
                                          height: AppTheme.paddingRegular,
                                        ),
                                        const Divider(),
                                        const SizedBox(
                                          height: AppTheme.paddingRegular,
                                        ),
                                        if (ticket.schedule != null) ...[
                                          Row(
                                            children: [
                                              const Icon(
                                                Icons.calendar_today,
                                                size: 16,
                                              ),
                                              const SizedBox(
                                                width: AppTheme.paddingSmall,
                                              ),
                                              Text(
                                                ticket
                                                    .schedule!
                                                    .formattedDepartureDate,
                                                style: const TextStyle(
                                                  fontSize:
                                                      AppTheme.fontSizeRegular,
                                                  fontWeight: FontWeight.w500,
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(
                                            height: AppTheme.paddingSmall,
                                          ),
                                          Row(
                                            children: [
                                              const Icon(
                                                Icons.access_time,
                                                size: 16,
                                              ),
                                              const SizedBox(
                                                width: AppTheme.paddingSmall,
                                              ),
                                              Text(
                                                'Departure Time: ${ticket.schedule!.formattedDepartureTime}',
                                                style: const TextStyle(
                                                  fontSize:
                                                      AppTheme.fontSizeRegular,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],
                                        const SizedBox(
                                          height: AppTheme.paddingRegular,
                                        ),
                                        ElevatedButton(
                                          onPressed: () {
                                            ticketProvider.setSelectedTicket(
                                              ticket.id,
                                            );
                                            Navigator.pushNamed(
                                              context,
                                              AppRoutes.ticketDetail,
                                              arguments: {
                                                'ticketId': ticket.id,
                                              },
                                            );
                                          },
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor:
                                                AppTheme.primaryColor,
                                            foregroundColor: Colors.white,
                                            shape: RoundedRectangleBorder(
                                              borderRadius:
                                                  BorderRadius.circular(
                                                    AppTheme
                                                        .borderRadiusRegular,
                                                  ),
                                            ),
                                            padding: const EdgeInsets.symmetric(
                                              horizontal: AppTheme.paddingLarge,
                                              vertical: AppTheme.paddingRegular,
                                            ),
                                          ),
                                          child: const Text('View Ticket'),
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

              // History tab
              RefreshIndicator(
                onRefresh: () async {
                  await ticketProvider.fetchTicketHistory();
                },
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
                          itemCount: ticketProvider.ticketHistory.length,
                          itemBuilder: (context, index) {
                            final ticket = ticketProvider.ticketHistory[index];
                            return ListTile(
                              title: Text(
                                ticket.schedule?.route?.routeName ??
                                    'Unknown Route',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              subtitle: Text(
                                ticket.schedule != null
                                    ? '${ticket.schedule!.formattedDepartureDate}, ${ticket.schedule!.formattedDepartureTime}'
                                    : 'Unknown date and time',
                              ),
                              trailing: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: AppTheme.paddingRegular,
                                  vertical: AppTheme.paddingXSmall,
                                ),
                                decoration: BoxDecoration(
                                  color: ticket.statusColor.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(
                                    AppTheme.borderRadiusRound,
                                  ),
                                ),
                                child: Text(
                                  ticket.statusText,
                                  style: TextStyle(
                                    color: ticket.statusColor,
                                    fontWeight: FontWeight.w500,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                              ),
                              onTap: () {
                                ticketProvider.setSelectedTicket(ticket.id);
                                Navigator.pushNamed(
                                  context,
                                  AppRoutes.ticketDetail,
                                  arguments: {'ticketId': ticket.id},
                                );
                              },
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

class ProfileTab extends StatelessWidget {
  const ProfileTab({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('My Profile')),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, _) {
          final user = authProvider.user;

          if (user == null) {
            return const Center(
              child: Text('Please login to view your profile'),
            );
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(AppTheme.paddingMedium),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Profile header
                Center(
                  child: Column(
                    children: [
                      CircleAvatar(
                        radius: 50,
                        backgroundColor: AppTheme.primaryColor,
                        child: Text(
                          user.name.isNotEmpty
                              ? user.name.substring(0, 1)
                              : 'U',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 36,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(height: AppTheme.paddingMedium),
                      Text(
                        user.name,
                        style: const TextStyle(
                          fontSize: AppTheme.fontSizeLarge,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        user.email,
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeRegular,
                          color: theme.textTheme.bodyMedium?.color,
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Profile info card
                Container(
                  decoration: BoxDecoration(
                    color: theme.cardColor,
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusMedium,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 4,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      _buildProfileItem(
                        icon: Icons.phone,
                        title: 'Phone Number',
                        subtitle: user.phone,
                        onTap: () {},
                      ),
                      const Divider(),
                      _buildProfileItem(
                        icon: Icons.badge,
                        title: 'ID Number',
                        subtitle: user.identityNumber ?? 'Not set',
                        onTap: () {},
                      ),
                      if (user.address != null) ...[
                        const Divider(),
                        _buildProfileItem(
                          icon: Icons.location_on,
                          title: 'Address',
                          subtitle: user.address!,
                          onTap: () {},
                        ),
                      ],
                    ],
                  ),
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Settings
                Container(
                  decoration: BoxDecoration(
                    color: theme.cardColor,
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusMedium,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 4,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      _buildProfileItem(
                        icon: Icons.person,
                        title: 'Edit Profile',
                        onTap: () {
                          Navigator.pushNamed(context, AppRoutes.editProfile);
                        },
                      ),
                      const Divider(),
                      _buildProfileItem(
                        icon: Icons.settings,
                        title: 'Settings',
                        onTap: () {
                          // Navigate to settings
                        },
                      ),
                      const Divider(),
                      _buildProfileItem(
                        icon: Icons.help,
                        title: 'Help & Support',
                        onTap: () {
                          // Navigate to help
                        },
                      ),
                      const Divider(),
                      _buildProfileItem(
                        icon: Icons.info,
                        title: 'About',
                        onTap: () {
                          // Show about dialog
                        },
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Logout button
                ElevatedButton.icon(
                  onPressed: () async {
                    final confirm = await showDialog<bool>(
                      context: context,
                      builder:
                          (context) => AlertDialog(
                            title: const Text('Confirm Logout'),
                            content: const Text(
                              'Are you sure you want to logout?',
                            ),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.pop(context, false),
                                child: const Text('Cancel'),
                              ),
                              TextButton(
                                onPressed: () => Navigator.pop(context, true),
                                child: const Text('Logout'),
                              ),
                            ],
                          ),
                    );

                    if (confirm == true) {
                      await authProvider.logout();
                      if (context.mounted) {
                        Navigator.pushReplacementNamed(
                          context,
                          AppRoutes.login,
                        );
                      }
                    }
                  },
                  icon: const Icon(Icons.logout),
                  label: const Text('Logout'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.red,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingLarge,
                      vertical: AppTheme.paddingMedium,
                    ),
                  ),
                ),

                const SizedBox(height: AppTheme.paddingLarge),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildProfileItem({
    required IconData icon,
    required String title,
    String? subtitle,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(icon),
      title: Text(title, style: const TextStyle(fontWeight: FontWeight.w500)),
      subtitle: subtitle != null ? Text(subtitle) : null,
      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
      onTap: onTap,
    );
  }
}
