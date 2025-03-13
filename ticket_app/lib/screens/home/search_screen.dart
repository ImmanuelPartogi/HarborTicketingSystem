import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/ferry_provider.dart';
import '../../models/schedule_model.dart';
import '../../widgets/common/ferry_card.dart';
import '../../widgets/common/loading_indicator.dart';

class SearchScreen extends StatefulWidget {
  final String? departurePort;
  final String? arrivalPort;
  final DateTime? departureDate;
  final int? passengerCount;

  const SearchScreen({
    Key? key,
    this.departurePort,
    this.arrivalPort,
    this.departureDate,
    this.passengerCount,
  }) : super(key: key);

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  String _sortBy = 'departure_time_asc';
  bool _isSearching = false;
  DateTime? _selectedDate;
  String? _selectedDeparturePort;
  String? _selectedArrivalPort;
  int _passengerCount = 1;
  
  @override
  void initState() {
    super.initState();
    _selectedDate = widget.departureDate ?? DateTime.now();
    _selectedDeparturePort = widget.departurePort;
    _selectedArrivalPort = widget.arrivalPort;
    _passengerCount = widget.passengerCount ?? 1;
    
    _searchSchedules();
  }
  
  Future<void> _searchSchedules() async {
    if (_selectedDeparturePort == null || _selectedArrivalPort == null) {
      return;
    }
    
    setState(() {
      _isSearching = true;
    });
    
    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
    
    try {
      await ferryProvider.fetchSchedules(
        departurePort: _selectedDeparturePort!,
        arrivalPort: _selectedArrivalPort!,
        departureDate: _selectedDate ?? DateTime.now(),
      );
    } finally {
      setState(() {
        _isSearching = false;
      });
    }
  }
  
