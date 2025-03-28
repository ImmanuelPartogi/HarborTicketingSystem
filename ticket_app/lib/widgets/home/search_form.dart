import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../providers/ferry_provider.dart';
import '../../config/routes.dart';
import '../common/custom_button.dart';

class SearchForm extends StatefulWidget {
  const SearchForm({Key? key}) : super(key: key);

  @override
  State<SearchForm> createState() => _SearchFormState();
}

class _SearchFormState extends State<SearchForm> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  String? _selectedDeparturePort;
  String? _selectedArrivalPort;
  DateTime _selectedDate = DateTime.now();
  int _passengerCount = 1;
  bool _isLoadingDeparturePorts = false; // Loading state for departure ports
  bool _isLoadingArrivalPorts =
      false; // Loading state for arrival ports when departure port is selected
  bool _isDisposed = false;

  @override
  void initState() {
    super.initState();
    // Gunakan Future.microtask untuk menunggu build pertama selesai
    Future.microtask(() {
      if (!mounted) return;
      _loadRoutes();
    });
  }

  @override
  void dispose() {
    _isDisposed = true;
    super.dispose();
  }

  Future<void> _loadRoutes() async {
    if (!mounted) return;

    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);

    // Update state hanya jika widget masih terpasang
    if (mounted) {
      setState(() {
        _isLoadingDeparturePorts = true;
      });
    }

    try {
      // Load all routes without filtering
      await ferryProvider.fetchRoutes(activeOnly: true);
    } catch (e) {
      print('Error loading routes: $e');
    }

    // Cek lagi apakah widget masih terpasang sebelum update state
    if (mounted) {
      setState(() {
        _isLoadingDeparturePorts = false;
      });
    }
  }

  // Method to load arrival ports when departure port is selected
  Future<void> _loadArrivalPorts(String departurePort) async {
    if (!mounted) return;

    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);

    // Show loading indicator for arrival ports
    if (mounted) {
      setState(() {
        _isLoadingArrivalPorts = true;
      });
    }

    try {
      // Call the fetchRoutes with the departurePort filter to get specific arrival ports
      // This leverages the existing fetchRoutes method in the FerryProvider
      await ferryProvider.fetchRoutes(
        activeOnly: true,
        departurePort: departurePort,
      );
    } catch (e) {
      print('Error loading arrival ports: $e');
    }

    // Hide loading indicator
    if (mounted) {
      setState(() {
        _isLoadingArrivalPorts = false;
      });
    }
  }

  List<String> _getDeparturePorts() {
    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
    return ferryProvider.getUniqueDeparturePorts();
  }

  List<String> _getArrivalPorts() {
    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
    if (_selectedDeparturePort == null) {
      return [];
    }

    // This uses the existing method in FerryProvider to get arrival ports for the selected departure port
    List<String> arrivalPorts = ferryProvider.getUniqueArrivalPorts(
      _selectedDeparturePort!,
    );

    // Sort the ports alphabetically for better user experience
    arrivalPorts.sort();
    return arrivalPorts;
  }

  Future<void> _selectDate(BuildContext context) async {
    final DateTime now = DateTime.now();
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: now,
      lastDate: DateTime(now.year + 1, now.month, now.day),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: AppTheme.primaryColor,
              onPrimary: Colors.white,
              surface: Colors.white,
              onSurface: Colors.black,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null && picked != _selectedDate && mounted) {
      setState(() {
        _selectedDate = picked;
      });
    }
  }

  void _updatePassengerCount(int count) {
    if (count >= 1 && count <= 50 && mounted) {
      setState(() {
        _passengerCount = count;
      });
    }
  }

  void _search() {
    if (_formKey.currentState!.validate()) {
      Navigator.pushNamed(
        context,
        AppRoutes.search,
        arguments: {
          'departurePort': _selectedDeparturePort,
          'arrivalPort': _selectedArrivalPort,
          'departureDate': _selectedDate,
          'passengerCount': _passengerCount,
        },
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final departurePorts = _getDeparturePorts();
    final arrivalPorts = _getArrivalPorts();

    return Container(
      margin: const EdgeInsets.all(AppTheme.paddingMedium),
      padding: const EdgeInsets.all(AppTheme.paddingMedium),
      decoration: BoxDecoration(
        color: theme.cardColor,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Find Ferry Tickets',
              style: TextStyle(
                fontSize: AppTheme.fontSizeLarge,
                fontWeight: FontWeight.bold,
                color: theme.textTheme.displaySmall?.color,
              ),
            ),
            const SizedBox(height: AppTheme.paddingMedium),

            // Departure Port Dropdown
            _buildDropdownField(
              label: 'Departure Port',
              hint: 'Select departure port',
              icon: Icons.location_on,
              value: _selectedDeparturePort,
              items:
                  departurePorts.map((port) {
                    return DropdownMenuItem<String>(
                      value: port,
                      child: Text(port),
                    );
                  }).toList(),
              onChanged: (value) {
                if (mounted) {
                  setState(() {
                    _selectedDeparturePort = value as String?;
                    _selectedArrivalPort =
                        null; // Reset arrival port when departure changes
                  });

                  // Load arrival ports when departure port changes
                  if (value != null) {
                    _loadArrivalPorts(value as String);
                  }
                }
              },
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please select departure port';
                }
                return null;
              },
              isLoading: _isLoadingDeparturePorts,
            ),

            const SizedBox(height: AppTheme.paddingMedium),

            // Arrival Port Dropdown
            _buildDropdownField(
              label: 'Arrival Port',
              hint:
                  _selectedDeparturePort == null
                      ? 'Select departure port first'
                      : 'Select arrival port',
              icon: Icons.location_on,
              value: _selectedArrivalPort,
              items:
                  arrivalPorts.map((port) {
                    return DropdownMenuItem<String>(
                      value: port,
                      child: Text(port),
                    );
                  }).toList(),
              onChanged:
                  _selectedDeparturePort == null
                      ? null
                      : (value) {
                        if (mounted) {
                          setState(() {
                            _selectedArrivalPort = value as String?;
                          });
                        }
                      },
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please select arrival port';
                }
                return null;
              },
              isLoading: _isLoadingArrivalPorts,
            ),

            const SizedBox(height: AppTheme.paddingMedium),

            // Date Picker
            _buildDateField(
              label: 'Departure Date',
              value: DateFormat('EEE, dd MMM yyyy').format(_selectedDate),
              icon: Icons.calendar_today,
              onTap: () => _selectDate(context),
            ),

            const SizedBox(height: AppTheme.paddingMedium),

            // Passenger Count
            _buildPassengerCountField(
              label: 'Passengers',
              value: _passengerCount,
              onDecrease: () => _updatePassengerCount(_passengerCount - 1),
              onIncrease: () => _updatePassengerCount(_passengerCount + 1),
            ),

            const SizedBox(height: AppTheme.paddingLarge),

            // Search Button
            CustomButton(
              text: 'Search Tickets',
              onPressed: _search,
              icon: Icons.search,
              type: ButtonType.primary,
              isFullWidth: true,
            ),
          ],
        ),
      ),
    );
  }

  // Modified to better handle the disabled state for arrival port
  Widget _buildDropdownField({
    required String label,
    required String hint,
    required IconData icon,
    required String? value,
    required List<DropdownMenuItem<String>> items,
    required void Function(dynamic)? onChanged,
    required String? Function(String?)? validator,
    bool isLoading = false,
  }) {
    final theme = Theme.of(context);
    final bool isDisabled = onChanged == null;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: theme.textTheme.bodyLarge?.color,
            fontSize: AppTheme.fontSizeRegular,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: 8),
        DropdownButtonFormField<String>(
          value: value,
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: TextStyle(
              color: isDisabled ? theme.disabledColor : theme.hintColor,
            ),
            prefixIcon: Icon(
              icon,
              color: isDisabled ? theme.disabledColor : null,
            ),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: AppTheme.paddingMedium,
              vertical: AppTheme.paddingRegular,
            ),
            filled: true,
            fillColor:
                isDisabled
                    ? theme.disabledColor.withOpacity(0.1)
                    : theme.cardColor,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              borderSide: BorderSide(
                color: isDisabled ? theme.disabledColor : theme.dividerColor,
              ),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              borderSide: BorderSide(
                color: isDisabled ? theme.disabledColor : theme.dividerColor,
              ),
            ),
          ),
          items: isLoading ? [] : items,
          onChanged: isLoading ? null : onChanged,
          validator: validator,
          isExpanded: true,
          icon:
              isLoading
                  ? SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation<Color>(
                        AppTheme.primaryColor,
                      ),
                    ),
                  )
                  : Icon(
                    Icons.arrow_drop_down,
                    color: isDisabled ? theme.disabledColor : null,
                  ),
          dropdownColor: theme.cardColor,
          style: TextStyle(
            color:
                isDisabled
                    ? theme.disabledColor
                    : theme.textTheme.bodyLarge?.color,
          ),
        ),
      ],
    );
  }

  Widget _buildDateField({
    required String label,
    required String value,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    final theme = Theme.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: theme.textTheme.bodyLarge?.color,
            fontSize: AppTheme.fontSizeRegular,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: 8),
        InkWell(
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.symmetric(
              horizontal: AppTheme.paddingMedium,
              vertical: AppTheme.paddingRegular,
            ),
            decoration: BoxDecoration(
              border: Border.all(color: theme.dividerColor),
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              color: theme.cardColor,
            ),
            child: Row(
              children: [
                Icon(icon, color: theme.hintColor),
                const SizedBox(width: AppTheme.paddingRegular),
                Text(
                  value,
                  style: TextStyle(
                    color: theme.textTheme.bodyLarge?.color,
                    fontSize: AppTheme.fontSizeRegular,
                  ),
                ),
                const Spacer(),
                Icon(Icons.arrow_drop_down, color: theme.hintColor),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPassengerCountField({
    required String label,
    required int value,
    required VoidCallback onDecrease,
    required VoidCallback onIncrease,
  }) {
    final theme = Theme.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: theme.textTheme.bodyLarge?.color,
            fontSize: AppTheme.fontSizeRegular,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(
            horizontal: AppTheme.paddingMedium,
            vertical: AppTheme.paddingRegular,
          ),
          decoration: BoxDecoration(
            border: Border.all(color: theme.dividerColor),
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
            color: theme.cardColor,
          ),
          child: Row(
            children: [
              Icon(Icons.people, color: theme.hintColor),
              const SizedBox(width: AppTheme.paddingRegular),
              Text(
                '$value ${value == 1 ? 'Passenger' : 'Passengers'}',
                style: TextStyle(
                  color: theme.textTheme.bodyLarge?.color,
                  fontSize: AppTheme.fontSizeRegular,
                ),
              ),
              const Spacer(),
              Row(
                children: [
                  InkWell(
                    onTap: value > 1 ? onDecrease : null,
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusSmall,
                    ),
                    child: Container(
                      width: 32,
                      height: 32,
                      decoration: BoxDecoration(
                        color:
                            value > 1
                                ? AppTheme.primaryColor
                                : theme.disabledColor,
                        borderRadius: BorderRadius.circular(
                          AppTheme.borderRadiusSmall,
                        ),
                      ),
                      child: const Icon(
                        Icons.remove,
                        color: Colors.white,
                        size: 18,
                      ),
                    ),
                  ),
                  Container(
                    width: 40,
                    alignment: Alignment.center,
                    child: Text(
                      '$value',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: AppTheme.fontSizeMedium,
                      ),
                    ),
                  ),
                  InkWell(
                    onTap: value < 50 ? onIncrease : null,
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusSmall,
                    ),
                    child: Container(
                      width: 32,
                      height: 32,
                      decoration: BoxDecoration(
                        color:
                            value < 50
                                ? AppTheme.primaryColor
                                : theme.disabledColor,
                        borderRadius: BorderRadius.circular(
                          AppTheme.borderRadiusSmall,
                        ),
                      ),
                      child: const Icon(
                        Icons.add,
                        color: Colors.white,
                        size: 18,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }
}
