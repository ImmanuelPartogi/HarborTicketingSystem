import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_indicator.dart';
import '../../models/payment_model.dart'; // Import the payment model

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
  String _selectedPaymentType = 'virtual_account';

  bool _isLoading = false;
  bool _isPaymentProcessed = false;
  Timer? _paymentCheckTimer;
  int _timeoutMinutes = 15;
  int _remainingSeconds = 0;

  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  void initState() {
    super.initState();

    // Gunakan Future.microtask untuk memastikan build selesai terlebih dahulu
    Future.microtask(() {
      _loadBookingDetails();
      _startPaymentTimer();
    });
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
      // Validasi ID
      if (widget.bookingId <= 0) {
        throw Exception('Invalid booking ID: ${widget.bookingId}');
      }

      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );

      // Pastikan booking provider sudah diinisialisasi
      if (!bookingProvider.isInitialized) {
        await Future.delayed(Duration(seconds: 1));
      }

      // Gunakan current booking jika sudah ada
      if (bookingProvider.currentBooking?.id == widget.bookingId) {
        print('Using existing booking data, skipping fetch');
      } else {
        await bookingProvider.fetchBookingDetail(widget.bookingId);
      }

      // Double-check
      if (bookingProvider.currentBooking == null) {
        throw Exception('Failed to load booking details');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
        Navigator.pop(context);
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
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

      // Check payment status every 15 seconds
      if (_remainingSeconds % 15 == 0 && _isPaymentProcessed) {
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
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );
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
      builder:
          (context) => AlertDialog(
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
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );
      final success = await bookingProvider.processPayment(
        _selectedPaymentType,
        _selectedPaymentMethod,
      );

      setState(() {
        _isPaymentProcessed = success;
      });

      // Refresh the booking data to get payment details
      if (success && mounted) {
        await bookingProvider.fetchBookingDetail(widget.bookingId);

        // Check if we need to navigate to another screen
        final booking = bookingProvider.currentBooking;
        final payment = booking?.payment;

        if (payment != null) {
          if (payment.paymentUrl != null && payment.paymentUrl!.isNotEmpty) {
            // Handle redirect URLs for e-wallets
            if (_selectedPaymentType == 'e_wallet') {
              final url = payment.paymentUrl!;
              if (await canLaunch(url)) {
                await launch(url);
              }
            }
          }
        }
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              bookingProvider.paymentError ?? 'Payment processing failed',
            ),
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

  // Copy text to clipboard
  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$label copied to clipboard'),
        backgroundColor: Colors.green,
        duration: const Duration(seconds: 2),
      ),
    );
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
      key: _scaffoldKey,
      appBar: AppBar(title: const Text('Payment')),
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
                  const Icon(Icons.timer, color: Colors.black87, size: 20),
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

                  // Get payment data if available
                  final payment = booking.payment;
                  final isPaymentCreated = payment != null;

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
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusMedium,
                            ),
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
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
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
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Route',
                                      style: TextStyle(
                                        color:
                                            theme.textTheme.bodyMedium?.color,
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
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Departure Time',
                                      style: TextStyle(
                                        color:
                                            theme.textTheme.bodyMedium?.color,
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
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
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
                              if (booking.hasVehicles &&
                                  booking.vehicles != null &&
                                  booking.vehicles!.isNotEmpty)
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Vehicle',
                                      style: TextStyle(
                                        color:
                                            theme.textTheme.bodyMedium?.color,
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
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
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

                        // Show payment methods if payment not yet created
                        if (!isPaymentCreated) ...[
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
                              borderRadius: BorderRadius.circular(
                                AppTheme.borderRadiusMedium,
                              ),
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
                                      title: 'Virtual Account',
                                      type: 'virtual_account',
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
                              padding: const EdgeInsets.all(
                                AppTheme.paddingMedium,
                              ),
                              decoration: BoxDecoration(
                                color: theme.cardColor,
                                borderRadius: BorderRadius.circular(
                                  AppTheme.borderRadiusMedium,
                                ),
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
                          ],
                        ],

                        // Show payment details if payment already created
                        if (isPaymentCreated) ...[
                          Text(
                            'Payment Details',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeMedium,
                              fontWeight: FontWeight.bold,
                              color: theme.textTheme.displaySmall?.color,
                            ),
                          ),
                          const SizedBox(height: AppTheme.paddingMedium),

                          Container(
                            padding: const EdgeInsets.all(
                              AppTheme.paddingMedium,
                            ),
                            decoration: BoxDecoration(
                              color: theme.cardColor,
                              borderRadius: BorderRadius.circular(
                                AppTheme.borderRadiusMedium,
                              ),
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
                                // Payment status
                                Row(
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          const Text(
                                            'Payment Status',
                                            style: TextStyle(
                                              fontSize:
                                                  AppTheme.fontSizeRegular,
                                              color: Colors.grey,
                                            ),
                                          ),
                                          const SizedBox(
                                            height: AppTheme.paddingXSmall,
                                          ),
                                          Row(
                                            children: [
                                              Container(
                                                width: 12,
                                                height: 12,
                                                decoration: BoxDecoration(
                                                  color: payment!.statusColor,
                                                  shape: BoxShape.circle,
                                                ),
                                              ),
                                              const SizedBox(
                                                width: AppTheme.paddingXSmall,
                                              ),
                                              Text(
                                                payment.statusText,
                                                style: TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  color: payment.statusColor,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                    ElevatedButton(
                                      onPressed: _checkPaymentStatus,
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: theme.primaryColor,
                                        foregroundColor: Colors.white,
                                        textStyle: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                        ),
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: AppTheme.paddingMedium,
                                          vertical: AppTheme.paddingSmall,
                                        ),
                                      ),
                                      child: const Text('Check Status'),
                                    ),
                                  ],
                                ),

                                const SizedBox(height: AppTheme.paddingMedium),
                                const Divider(),
                                const SizedBox(height: AppTheme.paddingMedium),

                                // Payment method info
                                const Text(
                                  'Payment Method',
                                  style: TextStyle(
                                    fontSize: AppTheme.fontSizeRegular,
                                    color: Colors.grey,
                                  ),
                                ),
                                const SizedBox(height: AppTheme.paddingXSmall),
                                Row(
                                  children: [
                                    Container(
                                      width: 40,
                                      height: 40,
                                      decoration: BoxDecoration(
                                        color: Colors.grey.shade100,
                                        borderRadius: BorderRadius.circular(
                                          AppTheme.borderRadiusSmall,
                                        ),
                                        image: DecorationImage(
                                          image: AssetImage(
                                            'assets/images/payment_methods/${payment.paymentChannel.toLowerCase()}.png',
                                          ),
                                          fit: BoxFit.contain,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(
                                      width: AppTheme.paddingSmall,
                                    ),
                                    Text(
                                      '${payment.paymentMethodText} - ${payment.paymentChannelText}',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),

                                const SizedBox(height: AppTheme.paddingMedium),

                                // Virtual Account Number for bank transfers
                                if (payment.paymentMethod.toUpperCase() ==
                                        'VIRTUAL_ACCOUNT' &&
                                    payment.vaNumber != null) ...[
                                  const Text(
                                    'Virtual Account Number',
                                    style: TextStyle(
                                      fontSize: AppTheme.fontSizeRegular,
                                      color: Colors.grey,
                                    ),
                                  ),
                                  const SizedBox(
                                    height: AppTheme.paddingXSmall,
                                  ),
                                  Container(
                                    padding: const EdgeInsets.all(
                                      AppTheme.paddingRegular,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.grey.shade100,
                                      borderRadius: BorderRadius.circular(
                                        AppTheme.borderRadiusRegular,
                                      ),
                                      border: Border.all(
                                        color: Colors.grey.shade300,
                                      ),
                                    ),
                                    child: Row(
                                      children: [
                                        Expanded(
                                          child: Text(
                                            payment.vaNumber!,
                                            style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: AppTheme.fontSizeMedium,
                                              letterSpacing: 1.5,
                                            ),
                                          ),
                                        ),
                                        IconButton(
                                          onPressed:
                                              () => _copyToClipboard(
                                                payment.vaNumber!,
                                                'VA Number',
                                              ),
                                          icon: const Icon(Icons.copy),
                                          tooltip: 'Copy VA Number',
                                          color: theme.primaryColor,
                                        ),
                                      ],
                                    ),
                                  ),
                                ],

                                // QR Code for e-wallets
                                if (payment.paymentMethod.toUpperCase() ==
                                        'E_WALLET' &&
                                    payment.qrCodeUrl != null) ...[
                                  const SizedBox(
                                    height: AppTheme.paddingMedium,
                                  ),
                                  const Text(
                                    'Scan QR Code',
                                    style: TextStyle(
                                      fontSize: AppTheme.fontSizeRegular,
                                      color: Colors.grey,
                                    ),
                                  ),
                                  const SizedBox(height: AppTheme.paddingSmall),
                                  Center(
                                    child: Container(
                                      width: 200,
                                      height: 200,
                                      padding: const EdgeInsets.all(
                                        AppTheme.paddingSmall,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.white,
                                        borderRadius: BorderRadius.circular(
                                          AppTheme.borderRadiusSmall,
                                        ),
                                        border: Border.all(
                                          color: Colors.grey.shade300,
                                        ),
                                      ),
                                      child: Image.network(
                                        payment.qrCodeUrl!,
                                        errorBuilder: (
                                          context,
                                          error,
                                          stackTrace,
                                        ) {
                                          return const Center(
                                            child: Text(
                                              'Could not load QR Code',
                                              textAlign: TextAlign.center,
                                            ),
                                          );
                                        },
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: AppTheme.paddingSmall),
                                  Center(
                                    child: Text(
                                      'Scan with your ${payment.paymentChannelText} app',
                                      style: TextStyle(
                                        fontSize: AppTheme.fontSizeSmall,
                                        color:
                                            theme.textTheme.bodyMedium?.color,
                                      ),
                                    ),
                                  ),
                                ],

                                // Deep link button for e-wallets
                                if (payment.paymentMethod.toUpperCase() ==
                                        'E_WALLET' &&
                                    payment.deepLinkUrl != null) ...[
                                  const SizedBox(
                                    height: AppTheme.paddingMedium,
                                  ),
                                  Center(
                                    child: CustomButton(
                                      text:
                                          'Open ${payment.paymentChannelText} App',
                                      onPressed: () async {
                                        if (await canLaunch(
                                          payment.deepLinkUrl!,
                                        )) {
                                          await launch(payment.deepLinkUrl!);
                                        } else {
                                          ScaffoldMessenger.of(
                                            context,
                                          ).showSnackBar(
                                            SnackBar(
                                              content: Text(
                                                'Could not open ${payment.paymentChannelText} app',
                                              ),
                                              backgroundColor: Colors.red,
                                            ),
                                          );
                                        }
                                      },
                                      type: ButtonType.primary,
                                      icon: Icons.open_in_new,
                                    ),
                                  ),
                                ],

                                const SizedBox(height: AppTheme.paddingMedium),

                                // Payment instructions
                                const Text(
                                  'Payment Instructions',
                                  style: TextStyle(
                                    fontSize: AppTheme.fontSizeRegular,
                                    color: Colors.grey,
                                  ),
                                ),
                                const SizedBox(height: AppTheme.paddingSmall),
                                _buildPaymentInstructionsForExistingPayment(
                                  payment,
                                ),

                                const SizedBox(height: AppTheme.paddingMedium),

                                // Expiry info
                                Container(
                                  padding: const EdgeInsets.all(
                                    AppTheme.paddingRegular,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.amber.shade50,
                                    borderRadius: BorderRadius.circular(
                                      AppTheme.borderRadiusRegular,
                                    ),
                                    border: Border.all(
                                      color: Colors.amber.shade200,
                                    ),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(
                                        Icons.info_outline,
                                        color: Colors.amber,
                                        size: 24,
                                      ),
                                      const SizedBox(
                                        width: AppTheme.paddingRegular,
                                      ),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            const Text(
                                              'Important',
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                            const SizedBox(
                                              height: AppTheme.paddingXSmall,
                                            ),
                                            Text(
                                              'Please complete your payment within $_formattedRemainingTime to secure your booking. Payment will expire at ${DateFormat('HH:mm').format(payment.expiredAt)}.',
                                              style: const TextStyle(
                                                fontSize:
                                                    AppTheme.fontSizeRegular,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],

                        const SizedBox(height: AppTheme.paddingLarge),
                      ],
                    ),
                  );
                },
              ),
            ),

            // Pay now button (only show if payment not yet created)
            Consumer<BookingProvider>(
              builder: (context, bookingProvider, _) {
                final booking = bookingProvider.currentBooking;
                final isPaymentCreated = booking?.payment != null;

                if (isPaymentCreated) {
                  return Container(
                    padding: const EdgeInsets.all(AppTheme.paddingMedium),
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardColor,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 4,
                          offset: const Offset(0, -2),
                        ),
                      ],
                    ),
                    child: CustomButton(
                      text: 'Check Booking Status',
                      onPressed: () {
                        Navigator.pushNamedAndRemoveUntil(
                          context,
                          AppRoutes.bookingConfirmation,
                          (route) => false,
                          arguments: {'bookingId': widget.bookingId},
                        );
                      },
                      type: ButtonType.primary,
                      isFullWidth: true,
                    ),
                  );
                }

                return Container(
                  padding: const EdgeInsets.all(AppTheme.paddingMedium),
                  decoration: BoxDecoration(
                    color: Theme.of(context).cardColor,
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
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPaymentTypeTab({required String title, required String type}) {
    final theme = Theme.of(context);
    final isSelected = _selectedPaymentType == type;

    return Expanded(
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedPaymentType = type;
            _selectedPaymentMethod =
                ''; // Reset payment method when type changes
          });
        },
        child: Container(
          padding: const EdgeInsets.symmetric(
            vertical: AppTheme.paddingRegular,
          ),
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isSelected ? theme.primaryColor : Colors.transparent,
                width: 2,
              ),
            ),
          ),
          child: Text(
            title,
            style: TextStyle(
              color:
                  isSelected
                      ? theme.primaryColor
                      : theme.textTheme.bodyLarge?.color,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            ),
            textAlign: TextAlign.center,
          ),
        ),
      ),
    );
  }

  List<Widget> _buildPaymentMethodOptions(BookingProvider bookingProvider) {
    final paymentMethods = bookingProvider.getPaymentMethodsByType(
      _selectedPaymentType,
    );

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
                  borderRadius: BorderRadius.circular(
                    AppTheme.borderRadiusSmall,
                  ),
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
                  style: const TextStyle(fontWeight: FontWeight.w500),
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
          style: const TextStyle(fontSize: AppTheme.fontSizeRegular),
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
              const Icon(Icons.info_outline, color: Colors.amber, size: 24),
              const SizedBox(width: AppTheme.paddingRegular),
              Expanded(
                child: Text(
                  'Please complete your payment within $_formattedRemainingTime to secure your booking.',
                  style: const TextStyle(fontSize: AppTheme.fontSizeRegular),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildPaymentInstructionsForExistingPayment(Payment payment) {
    String instructionsContent = '';

    // Determine which instructions to show based on payment method
    if (payment.paymentMethod.toUpperCase() == 'VIRTUAL_ACCOUNT') {
      instructionsContent = '''
1. Login to your ${payment.paymentChannelText} Mobile Banking app or Internet Banking.
2. Choose "Transfer" > "Virtual Account".
3. Enter the Virtual Account number: ${payment.vaNumber ?? 'Not available'}.
4. Confirm the payment details and amount: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
5. Enter your PIN or password to authorize the payment.
6. Save your payment receipt as proof of transaction.
''';
    } else if (payment.paymentMethod.toUpperCase() == 'E_WALLET') {
      if (payment.qrCodeUrl != null) {
        instructionsContent = '''
1. Open your ${payment.paymentChannelText} app.
2. Tap "Scan" or "Pay" in your app.
3. Scan the QR code shown above.
4. Confirm the payment details and amount: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
5. Enter your PIN to authorize the payment.
6. Wait for confirmation message.
''';
      } else if (payment.deepLinkUrl != null) {
        instructionsContent = '''
1. Tap the "Open ${payment.paymentChannelText} App" button above.
2. You will be redirected to your ${payment.paymentChannelText} app.
3. Confirm the payment details and amount: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
4. Enter your PIN to authorize the payment.
5. Wait for confirmation message.
''';
      } else {
        instructionsContent = '''
1. Open your ${payment.paymentChannelText} app.
2. Choose "Pay" or "Scan".
3. Enter the payment amount: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
4. Confirm the payment details.
5. Enter your PIN to authorize the payment.
6. Wait for confirmation message.
''';
      }
    } else {
      instructionsContent = '''
1. Follow the payment instructions for your selected payment method.
2. Use the payment details provided above.
3. Ensure you complete the payment before the expiry time.
4. After payment, wait for confirmation message.
''';
    }

    return Text(
      instructionsContent,
      style: const TextStyle(fontSize: AppTheme.fontSizeRegular),
    );
  }
}

class LoadingOverlay extends StatelessWidget {
  final bool isLoading;
  final Widget child;
  final String loadingMessage;
  final Color color;

  const LoadingOverlay({
    Key? key,
    required this.isLoading,
    required this.child,
    this.loadingMessage = 'Loading...',
    this.color = Colors.white,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        child,
        if (isLoading)
          Container(
            color: Colors.black.withOpacity(0.3),
            child: Center(
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const CircularProgressIndicator(),
                    const SizedBox(height: 20),
                    Text(
                      loadingMessage,
                      style: const TextStyle(
                        color: Colors.black87,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
      ],
    );
  }
}