  void _showFilterDialog() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(AppTheme.borderRadiusLarge),
          topRight: Radius.circular(AppTheme.borderRadiusLarge),
        ),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text(
                    'Sort By',
                    style: TextStyle(
                      fontSize: AppTheme.fontSizeLarge,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: AppTheme.paddingMedium),
                  _buildSortOption(
                    title: 'Departure Time (Early to Late)',
                    value: 'departure_time_asc',
                    currentValue: _sortBy,
                    onChanged: (value) {
                      setState(() {
                        _sortBy = value!;
                      });
                    },
                  ),
                  _buildSortOption(
                    title: 'Departure Time (Late to Early)',
                    value: 'departure_time_desc',
                    currentValue: _sortBy,
                    onChanged: (value) {
                      setState(() {
                        _sortBy = value!;
                      });
                    },
                  ),
                  _buildSortOption(
                    title: 'Price (Low to High)',
                    value: 'price_asc',
                    currentValue: _sortBy,
                    onChanged: (value) {
                      setState(() {
                        _sortBy = value!;
                      });
                    },
                  ),
                  _buildSortOption(
                    title: 'Price (High to Low)',
                    value: 'price_desc',
                    currentValue: _sortBy,
                    onChanged: (value) {
                      setState(() {
                        _sortBy = value!;
                      });
                    },
                  ),
                  const SizedBox(height: AppTheme.paddingLarge),
                  ElevatedButton(
                    onPressed: () {
                      // Apply filter
                      final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
                      ferryProvider.setSortBy(_sortBy);
                      Navigator.pop(context);
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      minimumSize: const Size(double.infinity, 50),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                      ),
                    ),
                    child: const Text('Apply'),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }
  
  Widget _buildSortOption({
    required String title,
    required String value,
    required String currentValue,
    required void Function(String?) onChanged,
  }) {
    return RadioListTile<String>(
      title: Text(title),
      value: value,
      groupValue: currentValue,
      onChanged: onChanged,
      activeColor: AppTheme.primaryColor,
      contentPadding: EdgeInsets.zero,
      dense: true,
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              _selectedDeparturePort != null && _selectedArrivalPort != null
                  ? '$_selectedDeparturePort → $_selectedArrivalPort'
                  : 'Search Results',
              style: const TextStyle(
                fontSize: AppTheme.fontSizeMedium,
                fontWeight: FontWeight.bold,
              ),
            ),
            if (_selectedDate != null)
              Text(
                DateFormat('EEE, dd MMM yyyy').format(_selectedDate!),
                style: const TextStyle(
                  fontSize: AppTheme.fontSizeSmall,
                  fontWeight: FontWeight.normal,
                ),
              ),
          ],
        ),
        actions: [
          IconButton(
            onPressed: _showFilterDialog,
            icon: const Icon(Icons.filter_list),
          ),
        ],
      ),
      body: _isSearching
          ? const Center(child: LoadingIndicator(message: 'Searching schedules...'))
          : Consumer<FerryProvider>(
              builder: (context, ferryProvider, _) {
                if (ferryProvider.isLoadingSchedules) {
                  return const Center(child: LoadingIndicator(message: 'Loading schedules...'));
                }
                
                if (ferryProvider.scheduleError != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.error_outline,
                          size: 64,
                          color: Colors.red,
                        ),
                        const SizedBox(height: AppTheme.paddingMedium),
                        Text(
                          'Error',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeLarge,
                            fontWeight: FontWeight.bold,
                            color: theme.textTheme.displaySmall?.color,
                          ),
                        ),
                        const SizedBox(height: AppTheme.paddingSmall),
                        Text(
                          ferryProvider.scheduleError!,
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeRegular,
                            color: theme.textTheme.bodyMedium?.color,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: AppTheme.paddingLarge),
                        ElevatedButton(
                          onPressed: _searchSchedules,
                          child: const Text('Try Again'),
                        ),
                      ],
                    ),
                  );
                }
                
                if (ferryProvider.schedules.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.directions_boat_outlined,
                          size: 64,
                          color: theme.hintColor,
                        ),
                        const SizedBox(height: AppTheme.paddingMedium),
                        Text(
                          'No Schedules Found',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeLarge,
                            fontWeight: FontWeight.bold,
                            color: theme.textTheme.displaySmall?.color,
                          ),
                        ),
                        const SizedBox(height: AppTheme.paddingSmall),
                        Text(
                          'Try changing your search criteria or date',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeRegular,
                            color: theme.textTheme.bodyMedium?.color,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: AppTheme.paddingLarge),
                        ElevatedButton(
                          onPressed: () => Navigator.pop(context),
                          child: const Text('Back to Search'),
                        ),
                      ],
                    ),
                  );
                }
                
                // Show list of schedules
                return ListView.builder(
                  itemCount: ferryProvider.schedules.length + 1, // +1 for the header
                  itemBuilder: (context, index) {
                    if (index == 0) {
                      // Header with result count
                      return Padding(
                        padding: const EdgeInsets.all(AppTheme.paddingMedium),
                        child: Text(
                          '${ferryProvider.schedules.length} ${ferryProvider.schedules.length == 1 ? 'Schedule' : 'Schedules'} Found',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeMedium,
                            fontWeight: FontWeight.bold,
                            color: theme.textTheme.displaySmall?.color,
                          ),
                        ),
                      );
                    }
                    
                    final schedule = ferryProvider.schedules[index - 1];
                    return Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: AppTheme.paddingMedium,
                        vertical: AppTheme.paddingSmall,
                      ),
                      child: FerryCard(
                        schedule: schedule,
                        onTap: () => _selectSchedule(schedule),
                      ),
                    );
                  },
                );
              },
            ),
    );
  }
  
  void _selectSchedule(Schedule schedule) {
    final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
    ferryProvider.setSelectedSchedule(schedule.id);
    
    Navigator.pushNamed(
      context,
      AppRoutes.ferryDetails,
      arguments: {'scheduleId': schedule.id},
    );
  }
}