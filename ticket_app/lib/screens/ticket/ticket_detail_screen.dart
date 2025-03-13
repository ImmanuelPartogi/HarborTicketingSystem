import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/ticket_provider.dart';
import '../../widgets/common/loading_indicator.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/ticket/animated_ticket.dart';

class TicketDetailScreen extends StatefulWidget {
  final int ticketId;

  const TicketDetailScreen({
    Key? key,
    required this.ticketId,
  }) : super(key: key);

  @override
  State<TicketDetailScreen> createState() => _TicketDetailScreenState();
}

class _TicketDetailScreenState extends State<TicketDetailScreen> {
  @override
  void initState() {
    super.initState();
    _loadTicketDetail();
  }
  
  Future<void> _loadTicketDetail() async {
    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    await ticketProvider.fetchTicketDetail(widget.ticketId);
  }
  
  void _showQRCode() {
    Navigator.pushNamed(
      context,
      AppRoutes.qrCode,
      arguments: {'ticketId': widget.ticketId},
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ticket Details'),
        actions: [
          IconButton(
            onPressed: () {
              // Show ticket help or info
              showDialog(
                context: context,
                builder: (context) => AlertDialog(
                  title: const Text('Ticket Information'),
                  content: const SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'About Your E-Ticket',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: AppTheme.fontSizeMedium,
                          ),
                        ),
                        SizedBox(height: AppTheme.paddingSmall),
                        Text(
                          'This electronic ticket serves as your boarding pass. Please present this ticket when boarding the ferry.',
                        ),
                        SizedBox(height: AppTheme.paddingMedium),
                        Text(
                          'Security Features',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: AppTheme.fontSizeMedium,
                          ),
                        ),
                        SizedBox(height: AppTheme.paddingSmall),
                        Text(
                          '• Dynamic watermark pattern\n'
                          '• Secure QR code that updates periodically\n'
                          '• One-time use validation\n'
                          '• Real-time status updates',
                        ),
                        SizedBox(height: AppTheme.paddingMedium),
                        Text(
                          'Important Notes',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: AppTheme.fontSizeMedium,
                          ),
                        ),
                        SizedBox(height: AppTheme.paddingSmall),
                        Text(
                          '• Please arrive at least 30 minutes before departure\n'
                          '• Have your ID ready for verification\n'
                          '• The ticket will expire 30 minutes after scheduled departure',
                        ),
                      ],
                    ),
                  ),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Close'),
                    ),
                  ],
                ),
              );
            },
            icon: const Icon(Icons.info_outline),
          ),
        ],
      ),
      body: Consumer<TicketProvider>(
        builder: (context, ticketProvider, _) {
          if (ticketProvider.isLoadingTicketDetail) {
            return const Center(child: LoadingIndicator(message: 'Loading ticket...'));
          }
          
          final ticket = ticketProvider.selectedTicket;
          
          if (ticket == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 64,
                    color: Colors.red,
                  ),
                  const SizedBox(height: AppTheme.paddingMedium),
                  Text(
                    'Ticket Not Found',
                    style: TextStyle(
                      fontSize: AppTheme.fontSizeLarge,
                      fontWeight: FontWeight.bold,
                      color: theme.textTheme.displaySmall?.color,
                    ),
                  ),
                  const SizedBox(height: AppTheme.paddingSmall),
                  Text(
                    'The ticket you are looking for could not be found',
                    style: TextStyle(
                      fontSize: AppTheme.fontSizeRegular,
                      color: theme.textTheme.bodyMedium?.color,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: AppTheme.paddingLarge),
                  ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Go Back'),
                  ),
                ],
              ),
            );
          }
          
          // Check if ticket is valid for displaying
          final isValid = ticketProvider.isTicketValid(ticket);
          
          return Stack(
            children: [
              SingleChildScrollView(
                padding: const EdgeInsets.all(AppTheme.paddingMedium),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Animated Ticket
                    AnimatedTicket(
                      ticket: ticket,
                      onTap: isValid ? _showQRCode : null,
                    ),
                    
                    const SizedBox(height: AppTheme.paddingLarge),
                    
                    // Ticket details card
                    Container(
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
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Ticket Information',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeMedium,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: AppTheme.paddingMedium),
                          
                          // Ticket number and status
                          _buildInfoRow(
                            label: 'Ticket Number',
                            value: ticket.ticketNumber,
                          ),
                          _buildInfoRow(
                            label: 'Status',
                            value: ticket.statusText,
                            valueColor: ticket.statusColor,
                          ),
                          
                          if (ticket.schedule != null) ...[
                            _buildInfoRow(
                              label: 'Route',
                              value: ticket.schedule!.route?.routeName ?? 'Unknown',
                            ),
                            _buildInfoRow(
                              label: 'Ferry',
                              value: ticket.schedule!.ferry?.name ?? 'Unknown',
                            ),
                            _buildInfoRow(
                              label: 'Departure Date',
                              value: ticket.schedule!.formattedDepartureDate,
                            ),
                            _buildInfoRow(
                              label: 'Departure Time',
                              value: ticket.schedule!.formattedDepartureTime,
                            ),
                          ],
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: AppTheme.paddingLarge),
                    
                    // Passenger details card
                    if (ticket.passenger != null)
                      Container(
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
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Passenger Information',
                              style: TextStyle(
                                fontSize: AppTheme.fontSizeMedium,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: AppTheme.paddingMedium),
                            
                            _buildInfoRow(
                              label: 'Name',
                              value: ticket.passenger!.name,
                            ),
                            _buildInfoRow(
                              label: 'ID',
                              value: '${ticket.passenger!.identityTypeText}: ${ticket.passenger!.identityNumber}',
                            ),
                            if (ticket.passenger!.gender != null)
                              _buildInfoRow(
                                label: 'Gender',
                                value: ticket.passenger!.genderText,
                              ),
                            if (ticket.passenger!.dateOfBirth != null)
                              _buildInfoRow(
                                label: 'Date of Birth',
                                value: ticket.passenger!.dateOfBirth!,
                              ),
                          ],
                        ),
                      ),
                    
                    const SizedBox(height: AppTheme.paddingLarge),
                    
                    // Show QR Code button (if ticket is valid)
                    if (isValid)
                      CustomButton(
                        text: 'Show Boarding QR Code',
                        onPressed: _showQRCode,
                        type: ButtonType.primary,
                        icon: Icons.qr_code,
                        isFullWidth: true,
                      ),
                    
                    if (!isValid && ticket.isExpired)
                      Center(
                        child: Padding(
                          padding: const EdgeInsets.all(AppTheme.paddingMedium),
                          child: Text(
                            'This ticket has expired and is no longer valid for boarding.',
                            style: TextStyle(
                              color: theme.textTheme.bodyMedium?.color,
                              fontSize: AppTheme.fontSizeRegular,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                    
                    if (!isValid && ticket.isUsed)
                      Center(
                        child: Padding(
                          padding: const EdgeInsets.all(AppTheme.paddingMedium),
                          child: Text(
                            'This ticket has already been used and is no longer valid for boarding.',
                            style: TextStyle(
                              color: theme.textTheme.bodyMedium?.color,
                              fontSize: AppTheme.fontSizeRegular,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                    
                    if (!isValid && ticket.isCancelled)
                      Center(
                        child: Padding(
                          padding: const EdgeInsets.all(AppTheme.paddingMedium),
                          child: Text(
                            'This ticket has been cancelled and is no longer valid for boarding.',
                            style: TextStyle(
                              color: theme.textTheme.bodyMedium?.color,
                              fontSize: AppTheme.fontSizeRegular,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                    
                    const SizedBox(height: AppTheme.paddingLarge),
                  ],
                ),
              ),
              
              // Warning overlay for expired/cancelled tickets
              if (!isValid && (ticket.isExpired || ticket.isCancelled))
                Positioned(
                  top: 0,
                  left: 0,
                  right: 0,
                  child: Container(
                    padding: const EdgeInsets.all(AppTheme.paddingRegular),
                    color: ticket.isExpired
                        ? Colors.orange.withOpacity(0.9)
                        : Colors.red.withOpacity(0.9),
                    child: SafeArea(
                      child: Row(
                        children: [
                          const Icon(
                            Icons.warning_amber_rounded,
                            color: Colors.white,
                          ),
                          const SizedBox(width: AppTheme.paddingRegular),
                          Expanded(
                            child: Text(
                              ticket.isExpired
                                  ? 'This ticket has expired'
                                  : 'This ticket has been cancelled',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
            ],
          );
        },
      ),
    );
  }
  
  Widget _buildInfoRow({
    required String label,
    required String value,
    Color? valueColor,
  }) {
    final theme = Theme.of(context);
    
    return Padding(
      padding: const EdgeInsets.only(bottom: AppTheme.paddingSmall),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 140,
            child: Text(
              label,
              style: TextStyle(
                color: theme.textTheme.bodyMedium?.color,
                fontSize: AppTheme.fontSizeRegular,
              ),
            ),
          ),
          const SizedBox(width: AppTheme.paddingRegular),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                color: valueColor ?? theme.textTheme.bodyLarge?.color,
                fontWeight: FontWeight.w500,
                fontSize: AppTheme.fontSizeRegular,
              ),
            ),
          ),
        ],
      ),
    );
  }
}