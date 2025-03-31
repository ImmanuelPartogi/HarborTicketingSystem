import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/ferry_provider.dart';

class SearchForm extends StatefulWidget {
  const SearchForm({Key? key}) : super(key: key);

  @override
  State<SearchForm> createState() => _SearchFormState();
}

class _SearchFormState extends State<SearchForm> {
  String? _selectedDeparturePort;
  String? _selectedArrivalPort;
  DateTime _selectedDate = DateTime.now();
  int _passengerCount = 1;
  bool _isSearchEnabled = false;
  bool _isLoading = false;
  bool _portsInitialized = false;

  List<String> _departurePorts = [];
  List<String> _arrivalPorts = [];

  @override
  void initState() {
    super.initState();

    // Pindahkan loading ke postFrameCallback untuk menghindari setState during build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _loadPorts();
      }
    });
  }

  Future<void> _loadPorts() async {
    if (!mounted || _isLoading || _portsInitialized) return;

    setState(() {
      _isLoading = true;
    });

    try {
      // Load routes untuk mendapatkan port data
      final ferryProvider = Provider.of<FerryProvider>(context, listen: false);

      // Periksa apakah routes sudah di-load
      if (ferryProvider.routes.isEmpty) {
        await ferryProvider.fetchRoutes();
      }

      // Set departure ports dari ferryProvider
      final departurePorts = ferryProvider.getUniqueDeparturePorts();

      if (departurePorts.isNotEmpty) {
        setState(() {
          _departurePorts = departurePorts;
          _selectedDeparturePort = departurePorts.first;

          // Update arrival ports berdasarkan departure port yang dipilih
          _updateArrivalPorts();
          _portsInitialized = true;
        });
      }
    } catch (e) {
      debugPrint('Error loading ports: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _updateArrivalPorts() {
    if (_selectedDeparturePort != null) {
      final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
      final arrivalPorts = ferryProvider.getUniqueArrivalPorts(
        _selectedDeparturePort!,
      );

      setState(() {
        _arrivalPorts = arrivalPorts;
        _selectedArrivalPort =
            arrivalPorts.isNotEmpty ? arrivalPorts.first : null;
      });
    }
  }

  void _checkSearchEnabled() {
    setState(() {
      _isSearchEnabled =
          _selectedDeparturePort != null &&
          _selectedArrivalPort != null &&
          _selectedDate != null;
    });
  }

  void _onDeparturePortChanged(String? port) {
    setState(() {
      _selectedDeparturePort = port;
      _selectedArrivalPort = null; // Reset arrival port when departure changes

      // Update available arrival ports
      if (port != null) {
        final ferryProvider = Provider.of<FerryProvider>(
          context,
          listen: false,
        );
        _arrivalPorts = ferryProvider.getUniqueArrivalPorts(port);
      } else {
        _arrivalPorts = [];
      }
    });

    _checkSearchEnabled();
  }

  void _onArrivalPortChanged(String? port) {
    setState(() {
      _selectedArrivalPort = port;
    });

    _checkSearchEnabled();
  }

  Future<void> _selectDate(BuildContext context) async {
    final pickedDate = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: AppTheme.primaryColor,
              onPrimary: Colors.white,
              onSurface: Theme.of(context).textTheme.bodyLarge!.color!,
            ),
          ),
          child: child!,
        );
      },
    );

    if (pickedDate != null && pickedDate != _selectedDate) {
      setState(() {
        _selectedDate = pickedDate;
      });

      _checkSearchEnabled();
    }
  }

  void _updatePassengerCount(int count) {
    if (count >= 1 && count <= 50) {
      setState(() {
        _passengerCount = count;
      });
    }
  }

  void _search() {
    if (!_isSearchEnabled) return;

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

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: AppTheme.paddingMedium),
      padding: const EdgeInsets.all(AppTheme.paddingMedium),
      decoration: BoxDecoration(
        color: theme.cardColor,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMedium),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Find Your Ferry',
            style: TextStyle(
              fontSize: AppTheme.fontSizeMedium,
              fontWeight: FontWeight.bold,
              color: theme.textTheme.displaySmall?.color,
            ),
          ),
          const SizedBox(height: AppTheme.paddingRegular),

          // Departure Port dropdown
          DropdownButtonFormField<String>(
            value: _selectedDeparturePort,
            decoration: InputDecoration(
              labelText: 'Departure Port',
              prefixIcon: Icon(Icons.location_on, color: theme.primaryColor),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(
                  AppTheme.borderRadiusRegular,
                ),
              ),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: AppTheme.paddingRegular,
                vertical: AppTheme.paddingRegular,
              ),
            ),
            hint: const Text('Select Departure Port'),
            isExpanded: true,
            items:
                _departurePorts.map((port) {
                  return DropdownMenuItem<String>(
                    value: port,
                    child: Text(
                      port,
                      style: const TextStyle(
                        fontSize: AppTheme.fontSizeRegular,
                      ),
                    ),
                  );
                }).toList(),
            onChanged: _isLoading ? null : _onDeparturePortChanged,
          ),

          const SizedBox(height: AppTheme.paddingMedium),

          // Arrival Port dropdown
          DropdownButtonFormField<String>(
            value: _selectedArrivalPort,
            decoration: InputDecoration(
              labelText: 'Arrival Port',
              prefixIcon: Icon(Icons.location_on, color: theme.primaryColor),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(
                  AppTheme.borderRadiusRegular,
                ),
              ),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: AppTheme.paddingRegular,
                vertical: AppTheme.paddingRegular,
              ),
            ),
            hint: const Text('Select Arrival Port'),
            isExpanded: true,
            items:
                _arrivalPorts.map((port) {
                  return DropdownMenuItem<String>(
                    value: port,
                    child: Text(
                      port,
                      style: const TextStyle(
                        fontSize: AppTheme.fontSizeRegular,
                      ),
                    ),
                  );
                }).toList(),
            onChanged:
                _selectedDeparturePort == null || _isLoading
                    ? null
                    : _onArrivalPortChanged,
          ),

          const SizedBox(height: AppTheme.paddingMedium),

          // Date and passenger count in a row
          Row(
            children: [
              // Date selector
              Expanded(
                flex: 3,
                child: InkWell(
                  onTap: () => _selectDate(context),
                  borderRadius: BorderRadius.circular(
                    AppTheme.borderRadiusRegular,
                  ),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingRegular,
                      vertical: AppTheme.paddingSmall + 2,
                    ),
                    decoration: BoxDecoration(
                      border: Border.all(color: theme.dividerColor),
                      borderRadius: BorderRadius.circular(
                        AppTheme.borderRadiusRegular,
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.calendar_today,
                          color: theme.primaryColor,
                          size: 20,
                        ),
                        const SizedBox(width: AppTheme.paddingSmall),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                'Departure Date',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeSmall,
                                  color: theme.textTheme.bodyMedium?.color
                                      ?.withOpacity(0.7),
                                ),
                              ),
                              Text(
                                DateFormat(
                                  'EEE, dd MMM yyyy',
                                ).format(_selectedDate),
                                style: const TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),

              const SizedBox(width: AppTheme.paddingMedium),

              // Passenger count
              Expanded(
                flex: 2,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: AppTheme.paddingRegular,
                    vertical: AppTheme.paddingSmall,
                  ),
                  decoration: BoxDecoration(
                    border: Border.all(color: theme.dividerColor),
                    borderRadius: BorderRadius.circular(
                      AppTheme.borderRadiusRegular,
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Passengers',
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeSmall,
                          color: theme.textTheme.bodyMedium?.color?.withOpacity(
                            0.7,
                          ),
                        ),
                      ),
                      Row(
                        children: [
                          InkWell(
                            onTap:
                                () =>
                                    _updatePassengerCount(_passengerCount - 1),
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusSmall,
                            ),
                            child: Icon(
                              Icons.remove_circle_outline,
                              color:
                                  _passengerCount > 1
                                      ? theme.primaryColor
                                      : theme.disabledColor,
                              size: 20,
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.symmetric(
                              horizontal: AppTheme.paddingSmall,
                            ),
                            child: Text(
                              '$_passengerCount',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: AppTheme.fontSizeRegular,
                              ),
                            ),
                          ),
                          InkWell(
                            onTap:
                                () =>
                                    _updatePassengerCount(_passengerCount + 1),
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusSmall,
                            ),
                            child: Icon(
                              Icons.add_circle_outline,
                              color:
                                  _passengerCount < 50
                                      ? theme.primaryColor
                                      : theme.disabledColor,
                              size: 20,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: AppTheme.paddingMedium),

          // Search button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _isSearchEnabled ? _search : null,
              style: ElevatedButton.styleFrom(
                backgroundColor: theme.primaryColor,
                foregroundColor: Colors.white,
                disabledBackgroundColor: theme.disabledColor.withOpacity(0.3),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(
                    AppTheme.borderRadiusRegular,
                  ),
                ),
                padding: const EdgeInsets.symmetric(
                  vertical: AppTheme.paddingRegular,
                ),
              ),
              child: const Text(
                'Search Schedules',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: AppTheme.fontSizeRegular,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
