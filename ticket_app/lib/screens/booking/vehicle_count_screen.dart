import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/common/custom_button.dart';

class VehicleCountScreen extends StatefulWidget {
  final int scheduleId;
  final int ticketCount;

  const VehicleCountScreen({
    Key? key,
    required this.scheduleId,
    required this.ticketCount,
  }) : super(key: key);

  @override
  State<VehicleCountScreen> createState() => _VehicleCountScreenState();
}

class _VehicleCountScreenState extends State<VehicleCountScreen> {
  List<Map<String, dynamic>> _vehicles = [];
  final _formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );
    bookingProvider.clearVehicles();

    // Tambah satu kendaraan default
    _vehicles.add({'type': 'car', 'license_plate': ''});
  }

  void _addVehicle() {
    setState(() {
      if (_vehicles.length < 3) {
        // Batasi maksimal 3 kendaraan
        _vehicles.add({'type': 'car', 'license_plate': ''});
      }
    });
  }

  void _removeVehicle(int index) {
    setState(() {
      if (_vehicles.length > 1) {
        _vehicles.removeAt(index);
      }
    });
  }

  void _updateVehicleType(int index, String type) {
    setState(() {
      _vehicles[index]['type'] = type;
    });
  }

  void _updateLicensePlate(int index, String licensePlate) {
    setState(() {
      _vehicles[index]['license_plate'] = licensePlate;
    });
  }

  void _continue() {
    if (_formKey.currentState?.validate() ?? false) {
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );

      // Tambahkan kendaraan ke provider
      for (var vehicle in _vehicles) {
        bookingProvider.addVehicle(vehicle);
      }

      // Tampilkan loading dialog
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => const Center(child: CircularProgressIndicator()),
      );

      // Berikan context ke createBooking()
      bookingProvider
          .createBooking(context)
          .then((success) {
            // Tutup dialog loading
            Navigator.pop(context);

            if (success && bookingProvider.currentBooking != null) {
              Navigator.pushNamed(
                context,
                AppRoutes.payment,
                arguments: {
                  'bookingId': bookingProvider.currentBooking!.id,
                  'totalAmount': bookingProvider.currentBooking!.totalAmount,
                },
              );
            } else {
              // Tampilkan pesan error
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    bookingProvider.bookingError ?? 'Gagal membuat pemesanan',
                  ),
                  backgroundColor: Colors.red,
                ),
              );
            }
          })
          .catchError((error) {
            // Tutup dialog loading jika masih terbuka
            if (Navigator.canPop(context)) {
              Navigator.pop(context);
            }

            // Tampilkan error
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Error: ${error.toString()}'),
                backgroundColor: Colors.red,
              ),
            );
          });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detail Kendaraan')),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppTheme.paddingMedium),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Tambahkan detail kendaraan',
                style: TextStyle(
                  fontSize: AppTheme.fontSizeLarge,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: AppTheme.paddingMedium),

              for (int i = 0; i < _vehicles.length; i++) _buildVehicleCard(i),

              const SizedBox(height: AppTheme.paddingMedium),

              if (_vehicles.length < 3)
                TextButton.icon(
                  onPressed: _addVehicle,
                  icon: const Icon(Icons.add_circle_outline),
                  label: const Text('Tambah Kendaraan Lain'),
                  style: TextButton.styleFrom(
                    foregroundColor: Theme.of(context).primaryColor,
                  ),
                ),

              const SizedBox(height: AppTheme.paddingLarge),

              CustomButton(
                text: 'Lanjut ke Pembayaran',
                onPressed: _continue,
                type: ButtonType.primary,
                isFullWidth: true,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildVehicleCard(int index) {
    return Card(
      margin: const EdgeInsets.only(bottom: AppTheme.paddingMedium),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
      ),
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.paddingMedium),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Kendaraan ${index + 1}',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeMedium,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                if (_vehicles.length > 1)
                  IconButton(
                    onPressed: () => _removeVehicle(index),
                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                    constraints: const BoxConstraints(),
                    padding: const EdgeInsets.all(8),
                  ),
              ],
            ),
            const SizedBox(height: AppTheme.paddingMedium),

            // Tipe Kendaraan
            DropdownButtonFormField<String>(
              value: _vehicles[index]['type'],
              decoration: const InputDecoration(
                labelText: 'Tipe Kendaraan',
                border: OutlineInputBorder(),
              ),
              items: const [
                DropdownMenuItem(value: 'motorcycle', child: Text('Motor')),
                DropdownMenuItem(value: 'car', child: Text('Mobil')),
                DropdownMenuItem(value: 'truck', child: Text('Truk')),
                DropdownMenuItem(value: 'bus', child: Text('Bus')),
              ],
              onChanged: (value) {
                if (value != null) {
                  _updateVehicleType(index, value);
                }
              },
            ),

            const SizedBox(height: AppTheme.paddingRegular),

            // Nomor Polisi
            TextFormField(
              initialValue: _vehicles[index]['license_plate'],
              decoration: const InputDecoration(
                labelText: 'Nomor Polisi',
                border: OutlineInputBorder(),
                hintText: 'Contoh: B 1234 XYZ',
              ),
              textCapitalization: TextCapitalization.characters,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Nomor polisi wajib diisi';
                }
                return null;
              },
              onChanged: (value) => _updateLicensePlate(index, value),
            ),
          ],
        ),
      ),
    );
  }
}
