import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/ferry_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_indicator.dart';
import '../../providers/booking_provider.dart';

class FerryDetailsScreen extends StatefulWidget {
  final int scheduleId;

  const FerryDetailsScreen({Key? key, required this.scheduleId})
    : super(key: key);

  @override
  State<FerryDetailsScreen> createState() => _FerryDetailsScreenState();
}

class _FerryDetailsScreenState extends State<FerryDetailsScreen> {
  int _passengerCount = 1;
  bool _hasVehicle = false;

  @override
  void initState() {
    super.initState();
    // Use addPostFrameCallback to ensure this runs after the first build is complete
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadScheduleDetail();
    });
  }

  Future<void> _loadScheduleDetail() async {
    if (!mounted) return;
    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
    await ferryProvider.fetchScheduleDetail(widget.scheduleId);
  }

  void _updatePassengerCount(int count) {
    if (count >= 1 && count <= 50) {
      setState(() {
        _passengerCount = count;
      });
    }
  }

  void _proceedToBooking() {
    print("BUTTON PRESSED: _proceedToBooking called");

    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );
    final schedule = ferryProvider.selectedSchedule;

    if (schedule == null) {
      print("ERROR: Schedule is null");
      return;
    }

    try {
      // Set schedule ID
      bookingProvider.setScheduleId(schedule.id);

      // Tambahkan jumlah penumpang (tanpa detail)
      bookingProvider.clearPassengers();
      for (int i = 0; i < _passengerCount; i++) {
        bookingProvider.addPassenger({}); // Kosong, tanpa detail
      }

      if (_hasVehicle) {
        // Jika ada kendaraan, buka layar detail kendaraan
        Navigator.of(context).pushNamed(
          AppRoutes.vehicleDetails,
          arguments: {'scheduleId': schedule.id},
        );
      } else {
        // Jika tidak ada kendaraan, langsung buat booking
        _createBookingDirectly();
      }
    } catch (e) {
      print("NAVIGATION ERROR: $e");
      // Show an error dialog for better visibility
      showDialog(
        context: context,
        builder:
            (ctx) => AlertDialog(
              title: Text("Navigation Error"),
              content: Text("Failed to navigate: $e"),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(ctx).pop(),
                  child: Text("OK"),
                ),
              ],
            ),
      );
    }
  }

  Future<void> _createBookingDirectly() async {
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );

    // Tampilkan loading
    showDialog(
      context: context,
      barrierDismissible: false,
      builder:
          (ctx) => const AlertDialog(
            content: LoadingIndicator(message: 'Membuat pemesanan...'),
          ),
    );

    try {
      // Berikan context ke fungsi createBooking
      final success = await bookingProvider.createBooking(context);

      // Tutup dialog loading
      Navigator.of(context, rootNavigator: true).pop();

      if (success) {
        // Lanjut ke halaman detail booking atau konfirmasi
        Navigator.of(context).pushReplacementNamed(
          AppRoutes.bookingConfirmation,
          arguments: {'bookingId': bookingProvider.currentBooking?.id},
        );
      } else {
        // Tampilkan error
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              bookingProvider.bookingError ?? 'Gagal membuat pemesanan',
            ),
          ),
        );
      }
    } catch (e) {
      // Tutup dialog loading jika terjadi error
      Navigator.of(context, rootNavigator: true).pop();

      // Tampilkan error
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Error: ${e.toString()}')));
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
      appBar: AppBar(title: const Text('Ferry Details')),
      body: Consumer<FerryProvider>(
        builder: (context, ferryProvider, _) {
          if (ferryProvider.isLoadingScheduleDetail) {
            return const Center(
              child: LoadingIndicator(message: 'Loading details...'),
            );
          }

          final schedule = ferryProvider.selectedSchedule;

          if (schedule == null) {
            return const Center(child: Text('Schedule not found'));
          }

          final hasFerry = schedule.ferry != null;
          final hasRoute = schedule.route != null;

          return SingleChildScrollView(
            padding: const EdgeInsets.all(AppTheme.paddingMedium),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Route and time info card
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
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Route
                      if (hasRoute)
                        Text(
                          schedule.route!.routeName,
                          style: const TextStyle(
                            fontSize: AppTheme.fontSizeLarge,
                            fontWeight: FontWeight.bold,
                          ),
                        ),

                      const SizedBox(height: AppTheme.paddingMedium),

                      // Departure date and time
                      Row(
                        children: [
                          const Icon(Icons.calendar_today, size: 18),
                          const SizedBox(width: AppTheme.paddingSmall),
                          Text(
                            DateFormat(
                              'EEE, dd MMM yyyy',
                            ).format(schedule.departureTime),
                            style: const TextStyle(
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppTheme.paddingSmall),

                      Row(
                        children: [
                          const Icon(Icons.access_time, size: 18),
                          const SizedBox(width: AppTheme.paddingSmall),
                          Text(
                            'Departure Time: ${schedule.formattedDepartureTime}',
                            style: const TextStyle(
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),

                      if (hasRoute) ...[
                        const SizedBox(height: AppTheme.paddingSmall),
                        Row(
                          children: [
                            const Icon(Icons.timelapse, size: 18),
                            const SizedBox(width: AppTheme.paddingSmall),
                            Text(
                              'Duration: ${schedule.route!.formattedDuration}',
                              style: const TextStyle(
                                fontSize: AppTheme.fontSizeRegular,
                              ),
                            ),
                          ],
                        ),
                      ],

                      const SizedBox(height: AppTheme.paddingMedium),

                      // Status indicator
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppTheme.paddingRegular,
                          vertical: AppTheme.paddingXSmall,
                        ),
                        decoration: BoxDecoration(
                          color:
                              schedule.isAvailable
                                  ? Colors.green.withOpacity(0.1)
                                  : Colors.red.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(
                            AppTheme.borderRadiusRound,
                          ),
                        ),
                        child: Text(
                          schedule.statusText,
                          style: TextStyle(
                            color:
                                schedule.isAvailable
                                    ? Colors.green
                                    : Colors.red,
                            fontWeight: FontWeight.w600,
                            fontSize: AppTheme.fontSizeSmall,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Ferry information
                if (hasFerry) ...[
                  Text(
                    'Ferry Information',
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
                      borderRadius: BorderRadius.circular(
                        AppTheme.borderRadiusMedium,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.05),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Ferry name and type
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(
                                AppTheme.paddingSmall,
                              ),
                              decoration: BoxDecoration(
                                color: AppTheme.primaryColor.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(
                                  AppTheme.borderRadiusRegular,
                                ),
                              ),
                              child: const Icon(
                                Icons.directions_boat,
                                color: AppTheme.primaryColor,
                                size: 24,
                              ),
                            ),
                            const SizedBox(width: AppTheme.paddingMedium),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    schedule.ferry!.name,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: AppTheme.fontSizeMedium,
                                    ),
                                  ),
                                  Text(
                                    'Type: ${schedule.ferry!.type}',
                                    style: TextStyle(
                                      color: theme.textTheme.bodyMedium?.color,
                                      fontSize: AppTheme.fontSizeRegular,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: AppTheme.paddingMedium),
                        const Divider(),
                        const SizedBox(height: AppTheme.paddingMedium),

                        // Capacity info
                        Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Passenger Capacity',
                                    style: TextStyle(
                                      color: theme.textTheme.bodyMedium?.color,
                                      fontSize: AppTheme.fontSizeSmall,
                                    ),
                                  ),
                                  Row(
                                    children: [
                                      Text(
                                        '${schedule.availableSeats}',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: AppTheme.fontSizeRegular,
                                        ),
                                      ),
                                      Text(
                                        ' / ${schedule.ferry!.capacity} available',
                                        style: TextStyle(
                                          color:
                                              theme.textTheme.bodyMedium?.color,
                                          fontSize: AppTheme.fontSizeRegular,
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
                                    'Vehicle Capacity',
                                    style: TextStyle(
                                      color: theme.textTheme.bodyMedium?.color,
                                      fontSize: AppTheme.fontSizeSmall,
                                    ),
                                  ),
                                  if (schedule.ferry!.carCapacity > 0)
                                    Text(
                                      'Cars: ${schedule.availableCars} / ${schedule.ferry!.carCapacity}',
                                      style: const TextStyle(
                                        fontSize: AppTheme.fontSizeRegular,
                                      ),
                                    ),
                                  if (schedule.ferry!.motorcycleCapacity > 0)
                                    Text(
                                      'Motorcycles: ${schedule.availableMotorcycles} / ${schedule.ferry!.motorcycleCapacity}',
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
                    ),
                  ),

                  const SizedBox(height: AppTheme.paddingLarge),
                ],

                // Booking options
                Text(
                  'Booking Options',
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
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusMedium,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Passenger count
                      Text(
                        'Number of Passengers',
                        style: TextStyle(
                          fontWeight: FontWeight.w500,
                          color: theme.textTheme.bodyLarge?.color,
                        ),
                      ),
                      const SizedBox(height: AppTheme.paddingSmall),
                      Row(
                        children: [
                          InkWell(
                            onTap:
                                () =>
                                    _updatePassengerCount(_passengerCount - 1),
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusRegular,
                            ),
                            child: Container(
                              width: 36,
                              height: 36,
                              decoration: BoxDecoration(
                                border: Border.all(color: theme.dividerColor),
                                borderRadius: BorderRadius.circular(
                                  AppTheme.borderRadiusRegular,
                                ),
                              ),
                              child: Icon(
                                Icons.remove,
                                color:
                                    _passengerCount > 1
                                        ? theme.textTheme.bodyLarge?.color
                                        : theme.disabledColor,
                              ),
                            ),
                          ),
                          Container(
                            width: 50,
                            alignment: Alignment.center,
                            child: Text(
                              '$_passengerCount',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: AppTheme.fontSizeLarge,
                              ),
                            ),
                          ),
                          InkWell(
                            onTap:
                                () =>
                                    _updatePassengerCount(_passengerCount + 1),
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusRegular,
                            ),
                            child: Container(
                              width: 36,
                              height: 36,
                              decoration: BoxDecoration(
                                border: Border.all(color: theme.dividerColor),
                                borderRadius: BorderRadius.circular(
                                  AppTheme.borderRadiusRegular,
                                ),
                              ),
                              child: const Icon(Icons.add),
                            ),
                          ),
                          const Spacer(),
                          Text(
                            currencyFormat.format(
                              schedule.finalPrice * _passengerCount,
                            ),
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: AppTheme.fontSizeLarge,
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: AppTheme.paddingMedium),
                      const Divider(),
                      const SizedBox(height: AppTheme.paddingMedium),

                      // Vehicle option
                      if (hasFerry &&
                          (schedule.ferry!.carCapacity > 0 ||
                              schedule.ferry!.motorcycleCapacity > 0 ||
                              schedule.ferry!.busCapacity > 0 ||
                              schedule.ferry!.truckCapacity > 0)) ...[
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                'Add Vehicle',
                                style: TextStyle(
                                  fontWeight: FontWeight.w500,
                                  color: theme.textTheme.bodyLarge?.color,
                                ),
                              ),
                            ),
                            Switch(
                              value: _hasVehicle,
                              onChanged: (value) {
                                setState(() {
                                  _hasVehicle = value;
                                });
                              },
                              activeColor: AppTheme.primaryColor,
                            ),
                          ],
                        ),
                        if (_hasVehicle) ...[
                          const SizedBox(height: AppTheme.paddingSmall),
                          Text(
                            'You\'ll be asked for vehicle details in the next step',
                            style: TextStyle(
                              color: theme.textTheme.bodyMedium?.color,
                              fontSize: AppTheme.fontSizeSmall,
                            ),
                          ),
                        ],

                        const SizedBox(height: AppTheme.paddingMedium),
                        const Divider(),
                        const SizedBox(height: AppTheme.paddingMedium),
                      ],

                      // Price breakdown
                      Text(
                        'Price Breakdown',
                        style: TextStyle(
                          fontWeight: FontWeight.w500,
                          color: theme.textTheme.bodyLarge?.color,
                        ),
                      ),
                      const SizedBox(height: AppTheme.paddingSmall),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Passenger Fare',
                            style: TextStyle(
                              color: theme.textTheme.bodyMedium?.color,
                            ),
                          ),
                          Text(
                            '${currencyFormat.format(schedule.finalPrice)} x $_passengerCount',
                            style: const TextStyle(fontWeight: FontWeight.w500),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppTheme.paddingXSmall),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Vehicle Fare',
                            style: TextStyle(
                              color: theme.textTheme.bodyMedium?.color,
                            ),
                          ),
                          Text(
                            _hasVehicle ? 'To be calculated' : 'Not selected',
                            style: const TextStyle(fontWeight: FontWeight.w500),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppTheme.paddingSmall),
                      const Divider(thickness: 1),
                      const SizedBox(height: AppTheme.paddingSmall),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Total',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: AppTheme.fontSizeMedium,
                            ),
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                currencyFormat.format(
                                  schedule.finalPrice * _passengerCount,
                                ),
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: AppTheme.fontSizeMedium,
                                ),
                              ),
                              if (_hasVehicle)
                                Text(
                                  '+ vehicle fare',
                                  style: TextStyle(
                                    color: theme.textTheme.bodyMedium?.color,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
      bottomNavigationBar: Consumer<FerryProvider>(
        builder: (context, ferryProvider, _) {
          final schedule = ferryProvider.selectedSchedule;

          if (schedule == null) {
            return const SizedBox.shrink();
          }

          return Container(
            padding: const EdgeInsets.all(AppTheme.paddingMedium),
            decoration: BoxDecoration(
              color: theme.cardColor,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 10,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: ElevatedButton(
              onPressed: () {
                print("DIRECT BUTTON PRESS");
                final bookingProvider = Provider.of<BookingProvider>(
                  context,
                  listen: false,
                );

                // Set schedule ID
                bookingProvider.setScheduleId(schedule.id);

                // Tambahkan jumlah penumpang (tanpa detail)
                bookingProvider.clearPassengers();
                for (int i = 0; i < _passengerCount; i++) {
                  bookingProvider.addPassenger({}); // Kosong, tanpa detail
                }

                if (_hasVehicle) {
                  // Jika ada kendaraan, buka layar detail kendaraan
                  Navigator.of(context).pushNamed(
                    AppRoutes.vehicleDetails,
                    arguments: {'scheduleId': schedule.id},
                  );
                } else {
                  // Jika tidak ada kendaraan, langsung buat booking
                  _createBookingDirectly();
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                minimumSize: Size(double.infinity, 50),
              ),
              child: Text("Buat Pemesanan"),
            ),
          );
        },
      ),
    );
  }
}
