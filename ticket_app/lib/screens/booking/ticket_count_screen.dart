import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/common/custom_button.dart';

class TicketCountScreen extends StatefulWidget {
  final int scheduleId;
  final bool hasVehicle;

  const TicketCountScreen({
    Key? key,
    required this.scheduleId,
    this.hasVehicle = false,
  }) : super(key: key);

  @override
  State<TicketCountScreen> createState() => _TicketCountScreenState();
}

class _TicketCountScreenState extends State<TicketCountScreen> {
  int _ticketCount = 1;

  @override
  void initState() {
    super.initState();
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );
    bookingProvider.setScheduleId(widget.scheduleId);
    bookingProvider.clearPassengers();
  }

  void _incrementTicketCount() {
    setState(() {
      if (_ticketCount < 10) {
        _ticketCount++;
      }
    });
  }

  void _decrementTicketCount() {
    setState(() {
      if (_ticketCount > 1) {
        _ticketCount--;
      }
    });
  }

  void _continue() {
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );

    // Tambahkan dummy data tiket (tanpa data penumpang)
    for (int i = 0; i < _ticketCount; i++) {
      bookingProvider.addPassenger({'ticket_number': 'TICKET-${i + 1}'});
    }

    if (widget.hasVehicle) {
      Navigator.pushNamed(
        context,
        AppRoutes.vehicleDetails,
        arguments: {
          'scheduleId': widget.scheduleId,
          'ticketCount': _ticketCount,
        },
      );
    } else {
      // Berikan context ke createBooking()
      bookingProvider.createBooking(context).then((success) {
        if (success) {
          Navigator.pushNamed(
            context,
            AppRoutes.payment,
            arguments: {
              'bookingId': bookingProvider.currentBooking!.id,
              'totalAmount': bookingProvider.currentBooking!.totalAmount,
            },
          );
        }
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Jumlah Tiket')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppTheme.paddingMedium),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Berapa banyak tiket yang ingin Anda beli?',
              style: TextStyle(
                fontSize: AppTheme.fontSizeLarge,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: AppTheme.paddingMedium),

            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(
                  AppTheme.borderRadiusMedium,
                ),
              ),
              child: Padding(
                padding: const EdgeInsets.all(AppTheme.paddingMedium),
                child: Column(
                  children: [
                    const Text(
                      'Jumlah Tiket',
                      style: TextStyle(
                        fontSize: AppTheme.fontSizeMedium,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: AppTheme.paddingMedium),

                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        IconButton(
                          onPressed: _decrementTicketCount,
                          icon: const Icon(Icons.remove_circle_outline),
                          color: Theme.of(context).primaryColor,
                          iconSize: 32,
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingLarge,
                            vertical: AppTheme.paddingRegular,
                          ),
                          margin: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingRegular,
                          ),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusSmall,
                            ),
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: Text(
                            '$_ticketCount',
                            style: const TextStyle(
                              fontSize: AppTheme.fontSizeLarge,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        IconButton(
                          onPressed: _incrementTicketCount,
                          icon: const Icon(Icons.add_circle_outline),
                          color: Theme.of(context).primaryColor,
                          iconSize: 32,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: AppTheme.paddingLarge),

            CustomButton(
              text:
                  widget.hasVehicle
                      ? 'Lanjut ke Detail Kendaraan'
                      : 'Lanjut ke Pembayaran',
              onPressed: _continue,
              type: ButtonType.primary,
              isFullWidth: true,
            ),
          ],
        ),
      ),
    );
  }
}
