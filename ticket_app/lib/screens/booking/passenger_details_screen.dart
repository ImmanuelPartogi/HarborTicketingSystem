import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'dart:convert';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';
import '../../providers/booking_provider.dart';
import '../../providers/ferry_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/custom_text_field.dart';
import '../../widgets/common/loading_indicator.dart';

class PassengerDetailsScreen extends StatefulWidget {
  final int scheduleId;
  final int passengerCount;
  final bool hasVehicle;

  const PassengerDetailsScreen({
    Key? key,
    required this.scheduleId,
    required this.passengerCount,
    this.hasVehicle = false,
  }) : super(key: key);

  @override
  _PassengerDetailsScreenState createState() => _PassengerDetailsScreenState();
}

class _PassengerDetailsScreenState extends State<PassengerDetailsScreen> {
  int _selectedPassengerCount = 1;

  @override
  void initState() {
    super.initState();
    _selectedPassengerCount = widget.passengerCount;
    // Set schedule ID
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<BookingProvider>(
        context,
        listen: false,
      ).setScheduleId(widget.scheduleId);
    });
  }

  void _continue() {
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );

    // Tambahkan jumlah penumpang (tanpa detail)
    bookingProvider.clearPassengers();
    for (int i = 0; i < _selectedPassengerCount; i++) {
      bookingProvider.addPassenger({}); // Kosong, tanpa detail
    }

    if (widget.hasVehicle) {
      Navigator.pushNamed(
        context,
        AppRoutes.vehicleDetails,
        arguments: {'scheduleId': widget.scheduleId},
      );
    } else {
      // Langsung buat booking
      _createBooking();
    }
  }

  Future<void> _createBooking() async {
    // Implementasi pembuatan booking
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Jumlah Penumpang')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Pilih jumlah penumpang yang akan naik ferry'),
            SizedBox(height: 20),

            // Counter untuk memilih jumlah penumpang
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                IconButton(
                  icon: Icon(Icons.remove_circle),
                  onPressed:
                      _selectedPassengerCount > 1
                          ? () {
                            setState(() {
                              _selectedPassengerCount--;
                            });
                          }
                          : null,
                ),
                SizedBox(width: 20),
                Text(
                  '$_selectedPassengerCount',
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                ),
                SizedBox(width: 20),
                IconButton(
                  icon: Icon(Icons.add_circle),
                  onPressed:
                      _selectedPassengerCount < 10
                          ? () {
                            setState(() {
                              _selectedPassengerCount++;
                            });
                          }
                          : null,
                ),
              ],
            ),

            SizedBox(height: 40),
            ElevatedButton(
              onPressed: _continue,
              child: Text('Lanjutkan'),
              style: ElevatedButton.styleFrom(
                minimumSize: Size(double.infinity, 50),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
