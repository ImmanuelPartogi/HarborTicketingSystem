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
    
    // Format date and time
    final dateFormat = DateFormat('EEE, dd MMM yyyy');
    final timeFormat = DateFormat('HH:mm');
    
    return Card(
      elevation: 3,
      margin: const EdgeInsets.symmetric(
        vertical: AppTheme.paddingSmall,
        horizontal: 0,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
        child: Column(
          children: [
            // Header section with route info and status
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
                  // Ferry icon with primary color
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
                  
                  // Route name and date/time info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (hasSchedule)
                          Text(
                            ticket.schedule!.route?.routeName ?? 'Unknown Route',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: AppTheme.fontSizeMedium,
                            ),
                          ),
                        if (hasSchedule)
                          Text(
                            '${dateFormat.format(ticket.schedule!.departureTime)} · ${timeFormat.format(ticket.schedule!.departureTime)}',
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
            
            // Divider with dotted line design
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
            
            // Ticket body with details
            Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Passenger or vehicle info
                  Row(
                    children: [
                      // Icon based on ticket type
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: ticket.statusColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                        ),
                        child: Icon(
                          ticket.vehicle != null 
                              ? Icons.directions_car 
                              : Icons.person,
                          color: ticket.statusColor,
                        ),
                      ),
                      const SizedBox(width: AppTheme.paddingMedium),
                      
                      // Ticket number and passenger/vehicle info
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (ticket.vehicle != null)
                              Text(
                                '${ticket.vehicle!.typeText} - ${ticket.vehicle!.licensePlate}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w500,
                                  fontSize: AppTheme.fontSizeRegular,
                                ),
                              )
                            else if (hasPassenger)
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
                                color: theme.textTheme.bodyMedium?.color,
                                fontSize: AppTheme.fontSizeSmall,
                              ),
                            ),
                          ],
                        ),
                      ),
                      
                      // Info or View button based on ticket status
                      if (ticket.isActive)
                        ElevatedButton(
                          onPressed: onTap,
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
                          onPressed: onTap,
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
                  
                  // Ferry info if detailed view is enabled
                  if (isDetailed && hasSchedule) ...[
                    const SizedBox(height: AppTheme.paddingMedium),
                    const Divider(),
                    const SizedBox(height: AppTheme.paddingSmall),
                    
                    Text(
                      'Informasi Kapal',
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
                                'Kapal',
                                style: TextStyle(
                                  color: theme.hintColor,
                                  fontSize: AppTheme.fontSizeSmall,
                                ),
                              ),
                              Text(
                                ticket.schedule!.ferry?.name ?? 'Unknown',
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
                                'Keberangkatan',
                                style: TextStyle(
                                  color: theme.hintColor,
                                  fontSize: AppTheme.fontSizeSmall,
                                ),
                              ),
                              Text(
                                ticket.schedule!.formattedDepartureTime,
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
              ),
            ),
          ],
        ),
      ),
    );
  }
}