import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../providers/ticket_provider.dart';
import '../../widgets/common/loading_indicator.dart';

class QrCodeScreen extends StatefulWidget {
  final int ticketId;

  const QrCodeScreen({
    Key? key,
    required this.ticketId,
  }) : super(key: key);

  @override
  State<QrCodeScreen> createState() => _QrCodeScreenState();
}

class _QrCodeScreenState extends State<QrCodeScreen> {
  Timer? _refreshTimer;
  int _refreshCountdown = 30;
  
  @override
  void initState() {
    super.initState();
    _loadTicketDetail();
    _startRefreshTimer();
    
    // Keep screen on
    // SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual, overlays: []);
  }
  
  @override
  void dispose() {
    _refreshTimer?.cancel();
    // Restore system UI
    // SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual, overlays: SystemUiOverlay.values);
    super.dispose();
  }
  
  Future<void> _loadTicketDetail() async {
    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    await ticketProvider.fetchTicketDetail(widget.ticketId);
  }
  
  void _startRefreshTimer() {
    setState(() {
      _refreshCountdown = 30;
    });
    
    _refreshTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      setState(() {
        if (_refreshCountdown > 0) {
          _refreshCountdown--;
        } else {
          // Refresh QR code
          final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
          ticketProvider.generateTicketQR();
          
          // Restart timer
          _refreshCountdown = 30;
        }
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('Boarding Pass'),
        backgroundColor: Colors.black,
        iconTheme: const IconThemeData(color: Colors.white),
        titleTextStyle: const TextStyle(
          color: Colors.white,
          fontSize: AppTheme.fontSizeLarge,
          fontWeight: FontWeight.bold,
        ),
      ),
      body: Consumer<TicketProvider>(
        builder: (context, ticketProvider, _) {
          if (ticketProvider.isLoadingTicketDetail) {
            return const Center(
              child: LoadingIndicator(
                message: 'Loading boarding pass...',
                color: Colors.white,
              ),
            );
          }
          
          final ticket = ticketProvider.selectedTicket;
          
          if (ticket == null) {
            return Center(
              child: Text(
                'Ticket not found',
                style: TextStyle(color: Colors.white),
              ),
            );
          }
          
          // Check if ticket is valid for boarding
          final isValid = ticketProvider.isTicketValid(ticket);
          
          if (!isValid) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(AppTheme.paddingLarge),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.error_outline,
                      size: 80,
                      color: Colors.red,
                    ),
                    const SizedBox(height: AppTheme.paddingMedium),
                    Text(
                      'Invalid Ticket',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: AppTheme.fontSizeXLarge,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: AppTheme.paddingMedium),
                    Text(
                      ticket.isExpired
                          ? 'This ticket has expired and is no longer valid for boarding.'
                          : ticket.isUsed
                              ? 'This ticket has already been used.'
                              : 'This ticket has been cancelled.',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: AppTheme.fontSizeMedium,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: AppTheme.paddingLarge),
                    ElevatedButton(
                      onPressed: () => Navigator.pop(context),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: Colors.black,
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppTheme.paddingLarge,
                          vertical: AppTheme.paddingMedium,
                        ),
                      ),
                      child: const Text('Go Back'),
                    ),
                  ],
                ),
              ),
            );
          }
          
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Status bar
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: AppTheme.paddingLarge),
                  padding: const EdgeInsets.all(AppTheme.paddingSmall),
                  decoration: BoxDecoration(
                    color: Colors.green,
                    borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                  ),
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.check_circle,
                        color: Colors.white,
                        size: 18,
                      ),
                      SizedBox(width: AppTheme.paddingSmall),
                      Text(
                        'VALID FOR BOARDING',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: AppTheme.fontSizeRegular,
                        ),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: AppTheme.paddingLarge),
                
                // QR Code
                Container(
                  width: 280,
                  height: 280,
                  padding: const EdgeInsets.all(AppTheme.paddingMedium),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Expanded(
                        child: ticketProvider.generateTicketQR(),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: AppTheme.paddingMedium),
                
                // QR Code refresh info
                Text(
                  'QR Code refreshes in $_refreshCountdown seconds',
                  style: const TextStyle(
                    color: Colors.white70,
                    fontSize: AppTheme.fontSizeSmall,
                  ),
                ),
                
                const SizedBox(height: AppTheme.paddingLarge),
                
                // Ticket info
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: AppTheme.paddingLarge),
                  padding: const EdgeInsets.all(AppTheme.paddingMedium),
                  decoration: BoxDecoration(
                    color: Colors.white10,
                    borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
                  ),
                  child: Column(
                    children: [
                      // Route
                      if (ticket.schedule?.route != null)
                        Text(
                          ticket.schedule!.route!.routeName,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: AppTheme.fontSizeLarge,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      
                      const SizedBox(height: AppTheme.paddingMedium),
                      
                      // Time and date
                      if (ticket.schedule != null)
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(
                              Icons.access_time,
                              color: Colors.white70,
                              size: 16,
                            ),
                            const SizedBox(width: AppTheme.paddingSmall),
                            Text(
                              DateFormat('HH:mm, dd MMM yyyy').format(ticket.schedule!.departureTime),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: AppTheme.fontSizeMedium,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      
                      const SizedBox(height: AppTheme.paddingMedium),
                      const Divider(color: Colors.white24),
                      const SizedBox(height: AppTheme.paddingMedium),
                      
                      // Passenger info
                      if (ticket.passenger != null) ...[
                        Row(
                          children: [
                            const SizedBox(
                              width: 100,
                              child: Text(
                                'Passenger',
                                style: TextStyle(
                                  color: Colors.white70,
                                  fontSize: AppTheme.fontSizeRegular,
                                ),
                              ),
                            ),
                            Expanded(
                              child: Text(
                                ticket.passenger!.name,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: AppTheme.fontSizeRegular,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: AppTheme.paddingSmall),
                        Row(
                          children: [
                            const SizedBox(
                              width: 100,
                              child: Text(
                                'ID',
                                style: TextStyle(
                                  color: Colors.white70,
                                  fontSize: AppTheme.fontSizeRegular,
                                ),
                              ),
                            ),
                            Expanded(
                              child: Text(
                                '${ticket.passenger!.identityTypeText}: ${ticket.passenger!.identityNumber}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: AppTheme.fontSizeRegular,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                      
                      const SizedBox(height: AppTheme.paddingMedium),
                      
                      // Ticket number
                      Row(
                        children: [
                          const SizedBox(
                            width: 100,
                            child: Text(
                              'Ticket No.',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: AppTheme.fontSizeRegular,
                              ),
                            ),
                          ),
                          Expanded(
                            child: Text(
                              ticket.ticketNumber,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: AppTheme.fontSizeRegular,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: AppTheme.paddingXLarge),
                
                // Instructions
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: AppTheme.paddingLarge),
                  child: const Text(
                    'Present this QR code to the boarding staff. The code refreshes automatically for security.',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: AppTheme.fontSizeSmall,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}