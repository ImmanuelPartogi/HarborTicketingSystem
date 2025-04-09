import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'dart:async';

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

class _TicketListScreenState extends State<TicketListScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadTickets();

    // Tambahkan timer untuk memeriksa tiket kadaluarsa secara berkala
    Timer.periodic(Duration(minutes: 5), (timer) {
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
    super.dispose();
  }

  Future<void> _loadTickets() async {
    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    await ticketProvider.fetchActiveTickets();
    await ticketProvider.fetchTicketHistory();
    ticketProvider.checkAndMoveExpiredTickets(); // Panggil saat loading tiket
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tiket Saya'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [Tab(text: 'Aktif'), Tab(text: 'Riwayat')],
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
          return const Center(
            child: LoadingIndicator(message: 'Memuat tiket aktif...'),
          );
        }

        // Debug logs
        print('=== PENGELOMPOKAN TIKET DEBUG ===');
        print('Jumlah tiket aktif: ${ticketProvider.activeTickets.length}');

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
                  'Tidak Ada Tiket Aktif',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeLarge,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: AppTheme.paddingSmall),
                const Text(
                  'Anda tidak memiliki tiket aktif',
                  style: TextStyle(fontSize: AppTheme.fontSizeRegular),
                ),
                const SizedBox(height: AppTheme.paddingLarge),
                ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pushReplacementNamed(context, AppRoutes.home);
                  },
                  icon: const Icon(Icons.search),
                  label: const Text('Pesan Tiket Kapal'),
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

        // Gunakan fungsi pengelompokan berdasarkan tanggal, kapal, dan tujuan
        final groupedTickets = ticketProvider.getTicketsGroupedByDateFerryDestination();
        print('Tiket dikelompokkan menjadi ${groupedTickets.length} grup');

        // Debug untuk melihat setiap grup
        groupedTickets.forEach((key, tickets) {
          final groupInfo = ticketProvider.getGroupInfo(key);
          print('Grup kunci: $key');
          print('  Jumlah tiket: ${tickets.length}');
          print('  Tanggal: ${groupInfo['date']}');
          print('  Kapal: ${groupInfo['ferry']}');
          print('  Tujuan: ${groupInfo['destination']}');
        });

        return RefreshIndicator(
          onRefresh: () => ticketProvider.fetchActiveTickets(forceReload: true),
          child: ListView.builder(
            padding: const EdgeInsets.all(AppTheme.paddingMedium),
            itemCount: groupedTickets.length,
            itemBuilder: (context, index) {
              // Dapatkan ID grup dan tiket untuk grup ini
              final key = groupedTickets.keys.elementAt(index);
              final tickets = groupedTickets[key]!;

              print(
                'Membangun grup $index (key: $key) dengan ${tickets.length} tiket',
              );

              return TicketGroupItemEnhanced(
                tickets: tickets,
                groupKey: key,
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
          return const Center(
            child: LoadingIndicator(message: 'Memuat riwayat tiket...'),
          );
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
                  'Tidak Ada Riwayat Tiket',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeLarge,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: AppTheme.paddingSmall),
                const Text(
                  'Tiket yang sudah lalu akan muncul di sini',
                  style: TextStyle(fontSize: AppTheme.fontSizeRegular),
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
}

class TicketGroupItemEnhanced extends StatefulWidget {
  final List<dynamic> tickets;
  final String groupKey;

  const TicketGroupItemEnhanced({
    Key? key, 
    required this.tickets, 
    required this.groupKey
  }) : super(key: key);

  @override
  State<TicketGroupItemEnhanced> createState() => _TicketGroupItemEnhancedState();
}

class _TicketGroupItemEnhancedState extends State<TicketGroupItemEnhanced> {
  bool isExpanded = false;

  @override
  Widget build(BuildContext context) {
    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    final firstTicket = widget.tickets.first;
    final schedule = firstTicket.schedule;
    final groupInfo = ticketProvider.getGroupInfo(widget.groupKey);

    if (schedule == null) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.only(bottom: AppTheme.paddingLarge),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Trip header (clickable to expand)
          GestureDetector(
            onTap: () {
              setState(() {
                isExpanded = !isExpanded;
              });
            },
            child: Container(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              decoration: BoxDecoration(
                color: AppTheme.primaryColor,
                borderRadius: isExpanded
                    ? const BorderRadius.only(
                        topLeft: Radius.circular(AppTheme.borderRadiusMedium),
                        topRight: Radius.circular(AppTheme.borderRadiusMedium),
                      )
                    : BorderRadius.circular(AppTheme.borderRadiusMedium),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Row 1: Tujuan
                  Row(
                    children: [
                      const Icon(
                        Icons.place,
                        color: Colors.white,
                        size: 20,
                      ),
                      const SizedBox(width: AppTheme.paddingSmall),
                      Expanded(
                        child: Text(
                          'Tujuan: ${groupInfo['destination']}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: AppTheme.fontSizeMedium,
                          ),
                          overflow: TextOverflow.ellipsis,
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
                          borderRadius: BorderRadius.circular(
                            AppTheme.borderRadiusRound,
                          ),
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
                  
                  // Row 2: Jenis Kapal
                  Row(
                    children: [
                      const Icon(
                        Icons.directions_boat,
                        color: Colors.white70,
                        size: 16,
                      ),
                      const SizedBox(width: AppTheme.paddingSmall),
                      Text(
                        'Kapal: ${groupInfo['ferry']}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppTheme.paddingSmall),
                  
                  // Row 3: Tanggal Berangkat
                  Row(
                    children: [
                      const Icon(
                        Icons.calendar_today,
                        color: Colors.white70,
                        size: 16,
                      ),
                      const SizedBox(width: AppTheme.paddingSmall),
                      Text(
                        'Tanggal: ${groupInfo['date']}',
                        style: const TextStyle(color: Colors.white),
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
                      const Spacer(),
                      Icon(
                        isExpanded ? Icons.expand_less : Icons.expand_more,
                        color: Colors.white,
                      ),
                    ],
                  ),
                  const SizedBox(height: AppTheme.paddingSmall),
                  
                  // Badge jumlah tiket
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingRegular,
                      vertical: AppTheme.paddingXSmall,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(
                        AppTheme.borderRadiusRound,
                      ),
                    ),
                    child: Text(
                      '${widget.tickets.length} ${widget.tickets.length > 1 ? 'Tiket' : 'Tiket'}',
                      style: TextStyle(
                        color: AppTheme.primaryColor,
                        fontWeight: FontWeight.w600,
                        fontSize: AppTheme.fontSizeSmall,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Expandable tickets list
          if (isExpanded)
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
                      'Detail Tiket (${widget.tickets.length})',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                  const Divider(height: 1),
                  ...widget.tickets.map((ticket) => _buildTicketItem(ticket)).toList(),
                ],
              ),
            ),
        ],
      ),
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
                    'Tiket: ${ticket.ticketNumber}',
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
              child: const Text('Lihat'),
            ),
          ],
        ),
      ),
    );
  }
}