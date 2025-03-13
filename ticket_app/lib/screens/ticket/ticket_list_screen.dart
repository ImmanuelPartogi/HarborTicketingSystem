import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/ticket_provider.dart';
import '../../widgets/common/loading_indicator.dart';
import '../../widgets/common/ticket_card.dart';

class TicketListScreen extends StatefulWidget {
  const TicketListScreen({Key? key}) : super(key: key);

  @override
  State<TicketListScreen> createState() => _TicketListScreenState();
}

class _TicketListScreenState extends State<TicketListScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  
  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadTickets();
  }
  
  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }
  
  Future<void> _loadTickets() async {
    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    await ticketProvider.fetchActiveTickets();
    await ticketProvider.fetchTicketHistory();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Tickets'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Active'),
            Tab(text: 'History'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // Active tickets tab
          _buildActiveTicketsTab(),
          
          // History tickets tab
          _buildHistoryTicketsTab(),
        ],
      ),
    );
  }
  
  Widget _buildActiveTicketsTab() {
    return Consumer<TicketProvider>(
      builder: (context, ticketProvider, _) {
        if (ticketProvider.isLoadingActiveTickets) {
          return const Center(child: LoadingIndicator(message: 'Loading active tickets...'));
        }
        
        if (ticketProvider.activeTickets.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.confirmation_number_outlined,
                  size: 64,
                  color: Theme.of(context).hintColor,
                ),
                const SizedBox(height: AppTheme.paddingMedium),
                const Text(
                  'No Active Tickets',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeLarge,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: AppTheme.paddingSmall),
                const Text(
                  'You don\'t have any active tickets',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeRegular,
                  ),
                ),
                const SizedBox(height: AppTheme.paddingLarge),
                ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pushReplacementNamed(context, AppRoutes.home);
                  },
                  icon: const Icon(Icons.search),
                  label: const Text('Book a Ferry'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingLarge,
                      vertical: AppTheme.paddingRegular,
                    ),
                  ),
                ),
              ],
            ),
          );
        }
        
        // Group tickets by schedule
        final groupedTickets = ticketProvider.getTicketsGroupedBySchedule();
        
        return RefreshIndicator(
          onRefresh: () => ticketProvider.fetchActiveTickets(),
          child: ListView.builder(
            padding: const EdgeInsets.all(AppTheme.paddingMedium),
            itemCount: groupedTickets.length,
            itemBuilder: (context, index) {
              // Get schedule ID and tickets for this group
              final scheduleId = groupedTickets.keys.elementAt(index);
              final tickets = groupedTickets[scheduleId]!;
              
              // Get details from the first ticket
              final firstTicket = tickets.first;
              final schedule = firstTicket.schedule;
              
              if (schedule == null) {
                return const SizedBox.shrink();
              }
              
              return Container(
                margin: const EdgeInsets.only(bottom: AppTheme.paddingLarge),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Trip header
                    Container(
                      padding: const EdgeInsets.all(AppTheme.paddingMedium),
                      decoration: BoxDecoration(
                        color: AppTheme.primaryColor,
                        borderRadius: const BorderRadius.only(
                          topLeft: Radius.circular(AppTheme.borderRadiusMedium),
                          topRight: Radius.circular(AppTheme.borderRadiusMedium),
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(
                                Icons.directions_boat,
                                color: Colors.white,
                                size: 20,
                              ),
                              const SizedBox(width: AppTheme.paddingSmall),
                              Text(
                                schedule.route?.routeName ?? 'Unknown Route',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: AppTheme.fontSizeMedium,
                                ),
                              ),
                              const Spacer(),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: AppTheme.paddingRegular,
                                  vertical: AppTheme.paddingXSmall,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(AppTheme.borderRadiusRound),
                                ),
                                child: Text(
                                  schedule.statusText,
                                  style: TextStyle(
                                    color: schedule.isAvailable ? Colors.green : Colors.red,
                                    fontWeight: FontWeight.w600,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: AppTheme.paddingSmall),
                          Row(
                            children: [
                              const Icon(
                                Icons.calendar_today,
                                color: Colors.white70,
                                size: 16,
                              ),
                              const SizedBox(width: AppTheme.paddingSmall),
                              Text(
                                DateFormat('EEE, dd MMM yyyy').format(schedule.departureTime),
                                style: const TextStyle(
                                  color: Colors.white,
                                ),
                              ),
                              const SizedBox(width: AppTheme.paddingMedium),
                              const Icon(
                                Icons.access_time,
                                color: Colors.white70,
                                size: 16,
                              ),
                              const SizedBox(width: AppTheme.paddingSmall),
                              Text(
                                schedule.formattedDepartureTime,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Tickets list
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: const BorderRadius.only(
                          bottomLeft: Radius.circular(AppTheme.borderRadiusMedium),
                          bottomRight: Radius.circular(AppTheme.borderRadiusMedium),
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.1),
                            blurRadius: 4,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Padding(
                            padding: const EdgeInsets.all(AppTheme.paddingMedium),
                            child: Text(
                              '${tickets.length} ${tickets.length > 1 ? 'Tickets' : 'Ticket'}',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                          const Divider(height: 1),
                          ...tickets.map((ticket) => _buildTicketItem(ticket)).toList(),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        );
      },
    );
  }
  
  Widget _buildHistoryTicketsTab() {
    return Consumer<TicketProvider>(
      builder: (context, ticketProvider, _) {
        if (ticketProvider.isLoadingTicketHistory) {
          return const Center(child: LoadingIndicator(message: 'Loading ticket history...'));
        }
        
        if (ticketProvider.ticketHistory.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.history,
                  size: 64,
                  color: Theme.of(context).hintColor,
                ),
                const SizedBox(height: AppTheme.paddingMedium),
                const Text(
                  'No Ticket History',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeLarge,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: AppTheme.paddingSmall),
                const Text(
                  'Your past tickets will appear here',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeRegular,
                  ),
                ),
              ],
            ),
          );
        }
        
        return RefreshIndicator(
          onRefresh: () => ticketProvider.fetchTicketHistory(),
          child: ListView.builder(
            padding: const EdgeInsets.all(AppTheme.paddingMedium),
            itemCount: ticketProvider.ticketHistory.length,
            itemBuilder: (context, index) {
              final ticket = ticketProvider.ticketHistory[index];
              return TicketCard(
                ticket: ticket,
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
        );
      },
    );
  }
  
  Widget _buildTicketItem(ticket) {
    return InkWell(
      onTap: () {
        final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
        ticketProvider.setSelectedTicket(ticket.id);
        Navigator.pushNamed(
          context,
          AppRoutes.ticketDetail,
          arguments: {'ticketId': ticket.id},
        );
      },
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.paddingMedium),
        child: Row(
          children: [
            // Ticket icon
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: ticket.statusColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              ),
              child: Icon(
                Icons.confirmation_number_outlined,
                color: ticket.statusColor,
              ),
            ),
            
            const SizedBox(width: AppTheme.paddingMedium),
            
            // Ticket details
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (ticket.passenger != null)
                    Text(
                      ticket.passenger!.name,
                      style: const TextStyle(
                        fontWeight: FontWeight.w500,
                        fontSize: AppTheme.fontSizeRegular,
                      ),
                    ),
                  Text(
                    'Ticket: ${ticket.ticketNumber}',
                    style: TextStyle(
                      color: Theme.of(context).textTheme.bodyMedium?.color,
                      fontSize: AppTheme.fontSizeSmall,
                    ),
                  ),
                ],
              ),
            ),
            
            // View button
            ElevatedButton(
              onPressed: () {
                final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
                ticketProvider.setSelectedTicket(ticket.id);
                Navigator.pushNamed(
                  context,
                  AppRoutes.ticketDetail,
                  arguments: {'ticketId': ticket.id},
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                textStyle: const TextStyle(
                  fontSize: AppTheme.fontSizeSmall,
                  fontWeight: FontWeight.w500,
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: AppTheme.paddingRegular,
                  vertical: AppTheme.paddingXSmall,
                ),
                minimumSize: const Size(0, 0),
              ),
              child: const Text('View'),
            ),
          ],
        ),
      ),
    );
  }
}