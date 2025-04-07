import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_indicator.dart';

class PassengerCountScreen extends StatefulWidget {
  final int scheduleId;
  final bool hasVehicle;

  const PassengerCountScreen({
    Key? key,
    required this.scheduleId,
    this.hasVehicle = false,
  }) : super(key: key);

  @override
  State<PassengerCountScreen> createState() => _PassengerCountScreenState();
}

class _PassengerCountScreenState extends State<PassengerCountScreen> {
  int _passengerCount = 1;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();

    // Set schedule ID
    Future.microtask(() {
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );
      bookingProvider.setScheduleId(widget.scheduleId);
    });
  }

  void _increasePassengerCount() {
    setState(() {
      if (_passengerCount < 50) { // Batasi maksimum 50 penumpang
        _passengerCount++;
      }
    });
  }

  void _decreasePassengerCount() {
    setState(() {
      if (_passengerCount > 1) {
        _passengerCount--;
      }
    });
  }

  Future<void> _proceedToNext() async {
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );
    
    // Bersihkan data penumpang sebelumnya
    bookingProvider.clearPassengers();
    
    // Tambahkan dummy passenger entries sesuai jumlah
    for (int i = 0; i < _passengerCount; i++) {
      bookingProvider.addPassenger({});
    }

    // Navigate to next screen
    if (widget.hasVehicle) {
      Navigator.pushNamed(
        context,
        AppRoutes.vehicleDetails,
        arguments: {'scheduleId': widget.scheduleId},
      );
    } else {
      // Create booking directly if no vehicle
      try {
        setState(() {
          _isLoading = true;
        });

        final success = await bookingProvider.createBooking();

        if (success && mounted) {
          final booking = bookingProvider.currentBooking;

          if (booking == null || booking.id <= 0) {
            if (mounted) {
              setState(() {
                _isLoading = false;
                _errorMessage =
                    'Booking created but ID is invalid. Please try again.';
              });

              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text(
                    'Booking created but ID is invalid. Please try again.',
                  ),
                  backgroundColor: Colors.red,
                ),
              );
            }
            return;
          }

          // Navigasi ke halaman pembayaran
          if (mounted) {
            Navigator.pushNamed(
              context,
              AppRoutes.payment,
              arguments: {
                'bookingId': booking.id,
                'bookingCode': booking.bookingCode,
                'totalAmount': booking.totalAmount,
              },
            );
          }
        } else if (mounted) {
          setState(() {
            _isLoading = false;
            _errorMessage =
                bookingProvider.bookingError ??
                'Failed to create booking. Please try again.';
          });

          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                bookingProvider.bookingError ??
                    'Failed to create booking. Please try again.',
              ),
              backgroundColor: Colors.red,
            ),
          );
        }
      } catch (e) {
        if (mounted) {
          setState(() {
            _isLoading = false;
            _errorMessage = 'Error: ${e.toString()}';
          });

          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Error: ${e.toString()}'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Passengers')),
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: 'Processing booking...',
        child: Column(
          children: [
            // Error message if any
            if (_errorMessage != null)
              Container(
                padding: const EdgeInsets.all(AppTheme.paddingRegular),
                margin: const EdgeInsets.all(AppTheme.paddingMedium),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(
                    AppTheme.borderRadiusRegular,
                  ),
                  border: Border.all(color: Colors.red.shade200),
                ),
                child: Row(
                  children: [
                    Icon(Icons.error_outline, color: Colors.red),
                    SizedBox(width: AppTheme.paddingSmall),
                    Expanded(
                      child: Text(
                        _errorMessage!,
                        style: TextStyle(color: Colors.red.shade700),
                      ),
                    ),
                    IconButton(
                      icon: Icon(Icons.close, size: 16),
                      onPressed: () {
                        setState(() {
                          _errorMessage = null;
                        });
                      },
                    ),
                  ],
                ),
              ),

            Expanded(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'Select Number of Passengers',
                      style: TextStyle(
                        fontSize: AppTheme.fontSizeLarge,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: AppTheme.paddingLarge),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        IconButton(
                          onPressed: _decreasePassengerCount,
                          icon: Icon(Icons.remove_circle),
                          color: AppTheme.primaryColor,
                          iconSize: 40,
                        ),
                        Container(
                          width: 100,
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingLarge,
                            vertical: AppTheme.paddingMedium,
                          ),
                          decoration: BoxDecoration(
                            border: Border.all(color: theme.dividerColor),
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusRegular,
                            ),
                          ),
                          child: Text(
                            _passengerCount.toString(),
                            style: TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                        IconButton(
                          onPressed: _increasePassengerCount,
                          icon: Icon(Icons.add_circle),
                          color: AppTheme.primaryColor,
                          iconSize: 40,
                        ),
                      ],
                    ),
                    const SizedBox(height: AppTheme.paddingMedium),
                    Text(
                      'Total persons',
                      style: TextStyle(
                        color: theme.textTheme.bodyMedium?.color,
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Bottom navigation bar with continue button
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
                text:
                    widget.hasVehicle
                        ? 'Continue to Vehicle Details'
                        : 'Continue to Payment',
                onPressed: _proceedToNext,
                type: ButtonType.primary,
                isFullWidth: true,
              ),
            ),
          ],
        ),
      ),
    );
  }
}