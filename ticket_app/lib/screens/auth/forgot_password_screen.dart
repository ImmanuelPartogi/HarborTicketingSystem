import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/custom_text_field.dart';
import '../../widgets/common/loading_indicator.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({Key? key}) : super(key: key);

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _otpController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController = TextEditingController();
  
  bool _isLoading = false;
  String? _errorMessage;
  int _currentStep = 0; // 0: Enter phone, 1: Enter OTP, 2: New Password
  
  bool _isPasswordVisible = false;
  bool _isConfirmPasswordVisible = false;
  
  late ApiService _apiService;
  
  @override
  void initState() {
    super.initState();
    _apiService = ApiService(Provider.of(context, listen: false));
  }
  
  @override
  void dispose() {
    _phoneController.dispose();
    _otpController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }
  
  // Request OTP for password reset
  Future<void> _requestOtp() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    
    try {
      // Call API to request OTP
      await _apiService.post(
        '/api/forgot-password', 
        body: {'phone': _phoneController.text},
        requireAuth: false,
      );
      
      setState(() {
        _currentStep = 1; // Move to OTP verification step
        _isLoading = false;
      });
      
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('OTP has been sent to your phone'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString();
      });
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_errorMessage ?? 'Failed to send OTP'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
  
  // Verify OTP
  Future<void> _verifyOtp() async {
    if (_otpController.text.length != 6) {
      setState(() {
        _errorMessage = 'Please enter a valid 6-digit OTP';
      });
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_errorMessage!),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }
    
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    
    try {
      // Call API to verify OTP
      await _apiService.post(
        '/api/verify-forgot-password-otp',
        body: {
          'phone': _phoneController.text,
          'otp': _otpController.text,
        },
        requireAuth: false,
      );
      
      setState(() {
        _currentStep = 2; // Move to password reset step
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString();
      });
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_errorMessage ?? 'Invalid OTP'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
  
  // Reset Password
  Future<void> _resetPassword() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    
    try {
      // Call API to reset password
      await _apiService.post(
        '/api/reset-password',
        body: {
          'phone': _phoneController.text,
          'otp': _otpController.text,
          'password': _passwordController.text,
          'password_confirmation': _confirmPasswordController.text,
        },
        requireAuth: false,
      );
      
      setState(() {
        _isLoading = false;
      });
      
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Password has been reset successfully'),
          backgroundColor: Colors.green,
        ),
      );
      
      // Navigate to login screen
      Navigator.pushReplacementNamed(context, AppRoutes.login);
    } catch (e) {
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString();
      });
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_errorMessage ?? 'Failed to reset password'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Reset Password'),
        elevation: 0,
      ),
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: _getLoadingMessage(),
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(AppTheme.paddingLarge),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Icon and title
                  Center(
                    child: Column(
                      children: [
                        Icon(
                          Icons.lock_reset,
                          size: 80,
                          color: AppTheme.primaryColor,
                        ),
                        const SizedBox(height: AppTheme.paddingRegular),
                        Text(
                          'Forgot your password?',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeXLarge,
                            fontWeight: FontWeight.bold,
                            color: theme.textTheme.displayLarge?.color,
                          ),
                        ),
                        const SizedBox(height: AppTheme.paddingSmall),
                        Text(
                          _getInstructionText(),
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeRegular,
                            color: theme.textTheme.bodyMedium?.color,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ),
                  
                  const SizedBox(height: AppTheme.paddingXLarge),
                  
                  // Step 1: Phone input
                  Visibility(
                    visible: _currentStep == 0,
                    child: Column(
                      children: [
                        CustomTextField(
                          label: 'Phone Number',
                          hintText: 'Enter your registered phone number',
                          controller: _phoneController,
                          keyboardType: TextInputType.phone,
                          prefixIcon: Icons.phone,
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please enter your phone number';
                            }
                            if (value.length < 10) {
                              return 'Phone number must be at least 10 digits';
                            }
                            return null;
                          },
                        ),
                        
                        const SizedBox(height: AppTheme.paddingLarge),
                        
                        CustomButton(
                          text: 'Send OTP',
                          onPressed: _requestOtp,
                          type: ButtonType.primary,
                          isFullWidth: true,
                          size: ButtonSize.large,
                        ),
                      ],
                    ),
                  ),
                  
                  // Step 2: OTP input
                  Visibility(
                    visible: _currentStep == 1,
                    child: Column(
                      children: [
                        Text(
                          'We have sent an OTP to ${_phoneController.text}',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeRegular,
                            fontWeight: FontWeight.w500,
                            color: theme.textTheme.bodyLarge?.color,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        
                        const SizedBox(height: AppTheme.paddingLarge),
                        
                        CustomTextField(
                          label: 'OTP Code',
                          hintText: 'Enter 6-digit OTP code',
                          controller: _otpController,
                          keyboardType: TextInputType.number,
                          prefixIcon: Icons.lock_outline,
                          maxLength: 6,
                        ),
                        
                        const SizedBox(height: AppTheme.paddingRegular),
                        
                        TextButton(
                          onPressed: _requestOtp,
                          child: Text(
                            'Resend OTP',
                            style: TextStyle(
                              color: AppTheme.primaryColor,
                              fontSize: AppTheme.fontSizeRegular,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        
                        const SizedBox(height: AppTheme.paddingRegular),
                        
                        CustomButton(
                          text: 'Verify OTP',
                          onPressed: _verifyOtp,
                          type: ButtonType.primary,
                          isFullWidth: true,
                          size: ButtonSize.large,
                        ),
                      ],
                    ),
                  ),
                  
                  // Step 3: New password
                  Visibility(
                    visible: _currentStep == 2,
                    child: Column(
                      children: [
                        CustomTextField(
                          label: 'New Password',
                          hintText: 'Enter new password',
                          controller: _passwordController,
                          obscureText: !_isPasswordVisible,
                          prefixIcon: Icons.lock,
                          suffixIcon: _isPasswordVisible ? Icons.visibility : Icons.visibility_off,
                          onSuffixIconPressed: () {
                            setState(() {
                              _isPasswordVisible = !_isPasswordVisible;
                            });
                          },
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please enter a new password';
                            }
                            if (value.length < 8) {
                              return 'Password must be at least 8 characters';
                            }
                            if (!RegExp(r'[A-Z]').hasMatch(value)) {
                              return 'Password must contain at least one uppercase letter';
                            }
                            if (!RegExp(r'[0-9]').hasMatch(value)) {
                              return 'Password must contain at least one number';
                            }
                            return null;
                          },
                        ),
                        
                        const SizedBox(height: AppTheme.paddingMedium),
                        
                        CustomTextField(
                          label: 'Confirm Password',
                          hintText: 'Confirm your new password',
                          controller: _confirmPasswordController,
                          obscureText: !_isConfirmPasswordVisible,
                          prefixIcon: Icons.lock,
                          suffixIcon: _isConfirmPasswordVisible ? Icons.visibility : Icons.visibility_off,
                          onSuffixIconPressed: () {
                            setState(() {
                              _isConfirmPasswordVisible = !_isConfirmPasswordVisible;
                            });
                          },
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please confirm your password';
                            }
                            if (value != _passwordController.text) {
                              return 'Passwords do not match';
                            }
                            return null;
                          },
                        ),
                        
                        const SizedBox(height: AppTheme.paddingLarge),
                        
                        CustomButton(
                          text: 'Reset Password',
                          onPressed: _resetPassword,
                          type: ButtonType.primary,
                          isFullWidth: true,
                          size: ButtonSize.large,
                        ),
                      ],
                    ),
                  ),
                  
                  if (_errorMessage != null) ...[
                    const SizedBox(height: AppTheme.paddingMedium),
                    Text(
                      _errorMessage!,
                      style: const TextStyle(
                        color: Colors.red,
                        fontSize: AppTheme.fontSizeRegular,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                  
                  const SizedBox(height: AppTheme.paddingLarge),
                  
                  // Return to login
                  TextButton(
                    onPressed: () {
                      Navigator.pushReplacementNamed(context, AppRoutes.login);
                    },
                    child: Text(
                      'Return to Login',
                      style: TextStyle(
                        color: AppTheme.primaryColor,
                        fontSize: AppTheme.fontSizeRegular,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  String _getInstructionText() {
    switch (_currentStep) {
      case 0:
        return 'Enter your registered phone number and we\'ll send you an OTP to reset your password';
      case 1:
        return 'Enter the OTP code sent to your phone to verify your identity';
      case 2:
        return 'Create a new password for your account';
      default:
        return '';
    }
  }

  String _getLoadingMessage() {
    switch (_currentStep) {
      case 0:
        return 'Sending OTP...';
      case 1:
        return 'Verifying OTP...';
      case 2:
        return 'Resetting password...';
      default:
        return 'Loading...';
    }
  }
}