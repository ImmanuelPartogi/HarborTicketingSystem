import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../providers/ferry_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/custom_text_field.dart';
import '../../widgets/common/loading_indicator.dart';

class VehicleDetailsScreen extends StatefulWidget {
  final int scheduleId;
  final List<int>? passengerIds;

  const VehicleDetailsScreen({
    Key? key,
    required this.scheduleId,
    this.passengerIds,
  }) : super(key: key);

  @override
  State<VehicleDetailsScreen> createState() => _VehicleDetailsScreenState();
}

class _VehicleDetailsScreenState extends State<VehicleDetailsScreen> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  String _selectedVehicleType = 'car';
  final TextEditingController _licensePlateController = TextEditingController();
  final TextEditingController _brandController = TextEditingController();
  final TextEditingController _modelController = TextEditingController();
  final TextEditingController _weightController = TextEditingController();

  bool _saveVehicleInfo = true;
  bool _isLoading = false;
  List<Map<String, dynamic>> _savedVehicles = [];

  // Prices for different vehicle types
  double _carPrice = 0;
  double _motorcyclePrice = 0;
  double _busPrice = 0;
  double _truckPrice = 0;

  @override
  void initState() {
    super.initState();
    _loadSavedVehicles();
    _loadVehiclePrices();
  }

  @override
  void dispose() {
    _licensePlateController.dispose();
    _brandController.dispose();
    _modelController.dispose();
    _weightController.dispose();
    super.dispose();
  }

  Future<void> _loadSavedVehicles() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );
      final savedVehicles = await bookingProvider.loadSavedVehicles();

      setState(() {
        _savedVehicles = savedVehicles;
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _loadVehiclePrices() async {
    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);

    try {
      // Make sure we have the schedule details
      if (ferryProvider.selectedSchedule == null) {
        await ferryProvider.fetchScheduleDetail(widget.scheduleId);
      }

      // Get vehicle prices from route
      final schedule = ferryProvider.selectedSchedule;
      if (schedule != null && schedule.route != null) {
        setState(() {
          _carPrice = schedule.route!.carPrice;
          _motorcyclePrice = schedule.route!.motorcyclePrice;
          _busPrice = schedule.route!.busPrice;
          _truckPrice = schedule.route!.truckPrice;
        });
      }
    } catch (e) {
      print('Error loading vehicle prices: $e');
    }
  }

  double _getSelectedVehiclePrice() {
    switch (_selectedVehicleType) {
      case 'car':
        return _carPrice;
      case 'motorcycle':
        return _motorcyclePrice;
      case 'bus':
        return _busPrice;
      case 'truck':
        return _truckPrice;
      default:
        return 0;
    }
  }

  void _useSavedVehicle(Map<String, dynamic> vehicle) {
    setState(() {
      _selectedVehicleType = vehicle['type'] ?? 'car';
      _licensePlateController.text = vehicle['license_plate'] ?? '';
      _brandController.text = vehicle['brand'] ?? '';
      _modelController.text = vehicle['model'] ?? '';
      _weightController.text = vehicle['weight']?.toString() ?? '';
    });

    Navigator.pop(context); // Close the bottom sheet
  }

  void _showSavedVehiclesBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(AppTheme.borderRadiusLarge),
          topRight: Radius.circular(AppTheme.borderRadiusLarge),
        ),
      ),
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(AppTheme.paddingMedium),
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(context).size.height * 0.7,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Select Saved Vehicle',
                style: TextStyle(
                  fontSize: AppTheme.fontSizeLarge,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: AppTheme.paddingMedium),

              Expanded(
                child:
                    _savedVehicles.isEmpty
                        ? const Center(child: Text('No saved vehicles found'))
                        : ListView.builder(
                          itemCount: _savedVehicles.length,
                          itemBuilder: (context, index) {
                            final vehicle = _savedVehicles[index];
                            return ListTile(
                              leading: Icon(
                                vehicle['type'] == 'car'
                                    ? Icons.directions_car
                                    : vehicle['type'] == 'motorcycle'
                                    ? Icons.two_wheeler
                                    : vehicle['type'] == 'bus'
                                    ? Icons.directions_bus
                                    : Icons.local_shipping,
                              ),
                              title: Text(
                                vehicle['license_plate'] ?? 'Unknown',
                              ),
                              subtitle: Text(
                                '${vehicle['brand'] ?? ''} ${vehicle['model'] ?? ''}',
                              ),
                              onTap: () => _useSavedVehicle(vehicle),
                              trailing: const Icon(Icons.check_circle_outline),
                            );
                          },
                        ),
              ),

              const SizedBox(height: AppTheme.paddingMedium),

              Center(
                child: TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Cancel'),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _proceedToBooking() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      // Prepare vehicle data
      final vehicleData = {
        'type': _selectedVehicleType.toUpperCase(), // Konversi ke uppercase
        'license_plate': _licensePlateController.text,
        'brand': _brandController.text,
        'model': _modelController.text,
        'weight':
            _weightController.text.isNotEmpty
                ? double.parse(_weightController.text)
                : null,
        'price': _getSelectedVehiclePrice(),
        'save_info': _saveVehicleInfo,
      };

      // Add vehicle to booking provider
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );
      bookingProvider.clearVehicles();
      bookingProvider.addVehicle(vehicleData);

      // Create booking
      final success = await bookingProvider.createBooking();

      if (success && mounted) {
        final currentBooking = bookingProvider.currentBooking;

        Navigator.pushNamed(
          context,
          AppRoutes.payment,
          arguments: {
            'bookingId': currentBooking!.id,
            'totalAmount': currentBooking.totalAmount,
          },
        );
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              bookingProvider.bookingError ?? 'Failed to create booking',
            ),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: ${e.toString()}'),
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
      appBar: AppBar(title: const Text('Vehicle Details')),
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: 'Processing booking...',
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppTheme.paddingMedium),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header with saved vehicles button
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Vehicle Information',
                      style: TextStyle(
                        fontSize: AppTheme.fontSizeMedium,
                        fontWeight: FontWeight.bold,
                        color: theme.textTheme.displaySmall?.color,
                      ),
                    ),
                    if (_savedVehicles.isNotEmpty)
                      TextButton.icon(
                        onPressed: _showSavedVehiclesBottomSheet,
                        icon: const Icon(Icons.directions_car, size: 18),
                        label: const Text('Use Saved'),
                      ),
                  ],
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Vehicle type
                Text(
                  'Vehicle Type',
                  style: TextStyle(
                    color: theme.textTheme.bodyLarge?.color,
                    fontSize: AppTheme.fontSizeRegular,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 8),

                Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: theme.dividerColor),
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusRegular,
                    ),
                  ),
                  child: Column(
                    children: [
                      // Car option
                      _buildVehicleTypeOption(
                        type: 'car',
                        title: 'Car',
                        subtitle: 'Sedan, SUV, etc.',
                        price: _carPrice,
                        icon: Icons.directions_car,
                      ),

                      const Divider(height: 1),

                      // Motorcycle option
                      _buildVehicleTypeOption(
                        type: 'motorcycle',
                        title: 'Motorcycle',
                        subtitle: 'All types',
                        price: _motorcyclePrice,
                        icon: Icons.two_wheeler,
                      ),

                      const Divider(height: 1),

                      // Bus option
                      _buildVehicleTypeOption(
                        type: 'bus',
                        title: 'Bus',
                        subtitle: 'Minibus, Standard Bus',
                        price: _busPrice,
                        icon: Icons.directions_bus,
                      ),

                      const Divider(height: 1),

                      // Truck option
                      _buildVehicleTypeOption(
                        type: 'truck',
                        title: 'Truck',
                        subtitle: 'Small truck, pickup, etc.',
                        price: _truckPrice,
                        icon: Icons.local_shipping,
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Vehicle details
                Text(
                  'Vehicle Details',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeMedium,
                    fontWeight: FontWeight.bold,
                    color: theme.textTheme.displaySmall?.color,
                  ),
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // License plate
                CustomTextField(
                  label: 'License Plate',
                  hintText: 'Enter license plate number',
                  controller: _licensePlateController,
                  isRequired: true,
                  textCapitalization: TextCapitalization.characters,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'License plate is required';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Brand and model
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Brand
                    Expanded(
                      child: CustomTextField(
                        label: 'Brand',
                        hintText: 'e.g., Toyota',
                        controller: _brandController,
                        textCapitalization: TextCapitalization.words,
                      ),
                    ),

                    const SizedBox(width: AppTheme.paddingMedium),

                    // Model
                    Expanded(
                      child: CustomTextField(
                        label: 'Model',
                        hintText: 'e.g., Avanza',
                        controller: _modelController,
                        textCapitalization: TextCapitalization.words,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Weight (for trucks)
                if (_selectedVehicleType == 'truck')
                  CustomTextField(
                    label: 'Weight (kg)',
                    hintText: 'Enter vehicle weight in kg',
                    controller: _weightController,
                    keyboardType: TextInputType.number,
                    inputFormatters: [
                      FilteringTextInputFormatter.allow(RegExp(r'[0-9.]')),
                    ],
                    validator: (value) {
                      if (_selectedVehicleType == 'truck' &&
                          (value == null || value.isEmpty)) {
                        return 'Weight is required for trucks';
                      }
                      return null;
                    },
                  ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Save vehicle info
                Row(
                  children: [
                    Checkbox(
                      value: _saveVehicleInfo,
                      onChanged: (value) {
                        setState(() {
                          _saveVehicleInfo = value ?? false;
                        });
                      },
                      activeColor: AppTheme.primaryColor,
                    ),
                    Expanded(
                      child: GestureDetector(
                        onTap: () {
                          setState(() {
                            _saveVehicleInfo = !_saveVehicleInfo;
                          });
                        },
                        child: Text(
                          'Save vehicle information for future bookings',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeRegular,
                            color: theme.textTheme.bodyMedium?.color,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Summary
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
                        'Price Summary',
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeMedium,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: AppTheme.paddingRegular),
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
                            currencyFormat.format(_getSelectedVehiclePrice()),
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Continue button
                CustomButton(
                  text: 'Continue to Payment',
                  onPressed: _proceedToBooking,
                  type: ButtonType.primary,
                  isFullWidth: true,
                ),

                const SizedBox(height: AppTheme.paddingLarge),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildVehicleTypeOption({
    required String type,
    required String title,
    required String subtitle,
    required double price,
    required IconData icon,
  }) {
    final currencyFormat = NumberFormat.currency(
      locale: 'id',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    return RadioListTile<String>(
      value: type,
      groupValue: _selectedVehicleType,
      onChanged: (value) {
        setState(() {
          _selectedVehicleType = value!;
        });
      },
      title: Row(
        children: [
          Icon(icon),
          const SizedBox(width: AppTheme.paddingRegular),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.w500),
                ),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeSmall,
                    color: Theme.of(context).textTheme.bodyMedium?.color,
                  ),
                ),
              ],
            ),
          ),
          Text(
            currencyFormat.format(price),
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ],
      ),
      contentPadding: const EdgeInsets.symmetric(
        horizontal: AppTheme.paddingMedium,
        vertical: AppTheme.paddingSmall,
      ),
      activeColor: AppTheme.primaryColor,
    );
  }
}
