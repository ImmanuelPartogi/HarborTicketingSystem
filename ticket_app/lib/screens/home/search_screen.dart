import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:flutter_animate/flutter_animate.dart';

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
  State createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> with SingleTickerProviderStateMixin {
  String _sortBy = 'departure_time_asc';
  bool _isSearching = false;
  DateTime? _selectedDate;
  String? _selectedDeparturePort;
  String? _selectedArrivalPort;
  int _passengerCount = 1;
  late AnimationController _animationController;
  
  @override
  void initState() {
    super.initState();
    _selectedDate = widget.departureDate ?? DateTime.now();
    _selectedDeparturePort = widget.departurePort;
    _selectedArrivalPort = widget.arrivalPort;
    _passengerCount = widget.passengerCount ?? 1;
    
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );
    
    _searchSchedules();
  }
  
  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
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
      
      // Reset animation controller and forward to trigger animations
      _animationController.reset();
      _animationController.forward();
    } finally {
      setState(() {
        _isSearching = false;
      });
    }
  }
  
  void _showFilterDialog() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return Container(
              decoration: BoxDecoration(
                color: Theme.of(context).scaffoldBackgroundColor,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(AppTheme.borderRadiusLarge),
                  topRight: Radius.circular(AppTheme.borderRadiusLarge),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                    offset: const Offset(0, -5),
                  ),
                ],
              ),
              padding: EdgeInsets.only(
                left: AppTheme.paddingMedium,
                right: AppTheme.paddingMedium,
                top: AppTheme.paddingMedium,
                bottom: MediaQuery.of(context).viewInsets.bottom + AppTheme.paddingMedium,
              ),
              child: Wrap(
                children: [
                  // Handle bar at top
                  Center(
                    child: Container(
                      height: 5,
                      width: 40,
                      margin: const EdgeInsets.only(bottom: AppTheme.paddingMedium),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(100),
                      ),
                    ),
                  ),
                  
                  // Title
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Sort and Filter',
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeLarge,
                          fontWeight: FontWeight.bold,
                          color: Theme.of(context).primaryColor,
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(context),
                        icon: const Icon(Icons.close),
                        splashRadius: 24,
                      ),
                    ],
                  ),
                  
                  const Divider(),
                  const SizedBox(height: AppTheme.paddingSmall),
                  
                  // Sort options
                  const Text(
                    'Sort By',
                    style: TextStyle(
                      fontSize: AppTheme.fontSizeMedium,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: AppTheme.paddingSmall),
                  
                  // Sort options cards in a 2x2 grid
                  GridView.count(
                    crossAxisCount: 2,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    mainAxisSpacing: AppTheme.paddingSmall,
                    crossAxisSpacing: AppTheme.paddingSmall,
                    childAspectRatio: 2.5,
                    children: [
                      _buildSortOptionCard(
                        title: 'Earliest Departure',
                        icon: Icons.access_time,
                        value: 'departure_time_asc',
                        currentValue: _sortBy,
                        onSelect: (value) {
                          setState(() {
                            _sortBy = value;
                          });
                        },
                      ),
                      _buildSortOptionCard(
                        title: 'Latest Departure',
                        icon: Icons.access_time_filled,
                        value: 'departure_time_desc',
                        currentValue: _sortBy,
                        onSelect: (value) {
                          setState(() {
                            _sortBy = value;
                          });
                        },
                      ),
                      _buildSortOptionCard(
                        title: 'Lowest Price',
                        icon: Icons.arrow_downward,
                        value: 'price_asc',
                        currentValue: _sortBy,
                        onSelect: (value) {
                          setState(() {
                            _sortBy = value;
                          });
                        },
                      ),
                      _buildSortOptionCard(
                        title: 'Highest Price',
                        icon: Icons.arrow_upward,
                        value: 'price_desc',
                        currentValue: _sortBy,
                        onSelect: (value) {
                          setState(() {
                            _sortBy = value;
                          });
                        },
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: AppTheme.paddingLarge),
                  
                  // Apply button
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
                      minimumSize: const Size(double.infinity, 55),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                      ),
                      elevation: 2,
                    ),
                    child: const Text(
                      'Apply',
                      style: TextStyle(
                        fontSize: AppTheme.fontSizeMedium,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  
                  const SizedBox(height: AppTheme.paddingSmall),
                ],
              ),
            );
          },
        );
      },
    );
  }
  
  Widget _buildSortOptionCard({
    required String title,
    required IconData icon,
    required String value,
    required String currentValue,
    required Function(String) onSelect,
  }) {
    final isSelected = value == currentValue;
    final theme = Theme.of(context);
    
    return GestureDetector(
      onTap: () => onSelect(value),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        decoration: BoxDecoration(
          color: isSelected ? theme.primaryColor.withOpacity(0.1) : theme.cardColor,
          borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
          border: Border.all(
            color: isSelected ? theme.primaryColor : theme.dividerColor,
            width: 1.5,
          ),
        ),
        padding: const EdgeInsets.symmetric(
          horizontal: AppTheme.paddingSmall,
          vertical: AppTheme.paddingSmall,
        ),
        child: Row(
          children: [
            Icon(
              icon,
              color: isSelected ? theme.primaryColor : theme.hintColor,
              size: 20,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                title,
                style: TextStyle(
                  fontSize: AppTheme.fontSizeSmall,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  color: isSelected ? theme.primaryColor : theme.textTheme.bodyMedium?.color,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
            if (isSelected)
              Icon(
                Icons.check_circle,
                color: theme.primaryColor,
                size: 18,
              ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        elevation: 0,
        backgroundColor: theme.scaffoldBackgroundColor,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.directions_boat,
                  color: theme.primaryColor,
                  size: 20,
                ),
                const SizedBox(width: 8),
                Text(
                  _selectedDeparturePort != null && _selectedArrivalPort != null
                      ? '$_selectedDeparturePort → $_selectedArrivalPort'
                      : 'Search Results',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeMedium,
                    fontWeight: FontWeight.bold,
                    color: theme.textTheme.titleLarge?.color,
                  ),
                ),
              ],
            ),
            if (_selectedDate != null)
              Padding(
                padding: const EdgeInsets.only(left: 28),
                child: Text(
                  DateFormat('EEEE, dd MMMM yyyy').format(_selectedDate!),
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeSmall,
                    fontWeight: FontWeight.normal,
                    color: theme.hintColor,
                  ),
                ),
              ),
          ],
        ),
        actions: [
          // Filter button with animation
          Container(
            margin: const EdgeInsets.only(right: AppTheme.paddingSmall),
            decoration: BoxDecoration(
              color: theme.primaryColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
            ),
            child: IconButton(
              onPressed: _showFilterDialog,
              icon: const Icon(Icons.filter_list),
              tooltip: 'Sort Options',
              color: theme.primaryColor,
              splashRadius: 24,
            ),
          ),
        ],
      ),
      body: _isSearching
          ? Center(
              child: LoadingIndicator(
                message: 'Searching for the best routes...',
                color: theme.primaryColor,
              ),
            )
          : Consumer<FerryProvider>(
              builder: (context, ferryProvider, _) {
                if (ferryProvider.isLoadingSchedules) {
                  return Center(
                    child: LoadingIndicator(
                      message: 'Loading available schedules...',
                      color: theme.primaryColor,
                    ),
                  );
                }
                
                if (ferryProvider.scheduleError != null) {
                  return _buildErrorState(ferryProvider.scheduleError!, theme);
                }
                
                if (ferryProvider.schedules.isEmpty) {
                  return _buildEmptyState(theme);
                }
                
                // Show list of schedules with staggered animation
                return Column(
                  children: [
                    // Search result info card
                    Container(
                      margin: const EdgeInsets.all(AppTheme.paddingMedium),
                      padding: const EdgeInsets.symmetric(
                        horizontal: AppTheme.paddingMedium,
                        vertical: AppTheme.paddingSmall,
                      ),
                      decoration: BoxDecoration(
                        color: theme.primaryColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                        border: Border.all(
                          color: theme.primaryColor.withOpacity(0.3),
                          width: 1,
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            Icons.info_outline,
                            color: theme.primaryColor,
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '${ferryProvider.schedules.length} ${ferryProvider.schedules.length == 1 ? 'Schedule' : 'Schedules'} Found',
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.bold,
                              color: theme.primaryColor,
                            ),
                          ),
                        ],
                      ),
                    ).animate(controller: _animationController)
                      .fadeIn(duration: 500.ms)
                      .slideY(begin: -0.2, end: 0, duration: 500.ms, curve: Curves.easeOutQuad),
                    
                    // Schedule list
                    Expanded(
                      child: ListView.builder(
                        itemCount: ferryProvider.schedules.length,
                        padding: const EdgeInsets.fromLTRB(
                          AppTheme.paddingMedium,
                          0,
                          AppTheme.paddingMedium,
                          AppTheme.paddingMedium,
                        ),
                        itemBuilder: (context, index) {
                          final schedule = ferryProvider.schedules[index];
                          
                          // Enhanced Ferry Card with staggered animation
                          return Padding(
                            padding: const EdgeInsets.only(bottom: AppTheme.paddingMedium),
                            child: FerryCard(
                              schedule: schedule,
                              onTap: () => _selectSchedule(schedule),
                            )
                              .animate(controller: _animationController)
                              .fadeIn(
                                duration: 400.ms,
                                delay: (index * 100).ms,
                                curve: Curves.easeOut,
                              )
                              .slideX(
                                begin: 0.1,
                                end: 0,
                                duration: 400.ms,
                                delay: (index * 100).ms,
                                curve: Curves.easeOutQuad,
                              ),
                          );
                        },
                      ),
                    ),
                  ],
                );
              },
            ),
    );
  }
  
  Widget _buildErrorState(String errorMessage, ThemeData theme) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.error_outline,
                size: 64,
                color: Colors.red.shade700,
              ),
            ),
            const SizedBox(height: AppTheme.paddingMedium),
            Text(
              'Oops! Something went wrong',
              style: TextStyle(
                fontSize: AppTheme.fontSizeLarge,
                fontWeight: FontWeight.bold,
                color: theme.textTheme.displaySmall?.color,
              ),
            ),
            const SizedBox(height: AppTheme.paddingSmall),
            Text(
              errorMessage,
              style: TextStyle(
                fontSize: AppTheme.fontSizeRegular,
                color: theme.textTheme.bodyMedium?.color,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppTheme.paddingLarge),
            ElevatedButton.icon(
              onPressed: _searchSchedules,
              icon: const Icon(Icons.refresh),
              label: const Text('Try Again'),
              style: ElevatedButton.styleFrom(
                backgroundColor: theme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: AppTheme.paddingLarge,
                  vertical: AppTheme.paddingSmall,
                ),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
  
  Widget _buildEmptyState(ThemeData theme) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.paddingLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              decoration: BoxDecoration(
                color: theme.primaryColor.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.directions_boat_outlined,
                size: 64,
                color: theme.primaryColor,
              ),
            ),
            const SizedBox(height: AppTheme.paddingMedium),
            Text(
              'No Schedules Available',
              style: TextStyle(
                fontSize: AppTheme.fontSizeLarge,
                fontWeight: FontWeight.bold,
                color: theme.textTheme.displaySmall?.color,
              ),
            ),
            const SizedBox(height: AppTheme.paddingSmall),
            Text(
              'There are no ferry schedules for this route on the selected date. Try changing your search criteria or date.',
              style: TextStyle(
                fontSize: AppTheme.fontSizeRegular,
                color: theme.textTheme.bodyMedium?.color,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppTheme.paddingLarge),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                OutlinedButton.icon(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.arrow_back),
                  label: const Text('Back to Search'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: theme.primaryColor,
                    side: BorderSide(color: theme.primaryColor),
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingMedium,
                      vertical: AppTheme.paddingSmall,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                    ),
                  ),
                ),
                const SizedBox(width: AppTheme.paddingMedium),
                ElevatedButton.icon(
                  onPressed: () {
                    // Set date to tomorrow and search again
                    setState(() {
                      _selectedDate = DateTime.now().add(const Duration(days: 1));
                    });
                    _searchSchedules();
                  },
                  icon: const Icon(Icons.calendar_today),
                  label: const Text('Try Tomorrow'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: theme.primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.paddingMedium,
                      vertical: AppTheme.paddingSmall,
                    ),
                    elevation: 2,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
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