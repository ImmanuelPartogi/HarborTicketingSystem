import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/routes.dart';
import '../providers/auth_provider.dart';

class AuthGuard extends StatefulWidget {
  final Widget child;
  final bool requireVerification;

  const AuthGuard({
    Key? key,
    required this.child,
    this.requireVerification = true,
  }) : super(key: key);

  @override
  State<AuthGuard> createState() => _AuthGuardState();
}

class _AuthGuardState extends State<AuthGuard> {
  bool _isChecking = false; // Flag untuk mencegah pengecekkan berulang

  @override
  void initState() {
    super.initState();
    // Check authentication after the widget is built
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkAuth();
    });
  }

  Future<void> _checkAuth() async {
    if (_isChecking || !mounted) return; // Hindari pengecekkan berulang
    
    _isChecking = true;
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    
    // Cek apakah perlu mengambil user data dari server 
    // Jika sudah authenticated, tidak perlu mengambil ulang kecuali data sudah kadaluarsa
    final needsProfileRefresh = !authProvider.isAuthenticated || 
                               authProvider.lastProfileFetchTime == null ||
                               DateTime.now().difference(authProvider.lastProfileFetchTime!).inMinutes > 5;
    
    if (needsProfileRefresh) {
      await authProvider.getCurrentUser();
    }
    
    if (!authProvider.isAuthenticated) {
      if (authProvider.isRegisteredButNotVerified && widget.requireVerification) {
        // User is registered but not verified, redirect to OTP screen
        final phone = authProvider.user?.phone;
        if (phone != null && mounted) {
          Navigator.of(context).pushReplacementNamed(
            AppRoutes.otpVerification,
            arguments: {'phoneNumber': phone},
          );
        } else if (mounted) {
          // If we don't have the phone, go to login
          Navigator.of(context).pushReplacementNamed(AppRoutes.login);
        }
      } else if (mounted) {
        // Not authenticated at all, go to login
        Navigator.of(context).pushReplacementNamed(AppRoutes.login);
      }
    }
    
    _isChecking = false;
  }
  
  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, _) {
        // Jangan lakukan rebuild jika sedang melakukan pengecekan
        if (_isChecking) {
          return const Scaffold(
            body: Center(
              child: CircularProgressIndicator(),
            ),
          );
        }
        
        // For pages that require verification
        if (widget.requireVerification && !authProvider.isAuthenticated) {
          // Show loading indicator while checking auth status
          return const Scaffold(
            body: Center(
              child: CircularProgressIndicator(),
            ),
          );
        }
        
        // User is authenticated and verified, or verification not required
        return widget.child;
      },
    );
  }
}