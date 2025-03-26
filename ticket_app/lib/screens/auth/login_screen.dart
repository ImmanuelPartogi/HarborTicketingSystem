import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/custom_text_field.dart';
import '../../widgets/common/loading_indicator.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _rememberMe = false;
  bool _isPasswordVisible = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (_formKey.currentState!.validate()) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      
      final success = await authProvider.login(
        _emailController.text.trim(),
        _passwordController.text,
      );
      
      if (success && mounted) {
        // Check if the user is verified
        if (authProvider.isAuthenticated) {
          // User is authenticated and verified, navigate to home
          Navigator.pushReplacementNamed(context, AppRoutes.home);
        } else if (authProvider.isRegisteredButNotVerified) {
          // User is registered but not verified, navigate to OTP verification
          Navigator.pushReplacementNamed(
            context, 
            AppRoutes.otpVerification,
            arguments: {'phoneNumber': authProvider.user?.phone ?? ''},
          );
        }
      } else if (mounted) {
        // Login failed
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(authProvider.error ?? 'Login failed'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _goToRegister() {
    Navigator.pushNamed(context, AppRoutes.register);
  }
  
  void _goToForgotPassword() {
    Navigator.pushNamed(context, AppRoutes.forgotPassword);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, _) {
          return LoadingOverlay(
            isLoading: authProvider.isLoading,
            loadingMessage: 'Logging in...',
            child: SafeArea(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppTheme.paddingLarge),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const SizedBox(height: AppTheme.paddingXLarge),
                      
                      // Logo and App Name
                      Center(
                        child: Column(
                          children: [
                            Icon(
                              Icons.directions_boat,
                              size: 80,
                              color: AppTheme.primaryColor,
                            ),
                            const SizedBox(height: AppTheme.paddingRegular),
                            Text(
                              'Ferry Ticket App',
                              style: TextStyle(
                                fontSize: AppTheme.fontSizeXXLarge,
                                fontWeight: FontWeight.bold,
                                color: theme.textTheme.displayLarge?.color,
                              ),
                            ),
                            const SizedBox(height: AppTheme.paddingSmall),
                            Text(
                              'Book your ferry tickets with ease',
                              style: TextStyle(
                                fontSize: AppTheme.fontSizeMedium,
                                color: theme.textTheme.bodyMedium?.color,
                              ),
                            ),
                          ],
                        ),
                      ),
                      
                      const SizedBox(height: AppTheme.paddingXXLarge),
                      
                      // Login Form
                      Text(
                        'Login to your account',
                        style: TextStyle(
                          fontSize: AppTheme.fontSizeLarge,
                          fontWeight: FontWeight.w600,
                          color: theme.textTheme.displaySmall?.color,
                        ),
                      ),
                      const SizedBox(height: AppTheme.paddingLarge),
                      
                      // Email Field
                      CustomTextField(
                        label: 'Email',
                        hintText: 'Enter your email address',
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        prefixIcon: Icons.email,
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter your email address';
                          }
                          // Simple email validation
                          if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(value)) {
                            return 'Please enter a valid email address';
                          }
                          return null;
                        },
                      ),
                      
                      const SizedBox(height: AppTheme.paddingMedium),
                      
                      // Password Field
                      CustomTextField(
                        label: 'Password',
                        hintText: 'Enter your password',
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
                            return 'Please enter your password';
                          }
                          if (value.length < 6) {
                            return 'Password must be at least 6 characters';
                          }
                          return null;
                        },
                      ),
                      
                      const SizedBox(height: AppTheme.paddingRegular),
                      
                      // Remember Me and Forgot Password
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Checkbox(
                                value: _rememberMe,
                                onChanged: (value) {
                                  setState(() {
                                    _rememberMe = value ?? false;
                                  });
                                },
                                activeColor: AppTheme.primaryColor,
                              ),
                              Text(
                                'Remember Me',
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                  color: theme.textTheme.bodyMedium?.color,
                                ),
                              ),
                            ],
                          ),
                          TextButton(
                            onPressed: _goToForgotPassword,
                            child: Text(
                              'Forgot Password?',
                              style: TextStyle(
                                color: AppTheme.primaryColor,
                                fontSize: AppTheme.fontSizeRegular,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                      
                      const SizedBox(height: AppTheme.paddingLarge),
                      
                      // Login Button
                      CustomButton(
                        text: 'Login',
                        onPressed: _login,
                        type: ButtonType.primary,
                        isFullWidth: true,
                        size: ButtonSize.large,
                      ),
                      
                      const SizedBox(height: AppTheme.paddingLarge),
                      
                      // Register Link
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            "Don't have an account? ",
                            style: TextStyle(
                              fontSize: AppTheme.fontSizeRegular,
                              color: theme.textTheme.bodyMedium?.color,
                            ),
                          ),
                          TextButton(
                            onPressed: _goToRegister,
                            child: Text(
                              'Register Now',
                              style: TextStyle(
                                color: AppTheme.primaryColor,
                                fontSize: AppTheme.fontSizeRegular,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}