import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../models/ticket_model.dart';

class TicketCard extends StatelessWidget {
  final Ticket ticket;
  final VoidCallback? onTap;
  final bool isDetailed;
  
  const TicketCard({
    Key? key,
    required this.ticket,
    this.onTap,
    this.isDetailed = false,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final hasSchedule = ticket.schedule != null;
    final hasPassenger = ticket.passenger != null;
    
    // Format date
    final dateFormat = DateFormat('EEE, dd MMM yyyy');
    final timeFormat = DateFormat('HH:mm');
    
    return Card(
      elevation: 3,
      margin: const EdgeInsets.symmetric(
        vertical: AppTheme.paddingSmall,
        horizontal: AppTheme.paddingRegular,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
        child: Column(
          children: [
            // Top part with status indicator
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              decoration: BoxDecoration(
                color: ticket.statusColor.withOpacity(0.1),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(AppTheme.borderRadiusMedium),
                  topRight: Radius.circular(AppTheme.borderRadiusMedium),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    ticket.isActive 
                        ? Icons.confirmation_number 
                        : ticket.isExpired 
                            ? Icons.timelapse 
                            : ticket.isUsed 
                                ? Icons.check_circle 
                                : Icons.cancel,
                    color: ticket.statusColor,
                    size: 24,
                  ),
                  const SizedBox(width: AppTheme.paddingRegular),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          ticket.ticketNumber,
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: AppTheme.fontSizeMedium,
                          ),
                        ),
                        if (hasPassenger)
                          Text(
                            ticket.passenger!.name,
                            style: TextStyle(
                              color: theme.textTheme.bodyMedium?.color,
                              fontSize: AppTheme.fontSizeRegular,
                            ),
                          ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingMedium,
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
            
            // Divider with scissors
            Container(
              margin: const EdgeInsets.symmetric(horizontal: AppTheme.paddingMedium),
              child: Row(
                children: List.generate(30, (index) {
                  return Expanded(
                    child: Container(
                      height: 1,
                      color: index % 2 == 0 ? theme.dividerColor : Colors.transparent,
                    ),
                  );
                }),
              ),
            ),
            
            // Main ticket content
            Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (hasSchedule && ticket.schedule!.route != null) ...[
                    Text(
                      ticket.schedule!.route!.routeName,
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: AppTheme.fontSizeLarge,
                      ),
                    ),
                    const SizedBox(height: AppTheme.paddingSmall),
                    
                    // Departure details
                    Row(
                      children: [
                        const Icon(Icons.calendar_today, size: 16),
                        const SizedBox(width: AppTheme.paddingSmall),
                        Text(
                          dateFormat.format(ticket.schedule!.departureTime),
                          style: TextStyle(
                            color: theme.textTheme.bodyMedium?.color,
                            fontSize: AppTheme.fontSizeRegular,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppTheme.paddingXSmall),
                    
                    Row(
                      children: [
                        const Icon(Icons.access_time, size: 16),
                        const SizedBox(width: AppTheme.paddingSmall),
                        Text(
                          timeFormat.format(ticket.schedule!.departureTime),
                          style: TextStyle(
                            color: theme.textTheme.bodyMedium?.color,
                            fontSize: AppTheme.fontSizeRegular,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ] else ...[
                    Text(
                      'Route information not available',
                      style: TextStyle(
                        color: theme.hintColor,
                        fontSize: AppTheme.fontSizeRegular,
                      ),
                    ),
                  ],
                  
                  if (isDetailed && hasPassenger) ...[
                    const SizedBox(height: AppTheme.paddingMedium),
                    const Divider(),
                    const SizedBox(height: AppTheme.paddingSmall),
                    
                    // Passenger details
                    Text(
                      'Passenger Details',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: AppTheme.fontSizeMedium,
                      ),
                    ),
                    const SizedBox(height: AppTheme.paddingSmall),
                    
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Name',
                                style: TextStyle(
                                  color: theme.hintColor,
                                  fontSize: AppTheme.fontSizeSmall,
                                ),
                              ),
                              Text(
                                ticket.passenger!.name,
                                style: const TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'ID',
                                style: TextStyle(
                                  color: theme.hintColor,
                                  fontSize: AppTheme.fontSizeSmall,
                                ),
                              ),
                              Text(
                                '${ticket.passenger!.identityTypeText}: ${ticket.passenger!.identityNumber}',
                                style: const TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    
                    if (ticket.passenger!.gender != null || ticket.passenger!.dateOfBirth != null) ...[
                      const SizedBox(height: AppTheme.paddingSmall),
                      Row(
                        children: [
                          if (ticket.passenger!.gender != null)
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Gender',
                                    style: TextStyle(
                                      color: theme.hintColor,
                                      fontSize: AppTheme.fontSizeSmall,
                                    ),
                                  ),
                                  Text(
                                    ticket.passenger!.genderText,
                                    style: const TextStyle(
                                      fontSize: AppTheme.fontSizeRegular,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          if (ticket.passenger!.dateOfBirth != null)
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Date of Birth',
                                    style: TextStyle(
                                      color: theme.hintColor,
                                      fontSize: AppTheme.fontSizeSmall,
                                    ),
                                  ),
                                  Text(
                                    ticket.passenger!.dateOfBirth!,
                                    style: const TextStyle(
                                      fontSize: AppTheme.fontSizeRegular,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),
                    ],
                  ],
                  
                  const SizedBox(height: AppTheme.paddingMedium),
                  
                  // Action buttons
                  if (ticket.isActive)
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: onTap,
                            icon: const Icon(Icons.qr_code, size: 20),
                            label: const Text('View Ticket'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: AppTheme.paddingSmall),
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