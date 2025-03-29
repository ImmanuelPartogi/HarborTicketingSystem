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

  // Controllers for text fields
  final List<TextEditingController> _nameControllers = [];
  final List<TextEditingController> _idNumberControllers = [];
  final List<TextEditingController> _dobControllers = [];
  final List<TextEditingController> _phoneControllers = [];
  final List<TextEditingController> _emailControllers = [];
  final List<TextEditingController> _addressControllers = [];

  bool _isLoading = false;
  List<Map<String, dynamic>> _savedPassengers = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();

    // Initialize lists based on passenger count
    for (int i = 0; i < widget.passengerCount; i++) {
      _formKeys.add(GlobalKey<FormState>());
      _passengerData.add({});
      _savePassengerInfo.add(i == 0); // Default to save for first passenger

      // Initialize controllers
      _nameControllers.add(TextEditingController());
      _idNumberControllers.add(TextEditingController());
      _dobControllers.add(TextEditingController());
      _phoneControllers.add(TextEditingController());
      _emailControllers.add(TextEditingController());
      _addressControllers.add(TextEditingController());
    }

    // Set schedule ID in the booking provider
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );
    bookingProvider.setScheduleId(widget.scheduleId);

    // Load user data for the first passenger
    _loadUserData();

    // Bungkus dalam Future.microtask untuk menghindari setState() during build
    Future.microtask(() {
      _loadSavedPassengers();
    });
  }

  @override
  void dispose() {
    // Dispose controllers
    for (var controller in _nameControllers) {
      controller.dispose();
    }
    for (var controller in _idNumberControllers) {
      controller.dispose();
    }
    for (var controller in _dobControllers) {
      controller.dispose();
    }
    for (var controller in _phoneControllers) {
      controller.dispose();
    }
    for (var controller in _emailControllers) {
      controller.dispose();
    }
    for (var controller in _addressControllers) {
      controller.dispose();
    }
    super.dispose();
  }

  // Format ISO date to YYYY-MM-DD
  String _formatDate(String? isoDate) {
    if (isoDate == null || isoDate.isEmpty) {
      return '';
    }

    try {
      // Parse the ISO date
      final date = DateTime.parse(isoDate);
      // Format to YYYY-MM-DD
      return "${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}";
    } catch (e) {
      print('Error formatting date: $e');
      return isoDate; // Return original string if parsing fails
    }
  }

  // Load user data for the first passenger
  void _loadUserData() {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final user = authProvider.user;

    if (user != null) {
      // Format date of birth
      final formattedDob = _formatDate(user.dateOfBirth);

      // Only fill the first passenger with user data
      _nameControllers[0].text = user.name;
      _idNumberControllers[0].text = user.identityNumber ?? '';
      _dobControllers[0].text = formattedDob;
      _phoneControllers[0].text = user.phone;
      _emailControllers[0].text = user.email;
      _addressControllers[0].text = user.address ?? '';

      // Set dropdown values
      setState(() {
        _passengerData[0]['identity_type'] = _convertIdType(user.identityType);
        _passengerData[0]['gender'] = _convertGender(user.gender);

        // Also save the data to the passenger data map for form submission
        _passengerData[0]['name'] = user.name;
        _passengerData[0]['identity_number'] = user.identityNumber ?? '';
        _passengerData[0]['date_of_birth'] = formattedDob;
        _passengerData[0]['phone'] = user.phone;
        _passengerData[0]['email'] = user.email;
        _passengerData[0]['address'] = user.address ?? '';
      });
    }
  }

  // Convert ID type from backend format to form format
  String _convertIdType(String? idType) {
    if (idType == null) return 'ktp';

    switch (idType.toUpperCase()) {
      case 'KTP':
        return 'ktp';
      case 'SIM':
        return 'sim';
      case 'PASPOR':
        return 'passport';
      default:
        return 'ktp';
    }
  }

  // Convert gender from backend format to form format
  String _convertGender(String? gender) {
    if (gender == null) return 'm';

    switch (gender.toUpperCase()) {
      case 'MALE':
        return 'm';
      case 'FEMALE':
        return 'f';
      default:
        return 'm';
    }
  }

  Future<void> _loadSavedPassengers() async {
    if (mounted) {
      setState(() {
        _isLoading = true;
      });
    }

    try {
      // Tambahkan pengecekan inisialisasi BookingProvider
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );
      
      // Tunggu provider terinisialisasi
      if (!bookingProvider.isInitialized) {
        print('Waiting for BookingProvider to initialize...');
        await Future.delayed(Duration(milliseconds: 500));
        
        // Jika masih belum terinisialisasi setelah beberapa kali percobaan, abort
        int retryCount = 0;
        while (!bookingProvider.isInitialized && retryCount < 5) {
          await Future.delayed(Duration(milliseconds: 500));
          retryCount++;
        }
        
        if (!bookingProvider.isInitialized) {
          throw Exception('BookingProvider initialization timeout');
        }
      }

      final savedPassengers = await bookingProvider.loadSavedPassengers();
      print('Loaded ${savedPassengers.length} saved passengers');

      if (mounted) {
        setState(() {
          _savedPassengers = savedPassengers;
          _isLoading = false;
        });
      }
    } catch (e) {
      print('Error loading saved passengers: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Failed to load saved passengers. Please try again.';
        });
      }
    }
  }

  void _useSavedPassenger(
    int passengerIndex,
    Map<String, dynamic> savedPassenger,
  ) {
    setState(() {
      // Format date of birth if needed
      final formattedDob = _formatDate(savedPassenger['date_of_birth']);

      // Make a copy of savedPassenger to avoid modifying the original
      final updatedPassenger = {...savedPassenger};
      updatedPassenger['date_of_birth'] = formattedDob;

      // Update passenger data map
      _passengerData[passengerIndex] = updatedPassenger;

      // Update the controllers to reflect the saved data
      _nameControllers[passengerIndex].text = savedPassenger['name'] ?? '';
      _idNumberControllers[passengerIndex].text =
          savedPassenger['identity_number'] ?? '';
      _dobControllers[passengerIndex].text = formattedDob;
      _phoneControllers[passengerIndex].text = savedPassenger['phone'] ?? '';
      _emailControllers[passengerIndex].text = savedPassenger['email'] ?? '';
      _addressControllers[passengerIndex].text =
          savedPassenger['address'] ?? '';
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
                child:
                    _savedPassengers.isEmpty
                        ? const Center(child: Text('No saved passengers found'))
                        : ListView.builder(
                          itemCount: _savedPassengers.length,
                          itemBuilder: (context, index) {
                            final passenger = _savedPassengers[index];
                            return ListTile(
                              title: Text(passenger['name'] ?? 'Unknown'),
                              subtitle: Text(
                                '${passenger['identity_type']?.toUpperCase() ?? 'ID'}: ${passenger['identity_number'] ?? 'Unknown'}',
                              ),
                              onTap:
                                  () => _useSavedPassenger(
                                    passengerIndex,
                                    passenger,
                                  ),
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

    // Update passenger data with values from controllers
    for (int i = 0; i < widget.passengerCount; i++) {
      _passengerData[i]['name'] = _nameControllers[i].text;
      _passengerData[i]['identity_number'] = _idNumberControllers[i].text;
      _passengerData[i]['date_of_birth'] = _dobControllers[i].text;
      _passengerData[i]['phone'] = _phoneControllers[i].text;
      _passengerData[i]['email'] = _emailControllers[i].text;
      _passengerData[i]['address'] = _addressControllers[i].text;
      _passengerData[i]['save_info'] = _savePassengerInfo[i];
    }

    // Add passengers to booking provider
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );
    bookingProvider.clearPassengers();

    for (var passengerData in _passengerData) {
      bookingProvider.addPassenger(passengerData);
    }

    // Navigate to next screen
    if (widget.hasVehicle) {
      Navigator.pushNamed(
        context,
        AppRoutes.vehicleDetails,
        arguments: {'scheduleId': widget.scheduleId, 'passengerIds': <int>[]},
      );
    } else {
      // Create booking directly if no vehicle
      try {
        setState(() {
          _isLoading = true;
        });

        final success = await bookingProvider.createBooking();
        if (success) {
          final booking = bookingProvider.currentBooking;

          // TAMBAHKAN: Validasi
          if (booking == null || booking.id <= 0) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text(
                  'Booking created but ID is invalid. Please try again.',
                ),
                backgroundColor: Colors.red,
              ),
            );
            return;
          }

          // Lanjut ke layar pembayaran dengan ID yang benar
          Navigator.pushNamed(
            context,
            AppRoutes.payment,
            arguments: {
              'bookingId': booking.id,
              'totalAmount': booking.totalAmount,
            },
          );
        } else {
          // Tampilkan pesan error jika bookingProvider memiliki error
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                bookingProvider.bookingError ?? 'Failed to create booking. Please try again.',
              ),
              backgroundColor: Colors.red,
            ),
          );
        }
      } catch (e) {
        // Tangani error tak terduga
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
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
      appBar: AppBar(title: const Text('Passenger Details')),
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: 'Processing booking...',
        child: Column(
          children: [
            // Error message if any
            if (_errorMessage != null)
              Container(
                padding: const EdgeInsets.all(AppTheme.paddingRegular),
                margin: const EdgeInsets.all(AppTheme.paddingMedium),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
                  border: Border.all(color: Colors.red.shade200),
                ),
                child: Row(
                  children: [
                    Icon(Icons.error_outline, color: Colors.red),
                    SizedBox(width: AppTheme.paddingSmall),
                    Expanded(
                      child: Text(
                        _errorMessage!,
                        style: TextStyle(color: Colors.red.shade700),
                      ),
                    ),
                    IconButton(
                      icon: Icon(Icons.close, size: 16),
                      onPressed: () {
                        setState(() {
                          _errorMessage = null;
                        });
                      },
                    ),
                  ],
                ),
              ),
              
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
                text:
                    widget.hasVehicle
                        ? 'Continue to Vehicle Details'
                        : 'Continue to Payment',
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
                    onPressed:
                        () => _showSavedPassengersBottomSheet(passengerIndex),
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
              controller: _nameControllers[passengerIndex],
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
                        value:
                            _passengerData[passengerIndex]['identity_type'] ??
                            'ktp',
                        decoration: InputDecoration(
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.paddingMedium,
                            vertical: AppTheme.paddingRegular,
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusRegular,
                            ),
                          ),
                          filled: true,
                          fillColor: theme.cardColor,
                        ),
                        items: const [
                          DropdownMenuItem(value: 'ktp', child: Text('KTP')),
                          DropdownMenuItem(value: 'sim', child: Text('SIM')),
                          DropdownMenuItem(
                            value: 'passport',
                            child: Text('Passport'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _passengerData[passengerIndex]['identity_type'] =
                                value;
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
                    controller: _idNumberControllers[passengerIndex],
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
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusRegular,
                            ),
                          ),
                          filled: true,
                          fillColor: theme.cardColor,
                        ),
                        items: const [
                          DropdownMenuItem(value: 'm', child: Text('Male')),
                          DropdownMenuItem(value: 'f', child: Text('Female')),
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
                    controller: _dobControllers[passengerIndex],
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
              controller: _phoneControllers[passengerIndex],
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
              controller: _emailControllers[passengerIndex],
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
              controller: _addressControllers[passengerIndex],
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
                        _savePassengerInfo[passengerIndex] =
                            !_savePassengerInfo[passengerIndex];
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

// LoadingOverlay widget untuk menampilkan indikator loading
class LoadingOverlay extends StatelessWidget {
  final bool isLoading;
  final Widget child;
  final String loadingMessage;
  final Color color;

  const LoadingOverlay({
    Key? key,
    required this.isLoading,
    required this.child,
    this.loadingMessage = 'Loading...',
    this.color = Colors.white,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        child,
        if (isLoading)
          Container(
            color: Colors.black.withOpacity(0.3),
            child: Center(
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const CircularProgressIndicator(),
                    const SizedBox(height: 20),
                    Text(
                      loadingMessage,
                      style: const TextStyle(
                        color: Colors.black87,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
      ],
    );
  }
}