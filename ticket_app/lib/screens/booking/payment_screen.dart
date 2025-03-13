import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_indicator.dart';

class PaymentScreen extends StatefulWidget {
  final int bookingId;
  final double totalAmount;

  const PaymentScreen({
    Key? key,
    required this.bookingId,
    required this.totalAmount,
  }) : super(key: key);

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  String _selectedPaymentMethod = '';
  String _selectedPaymentType = 'bank_transfer';
  
  bool _isLoading = false;
  Timer? _paymentCheckTimer;
  int _timeoutMinutes = 15;
  int _remainingSeconds = 0;
  
  @override
  void initState() {
    super.initState();
    _loadBookingDetails();
    _startPaymentTimer();
  }
  
  @override
  void dispose() {
    _paymentCheckTimer?.cancel();
    super.dispose();
  }
  
  Future<void> _loadBookingDetails() async {
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
  
  void _startPaymentTimer() {
    // Set initial timeout
    _remainingSeconds = _timeoutMinutes * 60;
    
    // Start countdown timer
    _paymentCheckTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      setState(() {
        if (_remainingSeconds > 0) {
          _remainingSeconds--;
        } else {
          // Time's up, go back to home
          timer.cancel();
          _showTimeoutDialog();
        }
      });
      
      // Check payment status every 30 seconds
      if (_remainingSeconds % 30 == 0) {
        _checkPaymentStatus();
      }
    });
  }
  
  String get _formattedRemainingTime {
    final minutes = _remainingSeconds ~/ 60;
    final seconds = _remainingSeconds % 60;
    return '$minutes:${seconds.toString().padLeft(2, '0')}';
  }
  
  Future<void> _checkPaymentStatus() async {
    final bookingProvider = Provider.of<BookingProvider>(context, listen: false);
    final isCompleted = await bookingProvider.checkPaymentStatus();
    
    if (isCompleted && mounted) {
      _paymentCheckTimer?.cancel();
      
      Navigator.pushNamedAndRemoveUntil(
        context,
        AppRoutes.bookingConfirmation,
        (route) => false,
        arguments: {'bookingId': widget.bookingId},
      );
    }
  }
  
  void _showTimeoutDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Payment Time Expired'),
        content: const Text(
          'Your payment time has expired. The booking will be cancelled.',
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pushNamedAndRemoveUntil(
                context,
                AppRoutes.home,
                (route) => false,
              );
            },
            child: const Text('Back to Home'),
          ),
        ],
      ),
    );
  }
  
  Future<void> _processPayment() async {
    if (_selectedPaymentMethod.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select a payment method'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }
    
    setState(() {
      _isLoading = true;
    });
    
    try {
      final bookingProvider = Provider.of<BookingProvider>(context, listen: false);
      final success = await bookingProvider.processPayment(
        _selectedPaymentMethod,
        _selectedPaymentType,
      );
      
      if (success && mounted) {
        // Show confirmation screen
        Navigator.pushNamedAndRemoveUntil(
          context,
          AppRoutes.bookingConfirmation,
          (route) => false,
          arguments: {'bookingId': widget.bookingId},
        );
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(bookingProvider.paymentError ?? 'Payment processing failed'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final currencyFormat = NumberFormat.currency(
      locale: 'id',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Payment'),
      ),
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: 'Processing payment...',
        child: Column(
          children: [
            // Payment timeout indicator
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingRegular),
              color: Colors.amber,
              child: Row(
                children: [
                  const Icon(
                    Icons.timer,
                    color: Colors.black87,
                    size: 20,
                  ),
                  const SizedBox(width: AppTheme.paddingSmall),
                  Text(
                    'Payment will expire in: $_formattedRemainingTime',
                    style: const TextStyle(
                      color: Colors.black87,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
            
            // Main content
            Expanded(
              child: Consumer<BookingProvider>(
                builder: (context, bookingProvider, _) {
                  final booking = bookingProvider.currentBooking;
                  
                  if (booking == null) {
                    return const Center(
                      child: Text('Booking information not available'),
                    );
                  }
                  
                  return SingleChildScrollView(
                    padding: const EdgeInsets.all(AppTheme.paddingMedium),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Booking summary
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
                                'Booking Summary',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeMedium,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: AppTheme.paddingRegular),
                              
                              // Booking number
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    'Booking Number',
                                    style: TextStyle(
                                      color: theme.textTheme.bodyMedium?.color,
                                    ),
                                  ),
                                  Text(
                                    booking.bookingNumber,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: AppTheme.paddingSmall),
                              
                              // Route
                              if (booking.schedule?.route != null)
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Route',
                                      style: TextStyle(
                                        color: theme.textTheme.bodyMedium?.color,
                                      ),
                                    ),
                                    Text(
                                      booking.schedule!.route!.routeName,
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              const SizedBox(height: AppTheme.paddingSmall),
                              
                              // Departure time
                              if (booking.schedule != null)
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Departure Time',
                                      style: TextStyle(
                                        color: theme.textTheme.bodyMedium?.color,
                                      ),
                                    ),
                                    Text(
                                      '${DateFormat('EEE, dd MMM yyyy').format(booking.schedule!.departureTime)} ${booking.schedule!.formattedDepartureTime}',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              const SizedBox(height: AppTheme.paddingSmall),
                              
                              // Passengers
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    'Passengers',
                                    style: TextStyle(
                                      color: theme.textTheme.bodyMedium?.color,
                                    ),
                                  ),
                                  Text(
                                    '${booking.passengerCount} ${booking.passengerCount > 1 ? 'persons' : 'person'}',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: AppTheme.paddingSmall),
                              
                              // Vehicle
                              if (booking.hasVehicles && booking.vehicles != null && booking.vehicles!.isNotEmpty)
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Vehicle',
                                      style: TextStyle(
                                        color: theme.textTheme.bodyMedium?.color,
                                      ),
                                    ),
                                    Text(
                                      '${booking.vehicles!.first.typeText} (${booking.vehicles!.first.licensePlate})',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              
                              const SizedBox(height: AppTheme.paddingMedium),
                              const Divider(),
                              const SizedBox(height: AppTheme.paddingMedium),
                              
                              // Total amount
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text(
                                    'Total Amount',
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: AppTheme.fontSizeMedium,
                                    ),
                                  ),
                                  Text(
                                    currencyFormat.format(booking.totalAmount),
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: AppTheme.fontSizeMedium,
                                      color: theme.primaryColor,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        
                        const SizedBox(height: AppTheme.paddingLarge),
                        
                        // Payment method
                        Text(
                          'Select Payment Method',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeMedium,
                            fontWeight: FontWeight.bold,
                            color: theme.textTheme.displaySmall?.color,
                          ),
                        ),
                        const SizedBox(height: AppTheme.paddingMedium),
                        
                        // Payment type tabs
                        Container(
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
                            children: [
                              // Payment type selection
                              Row(
                                children: [
                                  _buildPaymentTypeTab(
                                    title: 'Bank Transfer',
                                    type: 'bank_transfer',
                                  ),
                                  _buildPaymentTypeTab(
                                    title: 'E-Wallet',
                                    type: 'e_wallet',
                                  ),
                                ],
                              ),
                              
                              const Divider(height: 1),
                              
                              // Payment methods based on selected type
                              ..._buildPaymentMethodOptions(bookingProvider),
                            ],
                          ),
                        ),
                        
                        const SizedBox(height: AppTheme.paddingLarge),
                        
                        // Payment instructions
                        if (_selectedPaymentMethod.isNotEmpty) ...[
                          Text(
                            'Payment Instructions',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeMedium,
                              fontWeight: FontWeight.bold,
                              color: theme.textTheme.displaySmall?.color,
                            ),
                          ),
                          const SizedBox(height: AppTheme.paddingMedium),
                          
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
                            child: _buildPaymentInstructions(bookingProvider),
                          ),
                          
                          const SizedBox(height: AppTheme.paddingLarge),
                        ],
                      ],
                    ),
                  );
                },
              ),
            ),
            
            // Pay now button
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              decoration: BoxDecoration(
                color: theme.cardColor,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 4,
                    offset: const Offset(0, -2),
                  ),
                ],
              ),
              child: CustomButton(
                text: 'Pay Now',
                onPressed: _processPayment,
                type: ButtonType.primary,
                isFullWidth: true,
              ),
            ),
          ],
        ),
      ),
    );
  }
  
  Widget _buildPaymentTypeTab({
    required String title,
    required String type,
  }) {
    final theme = Theme.of(context);
    final isSelected = _selectedPaymentType == type;
    
    return Expanded(
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedPaymentType = type;
            _selectedPaymentMethod = ''; // Reset payment method when type changes
          });
        },
        child: Container(
          padding: const EdgeInsets.symmetric(
            vertical: AppTheme.paddingRegular,
          ),
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isSelected ? AppTheme.primaryColor : Colors.transparent,
                width: 2,
              ),
            ),
          ),
          child: Text(
            title,
            style: TextStyle(
              color: isSelected ? AppTheme.primaryColor : theme.textTheme.bodyLarge?.color,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            ),
            textAlign: TextAlign.center,
          ),
        ),
      ),
    );
  }
  
  List<Widget> _buildPaymentMethodOptions(BookingProvider bookingProvider) {
    final paymentMethods = bookingProvider.getPaymentMethodsByType(_selectedPaymentType);
    
    return paymentMethods.map((method) {
      final isSelected = _selectedPaymentMethod == method['id'];
      
      return InkWell(
        onTap: () {
          setState(() {
            _selectedPaymentMethod = method['id'] as String;
          });
        },
        child: Container(
          padding: const EdgeInsets.all(AppTheme.paddingMedium),
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: Theme.of(context).dividerColor,
                width: 0.5,
              ),
            ),
            color: isSelected ? AppTheme.primaryColor.withOpacity(0.1) : null,
          ),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(AppTheme.borderRadiusSmall),
                  image: DecorationImage(
                    image: AssetImage(method['icon'] as String),
                    fit: BoxFit.contain,
                  ),
                ),
              ),
              const SizedBox(width: AppTheme.paddingMedium),
              Expanded(
                child: Text(
                  method['name'] as String,
                  style: const TextStyle(
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              Radio<String>(
                value: method['id'] as String,
                groupValue: _selectedPaymentMethod,
                onChanged: (value) {
                  setState(() {
                    _selectedPaymentMethod = value!;
                  });
                },
                activeColor: AppTheme.primaryColor,
              ),
            ],
          ),
        ),
      );
    }).toList();
  }
  
  Widget _buildPaymentInstructions(BookingProvider bookingProvider) {
    if (_selectedPaymentMethod.isEmpty) {
      return const SizedBox.shrink();
    }
    
    final instructions = bookingProvider.getPaymentInstructions(
      _selectedPaymentMethod,
      _selectedPaymentType,
    );
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          instructions['title'] ?? 'Payment Instructions',
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: AppTheme.fontSizeMedium,
          ),
        ),
        const SizedBox(height: AppTheme.paddingMedium),
        Text(
          instructions['steps'] ?? 'No instructions available',
          style: const TextStyle(
            fontSize: AppTheme.fontSizeRegular,
          ),
        ),
        const SizedBox(height: AppTheme.paddingMedium),
        Container(
          padding: const EdgeInsets.all(AppTheme.paddingRegular),
          decoration: BoxDecoration(
            color: Colors.amber.shade50,
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
            border: Border.all(color: Colors.amber.shade200),
          ),
          child: Row(
            children: [
              const Icon(
                Icons.info_outline,
                color: Colors.amber,
                size: 24,
              ),
              const SizedBox(width: AppTheme.paddingRegular),
              Expanded(
                child: Text(
                  'Please complete your payment within $_formattedRemainingTime to secure your booking.',
                  style: const TextStyle(
                    fontSize: AppTheme.fontSizeRegular,
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}