import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_indicator.dart';

class BookingConfirmationScreen extends StatefulWidget {
  final int bookingId;

  const BookingConfirmationScreen({
    Key? key,
    required this.bookingId,
  }) : super(key: key);

  @override
  State<BookingConfirmationScreen> createState() => _BookingConfirmationScreenState();
}

class _BookingConfirmationScreenState extends State<BookingConfirmationScreen> {
  bool _isLoading = false;
  
  @override
  void initState() {
    super.initState();
    _loadBookingDetail();
  }
  
  Future<void> _loadBookingDetail() async {
    setState(() {
      _isLoading = true;
    });
    
    try {
      final bookingProvider = Provider.of<BookingProvider>(context, listen: false);
      await bookingProvider.fetchBookingDetail(widget.bookingId);
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }
  
  void _viewTickets() {
    Navigator.pushNamedAndRemoveUntil(
      context,
      AppRoutes.ticketList,
      (route) => false,
    );
  }
  
  void _backToHome() {
    Navigator.pushNamedAndRemoveUntil(
      context,
      AppRoutes.home,
      (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: _isLoading
            ? const Center(child: LoadingIndicator(message: 'Loading booking details...'))
            : Consumer<BookingProvider>(
                builder: (context, bookingProvider, _) {
                  final booking = bookingProvider.currentBooking;
                  
                  if (booking == null) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(
                            Icons.error_outline,
                            size: 64,
                            color: Colors.red,
                          ),
                          const SizedBox(height: AppTheme.paddingMedium),
                          const Text(
                            'Booking Not Found',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeLarge,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: AppTheme.paddingSmall),
                          const Text(
                            'The booking information could not be found',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeRegular,
                            ),
                          ),
                          const SizedBox(height: AppTheme.paddingLarge),
                          ElevatedButton(
                            onPressed: _backToHome,
                            child: const Text('Go To Home'),
                          ),
                        ],
                      ),
                    );
                  }
                  
                  // Check if payment is completed
                  final isPaymentCompleted = booking.payment?.isCompleted ?? false;
                  
                  return SingleChildScrollView(
                    padding: const EdgeInsets.all(AppTheme.paddingLarge),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        // Success icon
                        Container(
                          width: 120,
                          height: 120,
                          decoration: BoxDecoration(
                            color: isPaymentCompleted
                                ? Colors.green.withOpacity(0.1)
                                : Colors.amber.withOpacity(0.1),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(
                            isPaymentCompleted ? Icons.check_circle : Icons.pending,
                            size: 80,
                            color: isPaymentCompleted ? Colors.green : Colors.amber,
                          ),
                        ),
                        
                        const SizedBox(height: AppTheme.paddingLarge),
                        
                        // Success message
                        Text(
                          isPaymentCompleted
                              ? 'Booking Confirmed!'
                              : 'Booking Pending Payment',
                          style: const TextStyle(
                            fontSize: AppTheme.fontSizeXLarge,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        
                        const SizedBox(height: AppTheme.paddingRegular),
                        
                        Text(
                          isPaymentCompleted
                              ? 'Your payment has been received and your tickets are ready'
                              : 'Please complete your payment to confirm your booking',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeRegular,
                            color: Theme.of(context).textTheme.bodyMedium?.color,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        
                        const SizedBox(height: AppTheme.paddingXLarge),
                        
                        // Booking details
                        Container(
                          padding: const EdgeInsets.all(AppTheme.paddingMedium),
                          decoration: BoxDecoration(
                            color: Theme.of(context).cardColor,
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
                                'Booking Details',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeMedium,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: AppTheme.paddingMedium),
                              
                              // Booking number
                              _buildInfoRow(
                                label: 'Booking Number',
                                value: booking.bookingNumber,
                              ),
                              
                              // Route
                              if (booking.schedule?.route != null)
                                _buildInfoRow(
                                  label: 'Route',
                                  value: booking.schedule!.route!.routeName,
                                ),
                                
                              // Departure time
                              if (booking.schedule != null)
                                _buildInfoRow(
                                  label: 'Departure Date',
                                  value: DateFormat('EEE, dd MMM yyyy').format(booking.schedule!.departureTime),
                                ),
                                
                              if (booking.schedule != null)
                                _buildInfoRow(
                                  label: 'Departure Time',
                                  value: booking.schedule!.formattedDepartureTime,
                                ),
                                
                              // Ferry
                              if (booking.schedule?.ferry != null)
                                _buildInfoRow(
                                  label: 'Ferry',
                                  value: booking.schedule!.ferry!.name,
                                ),
                                
                              // Passengers
                              _buildInfoRow(
                                label: 'Passengers',
                                value: '${booking.passengerCount} ${booking.passengerCount > 1 ? 'persons' : 'person'}',
                              ),
                              
                              // Vehicle
                              if (booking.hasVehicles && booking.vehicles != null && booking.vehicles!.isNotEmpty)
                                _buildInfoRow(
                                  label: 'Vehicle',
                                  value: '${booking.vehicles!.first.typeText} (${booking.vehicles!.first.licensePlate})',
                                ),
                              
                              // Payment status
                              _buildInfoRow(
                                label: 'Payment Status',
                                value: booking.payment?.statusText ?? 'Unknown',
                                valueColor: _getPaymentStatusColor(booking.payment?.status),
                              ),
                              
                              // Total amount
                              _buildInfoRow(
                                label: 'Total Amount',
                                value: NumberFormat.currency(
                                  locale: 'id',
                                  symbol: 'Rp ',
                                  decimalDigits: 0,
                                ).format(booking.totalAmount),
                                isBold: true,
                              ),
                            ],
                          ),
                        ),
                        
                        const SizedBox(height: AppTheme.paddingLarge),
                        
                        // Action buttons
                        if (isPaymentCompleted) ...[
                          CustomButton(
                            text: 'View My Tickets',
                            onPressed: _viewTickets,
                            type: ButtonType.primary,
                            isFullWidth: true,
                            icon: Icons.confirmation_number,
                          ),
                          
                          const SizedBox(height: AppTheme.paddingMedium),
                          
                          CustomButton(
                            text: 'Back to Home',
                            onPressed: _backToHome,
                            type: ButtonType.outline,
                            isFullWidth: true,
                          ),
                        ] else ...[
                          CustomButton(
                            text: 'Complete Payment',
                            onPressed: () {
                              Navigator.pushNamed(
                                context,
                                AppRoutes.payment,
                                arguments: {
                                  'bookingId': booking.id,
                                  'totalAmount': booking.totalAmount,
                                },
                              );
                            },
                            type: ButtonType.primary,
                            isFullWidth: true,
                            icon: Icons.payments,
                          ),
                          
                          const SizedBox(height: AppTheme.paddingMedium),
                          
                          CustomButton(
                            text: 'Back to Home',
                            onPressed: _backToHome,
                            type: ButtonType.outline,
                            isFullWidth: true,
                          ),
                        ],
                      ],
                    ),
                  );
                },
              ),
      ),
    );
  }
  
  Widget _buildInfoRow({
    required String label,
    required String value,
    Color? valueColor,
    bool isBold = false,
  }) {
    final theme = Theme.of(context);
    
    return Padding(
      padding: const EdgeInsets.only(bottom: AppTheme.paddingSmall),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(
              label,
              style: TextStyle(
                color: theme.textTheme.bodyMedium?.color,
                fontSize: AppTheme.fontSizeRegular,
              ),
            ),
          ),
          const SizedBox(width: AppTheme.paddingSmall),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                color: valueColor ?? theme.textTheme.bodyLarge?.color,
                fontWeight: isBold ? FontWeight.bold : FontWeight.w500,
                fontSize: AppTheme.fontSizeRegular,
              ),
            ),
          ),
        ],
      ),
    );
  }
  
  Color _getPaymentStatusColor(String? status) {
    if (status == null) return Colors.grey;
    
    switch (status) {
      case 'completed':
        return Colors.green;
      case 'pending':
        return Colors.amber;
      case 'failed':
      case 'expired':
        return Colors.red;
      case 'refunded':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }
}