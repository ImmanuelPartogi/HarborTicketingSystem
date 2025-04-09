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
  Timer? _expirationTimer;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadTickets();

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
    _expirationTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadTickets() async {
    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    await ticketProvider.fetchActiveTickets();
    await ticketProvider.fetchTicketHistory();
    ticketProvider.checkAndMoveExpiredTickets();
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
          tabs: const [
            Tab(text: 'Aktif'),
            Tab(text: 'Riwayat')
          ],
          labelStyle: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: AppTheme.fontSizeRegular,
          ),
          indicatorWeight: 3,
          indicatorColor: theme.primaryColor,
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // Tab tiket aktif
          _buildActiveTicketsTab(theme),

          // Tab riwayat tiket
          _buildHistoryTicketsTab(theme),
        ],
      ),
    );
  }

  Widget _buildActiveTicketsTab(ThemeData theme) {
    return Consumer<TicketProvider>(
      builder: (context, ticketProvider, _) {
        if (ticketProvider.isLoadingActiveTickets) {
          return const Center(
            child: LoadingIndicator(message: 'Memuat tiket aktif...'),
          );
        }

        if (ticketProvider.activeTickets.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.confirmation_number_outlined,
                  size: 64,
                  color: theme.hintColor,
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

        return RefreshIndicator(
          onRefresh: () => ticketProvider.fetchActiveTickets(forceReload: true),
          child: ListView.builder(
            padding: const EdgeInsets.all(AppTheme.paddingMedium),
            itemCount: ticketProvider.activeTickets.length,
            itemBuilder: (context, index) {
              final ticket = ticketProvider.activeTickets[index];
              return _buildTicketItem(context, ticket, ticketProvider);
            },
          ),
        );
      },
    );
  }

  Widget _buildHistoryTicketsTab(ThemeData theme) {
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
                  color: theme.hintColor,
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
              return _buildTicketItem(context, ticket, ticketProvider, isHistory: true);
            },
          ),
        );
      },
    );
  }
  
  Widget _buildTicketItem(
    BuildContext context,
    dynamic ticket,
    TicketProvider ticketProvider, {
    bool isHistory = false,
  }) {
    final theme = Theme.of(context);
    
    // Mendapatkan icon berdasarkan jenis tiket
    IconData ticketIcon = Icons.person;
    if (ticket.vehicle != null) {
      ticketIcon = Icons.directions_car;
    }
    
    // Tanggal keberangkatan
    String departureDate = '';
    if (ticket.schedule != null) {
      final date = ticket.schedule.departureTime;
      departureDate = DateFormat('EEE, dd MMM yyyy').format(date);
    }
    
    // Waktu keberangkatan
    String departureTime = '';
    if (ticket.schedule != null) {
      departureTime = ticket.schedule.formattedDepartureTime;
    }
    
    // Rute
    String routeName = 'Tidak diketahui';
    if (ticket.schedule?.route != null) {
      routeName = ticket.schedule!.route!.routeName;
    }
    
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
                  // Ikon kapal
                  Container(
                    padding: const EdgeInsets.all(AppTheme.paddingSmall),
                    decoration: BoxDecoration(
                      color: theme.primaryColor.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                    ),
                    child: Icon(
                      Icons.directions_boat,
                      color: theme.primaryColor,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: AppTheme.paddingMedium),
                  
                  // Informasi rute
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          routeName,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: AppTheme.fontSizeMedium,
                          ),
                        ),
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
                  
                  // Badge status
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingRegular,
                      vertical: AppTheme.paddingXSmall,
                    ),
                    decoration: BoxDecoration(
                      color: ticket.statusColor,
                      borderRadius: BorderRadius.circular(AppTheme.borderRadiusRound),
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
            
            // Divider dengan garis putus-putus
            Container(
              padding: const EdgeInsets.symmetric(horizontal: AppTheme.paddingSmall),
              child: Row(
                children: List.generate(40, (index) {
                  return Expanded(
                    child: Container(
                      height: 1,
                      margin: const EdgeInsets.symmetric(horizontal: 1),
                      color: index % 2 == 0 ? theme.dividerColor : Colors.transparent,
                    ),
                  );
                }),
              ),
            ),
            
            // Informasi penumpang/kendaraan
            Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Row(
                children: [
                  // Ikon untuk tipe tiket (penumpang atau kendaraan)
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: ticket.statusColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                    ),
                    child: Icon(
                      ticketIcon,
                      color: ticket.statusColor,
                    ),
                  ),
                  const SizedBox(width: AppTheme.paddingMedium),
                  
                  // Informasi tiket
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Nama penumpang atau jenis kendaraan
                        if (ticket.vehicle != null)
                          Text(
                            '${ticket.vehicle.typeText} - ${ticket.vehicle.licensePlate}',
                            style: const TextStyle(
                              fontWeight: FontWeight.w500,
                              fontSize: AppTheme.fontSizeRegular,
                            ),
                          )
                        else if (ticket.passenger != null)
                          Text(
                            ticket.passenger.name,
                            style: const TextStyle(
                              fontWeight: FontWeight.w500,
                              fontSize: AppTheme.fontSizeRegular,
                            ),
                          ),
                        
                        // Nomor tiket
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
                  
                  // Tombol aksi berdasarkan status
                  if (!isHistory && ticket.isActive)
                    ElevatedButton(
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
                        textStyle: const TextStyle(
                          fontSize: AppTheme.fontSizeSmall,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      child: const Text('Lihat'),
                    )
                  else
                    OutlinedButton(
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
                        textStyle: const TextStyle(
                          fontSize: AppTheme.fontSizeSmall,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      child: const Text('Detail'),
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