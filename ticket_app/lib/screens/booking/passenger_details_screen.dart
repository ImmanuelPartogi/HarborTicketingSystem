import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

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
  State<PassengerDetailsScreen> createState() => _PassengerDetailsScreenState();
}

class _PassengerDetailsScreenState extends State<PassengerDetailsScreen> {
  final List<GlobalKey<FormState>> _formKeys = [];
  final List<Map<String, dynamic>> _passengerData = [];
  final List<bool> _savePassengerInfo = [];
  
  bool _isLoading = false;
  List<Map<String, dynamic>> _savedPassengers = [];
  
  @override
  void initState() {
    super.initState();
    
    // Initialize lists based on passenger count
    for (int i = 0; i < widget.passengerCount; i++) {
      _formKeys.add(GlobalKey<FormState>());
      _passengerData.add({});
      _savePassengerInfo.add(i == 0); // Default to save for first passenger
    }
    
    // Set schedule ID in the booking provider
    final bookingProvider = Provider.of<BookingProvider>(context, listen: false);
    bookingProvider.setScheduleId(widget.scheduleId);
    
    // Load saved passengers
    _loadSavedPassengers();
  }
  
  Future<void> _loadSavedPassengers() async {
    setState(() {
      _isLoading = true;
    });
    
    try {
      final bookingProvider = Provider.of<BookingProvider>(context, listen: false);
      final savedPassengers = await bookingProvider.loadSavedPassengers();
      
      setState(() {
        _savedPassengers = savedPassengers;
        
        // Pre-fill first passenger data with user information if available
        if (savedPassengers.isNotEmpty && _passengerData.isNotEmpty) {
          _passengerData[0] = {...savedPassengers[0]};
        } else {
          // Try to use current user data if available
          final authProvider = Provider.of<AuthProvider>(context, listen: false);
          final user = authProvider.user;
          
          if (user != null && _passengerData.isNotEmpty) {
            _passengerData[0] = {
              'name': user.name,
              'identity_number': user.identityNumber ?? '',
              'identity_type': user.identityType ?? 'ktp',
              'gender': user.gender ?? 'm',
              'date_of_birth': user.dateOfBirth ?? '',
              'phone': user.phone,
              'email': user.email,
              'address': user.address ?? '',
            };
          }
        }
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }
  
  void _useSavedPassenger(int passengerIndex, Map<String, dynamic> savedPassenger) {
    setState(() {
      _passengerData[passengerIndex] = {...savedPassenger};
    });
    
    Navigator.pop(context); // Close the bottom sheet
  }
  
  void _showSavedPassengersBottomSheet(int passengerIndex) {
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
                'Select Saved Passenger',
                style: TextStyle(
                  fontSize: AppTheme.fontSizeLarge,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: AppTheme.paddingMedium),
              
              Expanded(
                child: _savedPassengers.isEmpty
                    ? const Center(
                        child: Text('No saved passengers found'),
                      )
                    : ListView.builder(
                        itemCount: _savedPassengers.length,
                        itemBuilder: (context, index) {
                          final passenger = _savedPassengers[index];
                          return ListTile(
                            title: Text(passenger['name'] ?? 'Unknown'),
                            subtitle: Text(
                              '${passenger['identity_type']?.toUpperCase() ?? 'ID'}: ${passenger['identity_number'] ?? 'Unknown'}',
                            ),
                            onTap: () => _useSavedPassenger(passengerIndex, passenger),
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
  
  bool _validateForms() {
    bool isValid = true;
    
    for (int i = 0; i < _formKeys.length; i++) {
      if (!(_formKeys[i].currentState?.validate() ?? false)) {
        isValid = false;
      }
    }
    
    return isValid;
  }
  
  Future<void> _proceedToNext() async {
    if (!_validateForms()) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please fill all required passenger details'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }
    
    // Save form data for each passenger
    for (int i = 0; i < _formKeys.length; i++) {
      _formKeys[i].currentState?.save();
      _passengerData[i]['save_info'] = _savePassengerInfo[i];
    }
    
    // Add passengers to booking provider
    final bookingProvider = Provider.of<BookingProvider>(context, listen: false);
    bookingProvider.clearPassengers();
    
    for (var passengerData in _passengerData) {
      bookingProvider.addPassenger(passengerData);
    }
    
    // Navigate to next screen
    if (widget.hasVehicle) {
      Navigator.pushNamed(
        context,
        AppRoutes.vehicleDetails,
        arguments: {
          'scheduleId': widget.scheduleId,
          'passengerIds': _passengerData.map((p) => p['id']).toList(),
        },
      );
    } else {
      // Create booking directly if no vehicle
      try {
        setState(() {
          _isLoading = true;
        });
        
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
              content: Text(bookingProvider.bookingError ?? 'Failed to create booking'),
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
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Passenger Details'),
      ),
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: 'Processing booking...',
        child: Column(
          children: [
            Expanded(
              child: DefaultTabController(
                length: widget.passengerCount,
                child: Column(
                  children: [
                    // Tab bar with passenger numbers
                    if (widget.passengerCount > 1)
                      TabBar(
                        tabs: List.generate(
                          widget.passengerCount,
                          (index) => Tab(text: 'Passenger ${index + 1}'),
                        ),
                        labelColor: AppTheme.primaryColor,
                        unselectedLabelColor: theme.textTheme.bodyMedium?.color,
                      ),
                    
                    // Tab content with passenger forms
                    Expanded(
                      child: TabBarView(
                        children: List.generate(
                          widget.passengerCount,
                          (index) => _buildPassengerForm(index),
                        ),
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
                text: widget.hasVehicle ? 'Continue to Vehicle Details' : 'Continue to Payment',
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
  
  Widget _buildPassengerForm(int passengerIndex) {
    final theme = Theme.of(context);
    
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppTheme.paddingMedium),
      child: Form(
        key: _formKeys[passengerIndex],
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header with saved passenger button
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Passenger ${passengerIndex + 1} Information',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeMedium,
                    fontWeight: FontWeight.bold,
                    color: theme.textTheme.displaySmall?.color,
                  ),
                ),
                if (_savedPassengers.isNotEmpty)
                  TextButton.icon(
                    onPressed: () => _showSavedPassengersBottomSheet(passengerIndex),
                    icon: const Icon(Icons.person, size: 18),
                    label: const Text('Use Saved'),
                  ),
              ],
            ),
            
            const SizedBox(height: AppTheme.paddingMedium),
            
            // Name
            CustomTextField(
              label: 'Full Name',
              hintText: 'Enter full name as per ID',
              isRequired: true,
              initialValue: _passengerData[passengerIndex]['name'],
              textCapitalization: TextCapitalization.words,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Full name is required';
                }
                return null;
              },
              onChanged: (value) {
                _passengerData[passengerIndex]['name'] = value;
              },
            ),
            
            const SizedBox(height: AppTheme.paddingMedium),
            
            // ID type and number
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // ID Type
                Expanded(
                  flex: 2,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            'ID Type',
                            style: TextStyle(
                              color: theme.textTheme.bodyLarge?.color,
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const Text(
                            ' *',
                            style: TextStyle(
                              color: Colors.red,
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<String>(
                        value: _passengerData[passengerIndex]['identity_type'] ?? 'ktp',
                        decoration: InputDecoration(
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingMedium,
                            vertical: AppTheme.paddingRegular,
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                          ),
                          filled: true,
                          fillColor: theme.cardColor,
                        ),
                        items: const [
                          DropdownMenuItem(
                            value: 'ktp',
                            child: Text('KTP'),
                          ),
                          DropdownMenuItem(
                            value: 'sim',
                            child: Text('SIM'),
                          ),
                          DropdownMenuItem(
                            value: 'passport',
                            child: Text('Passport'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _passengerData[passengerIndex]['identity_type'] = value;
                          });
                        },
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Required';
                          }
                          return null;
                        },
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(width: AppTheme.paddingMedium),
                
                // ID Number
                Expanded(
                  flex: 3,
                  child: CustomTextField(
                    label: 'ID Number',
                    hintText: 'Enter ID number',
                    isRequired: true,
                    initialValue: _passengerData[passengerIndex]['identity_number'],
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'ID number is required';
                      }
                      return null;
                    },
                    onChanged: (value) {
                      _passengerData[passengerIndex]['identity_number'] = value;
                    },
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppTheme.paddingMedium),
            
            // Gender and Date of Birth
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Gender
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            'Gender',
                            style: TextStyle(
                              color: theme.textTheme.bodyLarge?.color,
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const Text(
                            ' *',
                            style: TextStyle(
                              color: Colors.red,
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<String>(
                        value: _passengerData[passengerIndex]['gender'] ?? 'm',
                        decoration: InputDecoration(
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingMedium,
                            vertical: AppTheme.paddingRegular,
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                          ),
                          filled: true,
                          fillColor: theme.cardColor,
                        ),
                        items: const [
                          DropdownMenuItem(
                            value: 'm',
                            child: Text('Male'),
                          ),
                          DropdownMenuItem(
                            value: 'f',
                            child: Text('Female'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _passengerData[passengerIndex]['gender'] = value;
                          });
                        },
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Required';
                          }
                          return null;
                        },
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(width: AppTheme.paddingMedium),
                
                // Date of Birth
                Expanded(
                  child: CustomTextField(
                    label: 'Date of Birth',
                    hintText: 'YYYY-MM-DD',
                    isRequired: true,
                    initialValue: _passengerData[passengerIndex]['date_of_birth'],
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Date of birth is required';
                      }
                      // Simple date format validation
                      final dateFormatRegex = RegExp(r'^\d{4}-\d{2}-\d{2}$');
                      if (!dateFormatRegex.hasMatch(value)) {
                        return 'Use YYYY-MM-DD format';
                      }
                      return null;
                    },
                    onChanged: (value) {
                      _passengerData[passengerIndex]['date_of_birth'] = value;
                    },
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppTheme.paddingMedium),
            
            // Contact: Phone, Email
            CustomTextField(
              label: 'Phone Number',
              hintText: 'Enter phone number',
              isRequired: true,
              initialValue: _passengerData[passengerIndex]['phone'],
              keyboardType: TextInputType.phone,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Phone number is required';
                }
                return null;
              },
              onChanged: (value) {
                _passengerData[passengerIndex]['phone'] = value;
              },
            ),
            
            const SizedBox(height: AppTheme.paddingMedium),
            
            CustomTextField(
              label: 'Email',
              hintText: 'Enter email address',
              isRequired: true,
              initialValue: _passengerData[passengerIndex]['email'],
              keyboardType: TextInputType.emailAddress,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Email is required';
                }
                if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(value)) {
                  return 'Enter a valid email address';
                }
                return null;
              },
              onChanged: (value) {
                _passengerData[passengerIndex]['email'] = value;
              },
            ),
            
            const SizedBox(height: AppTheme.paddingMedium),
            
            // Address
            CustomTextField(
              label: 'Address',
              hintText: 'Enter address',
              initialValue: _passengerData[passengerIndex]['address'],
              maxLines: 3,
              onChanged: (value) {
                _passengerData[passengerIndex]['address'] = value;
              },
            ),
            
            const SizedBox(height: AppTheme.paddingMedium),
            
            // Save passenger info checkbox
            Row(
              children: [
                Checkbox(
                  value: _savePassengerInfo[passengerIndex],
                  onChanged: (value) {
                    setState(() {
                      _savePassengerInfo[passengerIndex] = value ?? false;
                    });
                  },
                  activeColor: AppTheme.primaryColor,
                ),
                Expanded(
                  child: GestureDetector(
                    onTap: () {
                      setState(() {
                        _savePassengerInfo[passengerIndex] = !_savePassengerInfo[passengerIndex];
                      });
                    },
                    child: Text(
                      'Save passenger information for future bookings',
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
          ],
        ),
      ),
    );
  }
}