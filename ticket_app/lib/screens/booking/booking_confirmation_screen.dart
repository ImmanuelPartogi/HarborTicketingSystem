import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../providers/ticket_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_indicator.dart';

class BookingConfirmationScreen extends StatefulWidget {
  final int bookingId;

  const BookingConfirmationScreen({Key? key, required this.bookingId})
    : super(key: key);

  @override
  State<BookingConfirmationScreen> createState() =>
      _BookingConfirmationScreenState();
}

class _BookingConfirmationScreenState extends State<BookingConfirmationScreen> {
  bool _isLoading = false;
  bool _isCheckingTickets = false;
  bool _ticketsAvailable = false;

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
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );
      await bookingProvider.fetchBookingDetail(widget.bookingId);

      // Jika booking sudah confirmed, periksa ketersediaan tiket
      final booking = bookingProvider.currentBooking;
      if (booking != null && booking.isConfirmed) {
        await _checkTicketsAvailability();
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  // Fungsi untuk memeriksa apakah tiket sudah tersedia
  Future<void> _checkTicketsAvailability() async {
    if (mounted) {
      setState(() {
        _isCheckingTickets = true;
      });
    }

    try {
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );
      final ticketProvider = Provider.of<TicketProvider>(
        context,
        listen: false,
      );

      final booking = bookingProvider.currentBooking;
      if (booking == null) return;

      // Cek apakah booking sudah memiliki relasi tickets
      if (booking.tickets != null && booking.tickets!.isNotEmpty) {
        setState(() {
          _ticketsAvailable = true;
        });
        return;
      }

      // Jika tickets belum ada di objek booking, coba fetch dengan booking ID
      final tickets = await ticketProvider.fetchTicketsByBookingId(booking.id);

      if (mounted) {
        setState(() {
          _ticketsAvailable = tickets.isNotEmpty;
        });
      }

      // Jika tidak ada tiket tetapi booking sudah confirmed, mungkin
      // tiket belum dibuat secara otomatis. Ini mungkin masalah backend.
      if (tickets.isEmpty && booking.isConfirmed) {
        print('Booking confirmed but no tickets found. Generating tickets...');
        await _generateTickets(); // TAMBAHKAN BARIS INI!
      }
    } catch (e) {
      print('Error checking tickets: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isCheckingTickets = false;
        });
      }
    }
  }

  // Fungsi untuk memicu pembuatan tiket (jika API mendukung)
  Future<void> _generateTickets() async {
    setState(() {
      _isCheckingTickets = true; // Tambahkan loading indicator
    });

    try {
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );
      final booking = bookingProvider.currentBooking;
      if (booking == null) return;

      // AKTIFKAN BARIS INI - Hapus komentar dan jalankan generateTickets
      final success = await bookingProvider.generateTickets(booking.id);

      if (success) {
        // Refresh booking untuk mendapatkan tiket yang baru dibuat
        await bookingProvider.fetchBookingDetail(booking.id);
        setState(() {
          _ticketsAvailable = true;
        });

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tickets generated successfully!'),
            backgroundColor: Colors.green,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              bookingProvider.bookingError ?? 'Failed to generate tickets',
            ),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      print('Error generating tickets: $e');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: ${e.toString()}'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isCheckingTickets = false; // Hilangkan loading indicator
      });
    }
  }

  void _viewTickets() {
    // Tambahkan reload active tickets sebelum navigasi
    final ticketProvider = Provider.of<TicketProvider>(context, listen: false);
    ticketProvider.fetchActiveTickets().then((_) {
      Navigator.pushNamedAndRemoveUntil(
        context,
        AppRoutes.ticketList,
        (route) => false,
      );
    });
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
        child:
            _isLoading
                ? const Center(
                  child: LoadingIndicator(
                    message: 'Loading booking details...',
                  ),
                )
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

                    // Determine booking status
                    final isConfirmed = booking.isConfirmed;
                    final isPending = booking.isPending;
                    final isPaymentCompleted =
                        booking.payment?.isCompleted ?? false;

                    return Stack(
                      children: [
                        SingleChildScrollView(
                          padding: const EdgeInsets.all(AppTheme.paddingLarge),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              // Status icon
                              Container(
                                width: 120,
                                height: 120,
                                decoration: BoxDecoration(
                                  color:
                                      isConfirmed
                                          ? Colors.green.withOpacity(0.1)
                                          : Colors.amber.withOpacity(0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  isConfirmed
                                      ? Icons.check_circle
                                      : Icons.pending,
                                  size: 80,
                                  color:
                                      isConfirmed ? Colors.green : Colors.amber,
                                ),
                              ),

                              const SizedBox(height: AppTheme.paddingLarge),

                              // Status message
                              Text(
                                isConfirmed
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
                                isConfirmed
                                    ? _ticketsAvailable
                                        ? 'Your tickets are ready! You can view them now.'
                                        : 'Your booking is confirmed. Tickets are being prepared.'
                                    : 'Please complete your payment to confirm your booking',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                  color:
                                      Theme.of(
                                        context,
                                      ).textTheme.bodyMedium?.color,
                                ),
                                textAlign: TextAlign.center,
                              ),

                              const SizedBox(height: AppTheme.paddingXLarge),

                              // Booking details
                              Container(
                                padding: const EdgeInsets.all(
                                  AppTheme.paddingMedium,
                                ),
                                decoration: BoxDecoration(
                                  color: Theme.of(context).cardColor,
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
                                      'Booking Details',
                                      style: TextStyle(
                                        fontSize: AppTheme.fontSizeMedium,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(
                                      height: AppTheme.paddingMedium,
                                    ),

                                    // Booking number
                                    _buildInfoRow(
                                      label: 'Booking Number',
                                      value: booking.bookingNumber,
                                    ),

                                    // Route
                                    if (booking.schedule?.route != null)
                                      _buildInfoRow(
                                        label: 'Route',
                                        value:
                                            booking.schedule!.route!.routeName,
                                      ),

                                    // Departure time
                                    if (booking.schedule != null)
                                      _buildInfoRow(
                                        label: 'Departure Date',
                                        value: DateFormat(
                                          'EEE, dd MMM yyyy',
                                        ).format(
                                          booking.schedule!.departureTime,
                                        ),
                                      ),

                                    if (booking.schedule != null)
                                      _buildInfoRow(
                                        label: 'Departure Time',
                                        value:
                                            booking
                                                .schedule!
                                                .formattedDepartureTime,
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
                                      value:
                                          '${booking.passengerCount} ${booking.passengerCount > 1 ? 'persons' : 'person'}',
                                    ),

                                    // Vehicle
                                    if (booking.hasVehicles &&
                                        booking.vehicles != null &&
                                        booking.vehicles!.isNotEmpty)
                                      _buildInfoRow(
                                        label: 'Vehicle',
                                        value:
                                            '${booking.vehicles!.first.typeText} (${booking.vehicles!.first.licensePlate})',
                                      ),

                                    // Booking status
                                    _buildInfoRow(
                                      label: 'Booking Status',
                                      value: booking.statusText,
                                      valueColor:
                                          isConfirmed
                                              ? Colors.green
                                              : Colors.amber,
                                    ),

                                    // Payment status
                                    _buildInfoRow(
                                      label: 'Payment Status',
                                      value:
                                          booking.payment?.statusText ??
                                          'Unknown',
                                      valueColor: _getPaymentStatusColor(
                                        booking.payment?.status,
                                      ),
                                    ),

                                    // Tickets status (new)
                                    if (isConfirmed)
                                      _buildInfoRow(
                                        label: 'Tickets',
                                        value:
                                            _isCheckingTickets
                                                ? 'Checking...'
                                                : _ticketsAvailable
                                                ? 'Available'
                                                : 'Processing',
                                        valueColor:
                                            _ticketsAvailable
                                                ? Colors.green
                                                : Colors.amber,
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
                              if (isConfirmed) ...[
                                if (_ticketsAvailable)
                                  CustomButton(
                                    text: 'View My Tickets',
                                    onPressed: _viewTickets,
                                    type: ButtonType.primary,
                                    isFullWidth: true,
                                    icon: Icons.confirmation_number,
                                  )
                                else
                                  CustomButton(
                                    text: 'Generate My Tickets',
                                    onPressed: _generateTickets,
                                    type: ButtonType.primary,
                                    isFullWidth: true,
                                    icon: Icons.assignment,
                                  ),

                                const SizedBox(height: AppTheme.paddingMedium),

                                CustomButton(
                                  text: 'Back to Home',
                                  onPressed: _backToHome,
                                  type: ButtonType.outline,
                                  isFullWidth: true,
                                ),
                              ] else if (isPending && isPaymentCompleted) ...[
                                // Show special message if payment is completed but booking not confirmed yet
                                Container(
                                  padding: const EdgeInsets.all(
                                    AppTheme.paddingMedium,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.amber.shade50,
                                    borderRadius: BorderRadius.circular(
                                      AppTheme.borderRadiusMedium,
                                    ),
                                    border: Border.all(color: Colors.amber),
                                  ),
                                  child: Column(
                                    children: [
                                      const Icon(
                                        Icons.access_time,
                                        color: Colors.amber,
                                        size: 32,
                                      ),
                                      const SizedBox(
                                        height: AppTheme.paddingSmall,
                                      ),
                                      const Text(
                                        'Payment Received',
                                        style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: AppTheme.fontSizeMedium,
                                        ),
                                      ),
                                      const SizedBox(
                                        height: AppTheme.paddingSmall,
                                      ),
                                      const Text(
                                        'Your payment has been received. Please wait while we confirm your booking and prepare your tickets.',
                                        textAlign: TextAlign.center,
                                      ),
                                      const SizedBox(
                                        height: AppTheme.paddingSmall,
                                      ),
                                      ElevatedButton(
                                        onPressed: _loadBookingDetail,
                                        child: const Text('Refresh Status'),
                                      ),
                                    ],
                                  ),
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

                              const SizedBox(height: AppTheme.paddingLarge),
                            ],
                          ),
                        ),

                        // Loading overlay for ticket checking
                        if (_isCheckingTickets)
                          Positioned.fill(
                            child: Container(
                              color: Colors.black.withOpacity(0.3),
                              child: const Center(
                                child: LoadingIndicator(
                                  message: 'Checking ticket availability...',
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ),
                      ],
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
