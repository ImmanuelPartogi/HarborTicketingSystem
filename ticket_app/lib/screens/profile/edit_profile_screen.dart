import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/custom_text_field.dart';
import '../../widgets/common/loading_indicator.dart';
import '../../models/user_model.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({Key? key}) : super(key: key);

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _identityNumberController =
      TextEditingController();
  String _identityType = 'KTP';
  final TextEditingController _dateOfBirthController = TextEditingController();
  String _gender = 'MALE';
  final TextEditingController _addressController = TextEditingController();

  DateTime? _selectedDate;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    // Load user data in initState, not during build
    Future.microtask(() {
      _loadUserData();
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _identityNumberController.dispose();
    _dateOfBirthController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  void _loadUserData() {
    final authProvider = _getAuthProvider();
    final user = authProvider.user;

    if (user != null) {
      _nameController.text = user.name;
      _emailController.text = user.email;
      _phoneController.text = user.phone;
      _identityNumberController.text = user.identityNumber ?? '';

      // Use uppercase values for dropdowns to match backend expectations
      _identityType = user.identityType?.toUpperCase() ?? 'KTP';

      // Parse date of birth if available
      if (user.dateOfBirth != null && user.dateOfBirth!.isNotEmpty) {
        try {
          _selectedDate = DateTime.parse(user.dateOfBirth!);
          _dateOfBirthController.text = DateFormat(
            'yyyy-MM-dd',
          ).format(_selectedDate!);
        } catch (e) {
          _dateOfBirthController.text = '';
        }
      }

      // Use uppercase gender to match backend expectations
      _gender = user.gender?.toUpperCase() ?? 'MALE';

      _addressController.text = user.address ?? '';
    }
  }

  Future<void> _selectDate(BuildContext context) async {
    final DateTime now = DateTime.now();
    final DateTime initialDate =
        _selectedDate ?? DateTime(now.year - 18, now.month, now.day);

    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: DateTime(1900),
      lastDate: now,
      helpText: 'Select your date of birth',
      errorFormatText: 'Enter valid date',
      errorInvalidText: 'Enter date within valid range',
      fieldLabelText: 'Date of birth',
      fieldHintText: 'YYYY-MM-DD',
    );

    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
        _dateOfBirthController.text = DateFormat('yyyy-MM-dd').format(picked);
      });
    }
  }

  Future<void> _updateProfile() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);

      // Map form fields to backend expected field names
      final profileData = {
        'name': _nameController.text,
        'email': _emailController.text,
        'phone': _phoneController.text,
        'identity_number': _identityNumberController.text,
        'identity_type': _identityType,
        'date_of_birth': _dateOfBirthController.text,
        'gender': _gender,
        'address': _addressController.text,
      };

      final success = await authProvider.updateProfile(profileData);

      if (success && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Profile updated successfully'),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.pop(context);
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(authProvider.error ?? 'Failed to update profile'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('An error occurred: ${e.toString()}'),
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

  // Helper method to get the current auth provider
  AuthProvider _getAuthProvider() {
    return Provider.of<AuthProvider>(context, listen: false);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Edit Profile')),
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: 'Updating profile...',
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppTheme.paddingMedium),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Basic information
                Text(
                  'Basic Information',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeMedium,
                    fontWeight: FontWeight.bold,
                    color: theme.textTheme.displaySmall?.color,
                  ),
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Full name
                CustomTextField(
                  label: 'Full Name',
                  hintText: 'Enter your full name',
                  controller: _nameController,
                  isRequired: true,
                  textCapitalization: TextCapitalization.words,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Full name is required';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Email
                CustomTextField(
                  label: 'Email',
                  hintText: 'Enter your email address',
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  isRequired: true,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Email is required';
                    }
                    if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(value)) {
                      return 'Please enter a valid email address';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Phone number
                CustomTextField(
                  label: 'Phone Number',
                  hintText: 'Enter your phone number',
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  isRequired: true,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Phone number is required';
                    }
                    if (value.length < 10) {
                      return 'Phone number must be at least 10 digits';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Identity information
                Text(
                  'Identity Information',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeMedium,
                    fontWeight: FontWeight.bold,
                    color: theme.textTheme.displaySmall?.color,
                  ),
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
                          Text(
                            'ID Type',
                            style: TextStyle(
                              color: theme.textTheme.bodyLarge?.color,
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 8),
                          DropdownButtonFormField<String>(
                            value: _identityType,
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
                              DropdownMenuItem(
                                value: 'KTP',
                                child: Text('KTP'),
                              ),
                              DropdownMenuItem(
                                value: 'SIM',
                                child: Text('SIM'),
                              ),
                              DropdownMenuItem(
                                value: 'PASPOR',
                                child: Text('Passport'),
                              ),
                            ],
                            onChanged: (value) {
                              setState(() {
                                _identityType = value!;
                              });
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
                        controller: _identityNumberController,
                        validator: (value) {
                          return null;
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
                          Text(
                            'Gender',
                            style: TextStyle(
                              color: theme.textTheme.bodyLarge?.color,
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 8),
                          DropdownButtonFormField<String>(
                            value: _gender,
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
                              DropdownMenuItem(
                                value: 'MALE',
                                child: Text('Male'),
                              ),
                              DropdownMenuItem(
                                value: 'FEMALE',
                                child: Text('Female'),
                              ),
                            ],
                            onChanged: (value) {
                              setState(() {
                                _gender = value!;
                              });
                            },
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(width: AppTheme.paddingMedium),

                    // Date of Birth
                    Expanded(
                      child: InkWell(
                        onTap: () => _selectDate(context),
                        child: CustomTextField(
                          label: 'Date of Birth',
                          hintText: 'YYYY-MM-DD',
                          controller: _dateOfBirthController,
                          readOnly:
                              true, // Make it read-only since we use a date picker
                          suffixIcon: Icons.calendar_today,
                          onSuffixIconPressed: () => _selectDate(context),
                          validator: (value) {
                            if (value != null && value.isNotEmpty) {
                              // Simple date format validation
                              final dateFormatRegex = RegExp(
                                r'^\d{4}-\d{2}-\d{2}$',
                              );
                              if (!dateFormatRegex.hasMatch(value)) {
                                return 'Use YYYY-MM-DD format';
                              }

                              try {
                                final date = DateTime.parse(value);
                                final now = DateTime.now();
                                if (date.isAfter(now)) {
                                  return 'Date cannot be in the future';
                                }
                              } catch (e) {
                                return 'Invalid date';
                              }
                            }
                            return null;
                          },
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: AppTheme.paddingLarge),

                // Address information
                Text(
                  'Address Information',
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeMedium,
                    fontWeight: FontWeight.bold,
                    color: theme.textTheme.displaySmall?.color,
                  ),
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Address
                CustomTextField(
                  label: 'Address',
                  hintText: 'Enter your address',
                  controller: _addressController,
                  maxLines: 3,
                  validator: (value) {
                    return null;
                  },
                ),

                const SizedBox(height: AppTheme.paddingXLarge),

                // Update button
                CustomButton(
                  text: 'Update Profile',
                  onPressed: _updateProfile,
                  type: ButtonType.primary,
                  isFullWidth: true,
                ),

                const SizedBox(height: AppTheme.paddingMedium),

                // Cancel button
                CustomButton(
                  text: 'Cancel',
                  onPressed: () => Navigator.pop(context),
                  type: ButtonType.outline,
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
}
