import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/custom_text_field.dart';
import '../../widgets/common/loading_indicator.dart';

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
  final TextEditingController _identityNumberController = TextEditingController();
  String _identityType = 'ktp';
  final TextEditingController _dateOfBirthController = TextEditingController();
  String _gender = 'm';
  final TextEditingController _addressController = TextEditingController();
  
  bool _isLoading = false;
  
  @override
  void initState() {
    super.initState();
    _loadUserData();
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
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final user = authProvider.user;
    
    if (user != null) {
      _nameController.text = user.name;
      _emailController.text = user.email;
      _phoneController.text = user.phone;
      _identityNumberController.text = user.identityNumber ?? '';
      _identityType = user.identityType ?? 'ktp';
      _dateOfBirthController.text = user.dateOfBirth ?? '';
      _gender = user.gender ?? 'm';
      _addressController.text = user.address ?? '';
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
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Profile'),
      ),
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
                      child: CustomTextField(
                        label: 'Date of Birth',
                        hintText: 'YYYY-MM-DD',
                        controller: _dateOfBirthController,
                        validator: (value) {
                          if (value != null && value.isNotEmpty) {
                            // Simple date format validation
                            final dateFormatRegex = RegExp(r'^\d{4}-\d{2}-\d{2}$');
                            if (!dateFormatRegex.hasMatch(value)) {
                              return 'Use YYYY-MM-DD format';
                            }
                          }
                          return null;
                        },
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