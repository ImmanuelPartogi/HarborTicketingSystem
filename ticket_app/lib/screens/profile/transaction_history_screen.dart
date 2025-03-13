import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/common/loading_indicator.dart';

class TransactionHistoryScreen extends StatefulWidget {
  const TransactionHistoryScreen({Key? key}) : super(key: key);

  @override
  State<TransactionHistoryScreen> createState() => _TransactionHistoryScreenState();
}

class _TransactionHistoryScreenState extends State<TransactionHistoryScreen> {
  bool _isLoading = false;
  String _currentFilter = 'all';
  
  @override
  void initState() {
    super.initState();
    _loadBookings();
  }
  
  Future<void> _loadBookings({String? status}) async {
    setState(() {
      _isLoading = true;
    });
    
    try {
      final bookingProvider = Provider.of<BookingProvider>(context, listen: false);
      await bookingProvider.fetchBookings(status: status);
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }
  
  void _applyFilter(String filter) {
    setState(() {
      _currentFilter = filter;
    });
    
    String? status;
    switch (filter) {
      case 'confirmed':
        status = 'confirmed';
        break;
      case 'pending':
        status = 'pending';
        break;
      case 'completed':
        status = 'completed';
        break;
      case 'cancelled':
        status = 'cancelled';
        break;
      default:
        status = null; // All bookings
    }
    
    _loadBookings(status: status);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Transaction History'),
        actions: [
          IconButton(
            onPressed: () {
              showModalBottomSheet(
                context: context,
                shape: const RoundedRectangleBorder(
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(AppTheme.borderRadiusLarge),
                    topRight: Radius.circular(AppTheme.borderRadiusLarge),
                  ),
                ),
                builder: (context) => _buildFilterBottomSheet(),
              );
            },
            icon: const Icon(Icons.filter_list),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => _loadBookings(
          status: _currentFilter == 'all' ? null : _currentFilter,
        ),
        child: _isLoading
            ? const Center(child: LoadingIndicator(message: 'Loading transactions...'))
            : Consumer<BookingProvider>(
                builder: (context, bookingProvider, _) {
                  if (bookingProvider.bookings.isEmpty) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.receipt_long,
                            size: 64,
                            color: theme.hintColor,
                          ),
                          const SizedBox(height: AppTheme.paddingMedium),
                          Text(
                            'No Transactions Found',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeLarge,
                              fontWeight: FontWeight.bold,
                              color: theme.textTheme.displaySmall?.color,
                            ),
                          ),
                          const SizedBox(height: AppTheme.paddingSmall),
                          Text(
                            _currentFilter == 'all'
                                ? 'You have no transaction history yet'
                                : 'No transactions with status: ${_currentFilter.toUpperCase()}',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeRegular,
                              color: theme.textTheme.bodyMedium?.color,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    );
                  }
                  
                  return ListView.builder(
                    padding: const EdgeInsets.all(AppTheme.paddingMedium),
                    itemCount: bookingProvider.bookings.length,
                    itemBuilder: (context, index) {
                      final booking = bookingProvider.bookings[index];
                      final hasSchedule = booking.schedule != null;
                      final hasRoute = hasSchedule && booking.schedule!.route != null;
                      
                      // Format date and time
                      String departureInfo = 'Unknown departure';
                      if (hasSchedule) {
                        final date = DateFormat('dd MMM yyyy').format(booking.schedule!.departureTime);
                        final time = booking.schedule!.formattedDepartureTime;
                        departureInfo = '$date, $time';
                      }
                      
                      // Format amount
                      final amountFormat = NumberFormat.currency(
                        locale: 'id',
                        symbol: 'Rp ',
                        decimalDigits: 0,
                      );
                      
                      return Card(
                        margin: const EdgeInsets.only(bottom: AppTheme.paddingMedium),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
                        ),
                        child: InkWell(
                          onTap: () {
                            bookingProvider.fetchBookingDetail(booking.id);
                            Navigator.pushNamed(
                              context,
                              AppRoutes.bookingConfirmation,
                              arguments: {'bookingId': booking.id},
                            );
                          },
                          borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
                          child: Padding(
                            padding: const EdgeInsets.all(AppTheme.paddingMedium),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Header with booking number and status
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        'Booking #${booking.bookingNumber}',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: AppTheme.fontSizeMedium,
                                        ),
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: AppTheme.paddingRegular,
                                        vertical: AppTheme.paddingXSmall,
                                      ),
                                      decoration: BoxDecoration(
                                        color: _getStatusColor(booking.status).withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRound),
                                      ),
                                      child: Text(
                                        booking.statusText,
                                        style: TextStyle(
                                          color: _getStatusColor(booking.status),
                                          fontWeight: FontWeight.w600,
                                          fontSize: AppTheme.fontSizeSmall,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                
                                const SizedBox(height: AppTheme.paddingRegular),
                                
                                // Route and departure info
                                Row(
                                  children: [
                                    Container(
                                      width: 40,
                                      height: 40,
                                      decoration: BoxDecoration(
                                        color: AppTheme.primaryColor.withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                                      ),
                                      child: const Icon(
                                        Icons.directions_boat,
                                        color: AppTheme.primaryColor,
                                      ),
                                    ),
                                    const SizedBox(width: AppTheme.paddingRegular),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            hasRoute ? booking.schedule!.route!.routeName : 'Unknown Route',
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w500,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          Text(
                                            departureInfo,
                                            style: TextStyle(
                                              color: theme.textTheme.bodyMedium?.color,
                                              fontSize: AppTheme.fontSizeSmall,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                                
                                const SizedBox(height: AppTheme.paddingRegular),
                                const Divider(),
                                const SizedBox(height: AppTheme.paddingSmall),
                                
                                // Amount and passenger count
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      '${booking.passengerCount} ${booking.passengerCount > 1 ? 'Passengers' : 'Passenger'}${booking.hasVehicles ? ' + Vehicle' : ''}',
                                      style: TextStyle(
                                        color: theme.textTheme.bodyMedium?.color,
                                        fontSize: AppTheme.fontSizeRegular,
                                      ),
                                    ),
                                    Text(
                                      amountFormat.format(booking.totalAmount),
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: AppTheme.fontSizeMedium,
                                      ),
                                    ),
                                  ],
                                ),
                                
                                const SizedBox(height: AppTheme.paddingSmall),
                                
                                // Booked date
                                Text(
                                  'Booked on ${DateFormat('dd MMM yyyy, HH:mm').format(booking.bookedAt)}',
                                  style: TextStyle(
                                    color: theme.textTheme.bodyMedium?.color,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  );
                },
              ),
      ),
    );
  }
  
  Widget _buildFilterBottomSheet() {
    return Container(
      padding: const EdgeInsets.all(AppTheme.paddingMedium),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text(
            'Filter Transactions',
            style: TextStyle(
              fontSize: AppTheme.fontSizeLarge,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: AppTheme.paddingLarge),
          
          // Filter options
          _buildFilterOption(
            title: 'All Transactions',
            value: 'all',
          ),
          _buildFilterOption(
            title: 'Confirmed',
            value: 'confirmed',
          ),
          _buildFilterOption(
            title: 'Pending Payment',
            value: 'pending',
          ),
          _buildFilterOption(
            title: 'Completed',
            value: 'completed',
          ),
          _buildFilterOption(
            title: 'Cancelled',
            value: 'cancelled',
          ),
          
          const SizedBox(height: AppTheme.paddingLarge),
          
          // Apply button
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
              foregroundColor: Colors.white,
              minimumSize: const Size(double.infinity, 50),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              ),
            ),
            child: const Text('Apply Filter'),
          ),
        ],
      ),
    );
  }
  
  Widget _buildFilterOption({
    required String title,
    required String value,
  }) {
    return RadioListTile<String>(
      title: Text(title),
      value: value,
      groupValue: _currentFilter,
      onChanged: (newValue) {
        Navigator.pop(context);
        _applyFilter(newValue!);
      },
      activeColor: AppTheme.primaryColor,
      contentPadding: EdgeInsets.zero,
    );
  }
  
  Color _getStatusColor(String status) {
    switch (status) {
      case 'confirmed':
        return Colors.green;
      case 'pending':
        return Colors.amber;
      case 'cancelled':
        return Colors.red;
      case 'completed':
        return Colors.blue;
      case 'refunded':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }
}