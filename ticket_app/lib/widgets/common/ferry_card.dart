import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../models/schedule_model.dart';

class FerryCard extends StatelessWidget {
  final Schedule schedule;
  final VoidCallback? onTap;
  final bool isDetailed;
  final VoidCallback? onBookPressed;
  
  const FerryCard({
    Key? key,
    required this.schedule,
    this.onTap,
    this.isDetailed = false,
    this.onBookPressed,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    // Format date and time
    final dateFormat = DateFormat('EEE, dd MMM yyyy');
    final timeFormat = DateFormat('HH:mm');
    
    // Check if we have arrival time and route info
    final hasArrivalTime = schedule.arrivalTime != null;
    final hasRouteInfo = schedule.route != null;
    final hasFerryInfo = schedule.ferry != null;
    
    // Calculate journey duration if we have arrival time
    final durationText = hasArrivalTime 
        ? _formatDuration(schedule.departureTime, schedule.arrivalTime!)
        : 'N/A';
    
    // Format price
    final currencyFormat = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp',
      decimalDigits: 0,
    );
    
    // Get route info
    final departurePort = hasRouteInfo ? schedule.route!.departurePort : 'N/A';
    final arrivalPort = hasRouteInfo ? schedule.route!.arrivalPort : 'N/A';
    
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
            // Top part with ferry info
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              decoration: BoxDecoration(
                color: _getStatusColor(schedule.status).withOpacity(0.1),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(AppTheme.borderRadiusMedium),
                  topRight: Radius.circular(AppTheme.borderRadiusMedium),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.directions_boat,
                    color: _getStatusColor(schedule.status),
                    size: 24,
                  ),
                  const SizedBox(width: AppTheme.paddingRegular),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          hasFerryInfo ? schedule.ferry!.name : 'Unknown Ferry',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: AppTheme.fontSizeMedium,
                          ),
                        ),
                        Text(
                          hasFerryInfo ? schedule.ferry!.type : 'Unknown Type',
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
                      color: _getStatusColor(schedule.status),
                      borderRadius: BorderRadius.circular(AppTheme.borderRadiusRound),
                    ),
                    child: Text(
                      schedule.statusText,
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
            
            // Main ferry content
            Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Route name
                  Text(
                    '$departurePort → $arrivalPort',
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
                        dateFormat.format(schedule.departureTime),
                        style: TextStyle(
                          color: theme.textTheme.bodyMedium?.color,
                          fontSize: AppTheme.fontSizeRegular,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppTheme.paddingXSmall),
                  
                  // Journey time details
                  Row(
                    children: [
                      Expanded(
                        child: Row(
                          children: [
                            const Icon(Icons.schedule, size: 16),
                            const SizedBox(width: AppTheme.paddingSmall),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Departure',
                                  style: TextStyle(
                                    color: theme.hintColor,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                                Text(
                                  timeFormat.format(schedule.departureTime),
                                  style: TextStyle(
                                    fontWeight: FontWeight.w600,
                                    fontSize: AppTheme.fontSizeRegular,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      
                      Expanded(
                        child: Row(
                          children: [
                            const Icon(Icons.access_time, size: 16),
                            const SizedBox(width: AppTheme.paddingSmall),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Duration',
                                  style: TextStyle(
                                    color: theme.hintColor,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                                Text(
                                  durationText,
                                  style: TextStyle(
                                    fontWeight: FontWeight.w600,
                                    fontSize: AppTheme.fontSizeRegular,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      
                      Expanded(
                        child: Row(
                          children: [
                            const Icon(Icons.schedule, size: 16),
                            const SizedBox(width: AppTheme.paddingSmall),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Arrival',
                                  style: TextStyle(
                                    color: theme.hintColor,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                                Text(
                                  hasArrivalTime 
                                    ? timeFormat.format(schedule.arrivalTime!) 
                                    : 'TBD',
                                  style: TextStyle(
                                    fontWeight: FontWeight.w600,
                                    fontSize: AppTheme.fontSizeRegular,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: AppTheme.paddingMedium),
                  
                  // Price and seats
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Price per Person',
                              style: TextStyle(
                                color: theme.hintColor,
                                fontSize: AppTheme.fontSizeSmall,
                              ),
                            ),
                            Text(
                              currencyFormat.format(schedule.finalPrice),
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: AppTheme.fontSizeMedium,
                                color: theme.primaryColor,
                              ),
                            ),
                            if (schedule.discountPercentage != null && schedule.discountPercentage! > 0)
                              Row(
                                children: [
                                  Text(
                                    currencyFormat.format(schedule.price),
                                    style: TextStyle(
                                      decoration: TextDecoration.lineThrough,
                                      color: theme.hintColor,
                                      fontSize: AppTheme.fontSizeSmall,
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 4,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.red.shade50,
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      '-${schedule.discountPercentage!.toStringAsFixed(0)}%',
                                      style: TextStyle(
                                        color: Colors.red,
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                          ],
                        ),
                      ),
                      
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Available Seats',
                              style: TextStyle(
                                color: theme.hintColor,
                                fontSize: AppTheme.fontSizeSmall,
                              ),
                            ),
                            Text(
                              '${schedule.availableSeats}',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: AppTheme.fontSizeMedium,
                                color: _getSeatsColor(schedule.availableSeats),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  
                  // Detailed information if required
                  if (isDetailed && hasFerryInfo) ...[
                    const SizedBox(height: AppTheme.paddingMedium),
                    const Divider(),
                    const SizedBox(height: AppTheme.paddingSmall),
                    
                    Text(
                      'Ferry Details',
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
                                'Capacity',
                                style: TextStyle(
                                  color: theme.hintColor,
                                  fontSize: AppTheme.fontSizeSmall,
                                ),
                              ),
                              Text(
                                '${schedule.ferry!.capacity} passengers',
                                style: const TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                ),
                              ),
                              if (schedule.ferry!.vehicleCapacityText.isNotEmpty) ...[
                                const SizedBox(height: 4),
                                Text(
                                  schedule.ferry!.vehicleCapacityText,
                                  style: const TextStyle(
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                        if (schedule.ferry?.description != null && schedule.ferry!.description!.isNotEmpty)
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Description',
                                  style: TextStyle(
                                    color: theme.hintColor,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                                Text(
                                  schedule.ferry!.description!,
                                  style: const TextStyle(
                                    fontSize: AppTheme.fontSizeRegular,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                      ],
                    ),
                    
                    const SizedBox(height: AppTheme.paddingSmall),
                    
                    // Vehicle availability
                    Text(
                      'Vehicle Availability',
                      style: TextStyle(
                        color: theme.hintColor,
                        fontSize: AppTheme.fontSizeSmall,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        if (schedule.availableCars > 0)
                          _buildVehicleChip(theme, 'Cars: ${schedule.availableCars}', Icons.directions_car),
                        if (schedule.availableMotorcycles > 0)
                          _buildVehicleChip(theme, 'Motorcycles: ${schedule.availableMotorcycles}', Icons.two_wheeler),
                        if (schedule.availableBuses > 0)
                          _buildVehicleChip(theme, 'Buses: ${schedule.availableBuses}', Icons.directions_bus),
                        if (schedule.availableTrucks > 0)
                          _buildVehicleChip(theme, 'Trucks: ${schedule.availableTrucks}', Icons.local_shipping),
                      ],
                    ),
                  ],
                  
                  const SizedBox(height: AppTheme.paddingMedium),
                  
                  // Action buttons
                  if (schedule.isAvailable)
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: onBookPressed ?? onTap,
                            icon: const Icon(Icons.bookmark, size: 20),
                            label: const Text('Book Now'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: theme.primaryColor,
                              foregroundColor: Colors.white,
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
  
  // Helper widget for vehicle chips
  Widget _buildVehicleChip(ThemeData theme, String label, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(right: 8.0),
      child: Chip(
        backgroundColor: theme.primaryColor.withOpacity(0.1),
        labelPadding: const EdgeInsets.symmetric(horizontal: 4),
        avatar: Icon(
          icon,
          size: 16,
          color: theme.primaryColor,
        ),
        label: Text(
          label,
          style: TextStyle(
            fontSize: AppTheme.fontSizeSmall,
            color: theme.primaryColor,
          ),
        ),
      ),
    );
  }
  
  // Helper methods
  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'scheduled':
        return Colors.green;
      case 'delayed':
        return Colors.orange;
      case 'departed':
        return Colors.blue;
      case 'arrived':
        return Colors.purple;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
  
  String _formatDuration(DateTime departureTime, DateTime arrivalTime) {
    final duration = arrivalTime.difference(departureTime);
    final hours = duration.inHours;
    final minutes = duration.inMinutes % 60;
    return '${hours}h ${minutes}m';
  }
  
  Color _getSeatsColor(int available) {
    if (available > 30) {
      return Colors.green;
    } else if (available > 10) {
      return Colors.orange;
    } else {
      return Colors.red;
    }
  }
}