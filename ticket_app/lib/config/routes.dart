import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/auth/otp_verification_screen.dart';
import '../screens/home/home_screen.dart';
import '../screens/home/search_screen.dart';
import '../screens/home/ferry_details_screen.dart';
import '../screens/booking/ticket_count_screen.dart';
import '../screens/booking/vehicle_count_screen.dart';
import '../screens/booking/payment_screen.dart';
import '../screens/booking/booking_confirmation_screen.dart';
import '../screens/ticket/ticket_list_screen.dart';
import '../screens/ticket/ticket_detail_screen.dart';
import '../screens/ticket/qr_code_screen.dart';
import '../screens/profile/profile_screen.dart';
import '../screens/profile/edit_profile_screen.dart';
import '../screens/profile/transaction_history_screen.dart';
import '../screens/auth/forgot_password_screen.dart';
import '../widgets/auth_guard.dart';
import '../providers/auth_provider.dart';
import '../screens/route/routes_screen.dart';
import '../screens/ticket/qr_code_screen.dart';
// Import settings screen
import '../screens/settings/settings_screen.dart';

class AppRoutes {
  // Route names
  static const String login = '/login';
  static const String register = '/register';
  static const String otpVerification = '/otp-verification';
  static const String forgotPassword = '/forgot-password';
  static const String home = '/home';
  static const String search = '/search';
  static const String ferryDetails = '/ferry-details';
  static const String passengerDetails = '/passenger-details';
  static const String vehicleDetails = '/vehicle-details';
  static const String payment = '/payment';
  static const String bookingConfirmation = '/booking-confirmation';
  static const String ticketList = '/ticket-list';
  static const String ticketDetail = '/ticket-detail';
  static const String qrCode = '/qr-code';
  static const String profile = '/profile';
  static const String editProfile = '/edit-profile';
  static const String transactionHistory = '/transaction-history';
  static const String routes = '/routes';
  static const String ticketCount = '/ticket-count';
  // Tambahkan route settings
  static const String settings = '/settings';

  // Define which routes are public (not requiring authentication)
  static final List<String> publicRoutes = [
    login,
    register,
    otpVerification,
    forgotPassword,
  ];

  // Check if a route is protected
  static bool isProtectedRoute(String? routeName) {
    if (routeName == null) return false;
    return !publicRoutes.contains(routeName);
  }

  static Route<dynamic> onGenerateRoute(RouteSettings settings) {
    final routeName = settings.name;
    
    // Perbaikan: gunakan if-else alih-alih switch untuk non-constant pattern
    if (routeName == AppRoutes.home) {
      return MaterialPageRoute(
        builder: (context) => const HomeScreen(),
        settings: settings,
      );
    } else if (routeName == AppRoutes.routes) {
      return MaterialPageRoute(
        builder: (context) => const RoutesScreen(),
        settings: settings,
      );
    } else if (routeName == AppRoutes.settings) {
      return MaterialPageRoute(
        builder: (context) => const SettingsScreen(),
        settings: settings,
      );
    } else if (routeName == AppRoutes.login) {
      return MaterialPageRoute(
        builder: (context) => const LoginScreen(),
        settings: settings,
      );
    } else {
      // Default case
      // Handle route protection
      if (isProtectedRoute(routeName)) {
        return MaterialPageRoute(
          builder: (context) => AuthGuard(child: _buildRoute(settings, context)),
          settings: settings,
        );
      }

      // For public routes, don't use AuthGuard
      return MaterialPageRoute(
        builder: (context) => _buildRoute(settings, context),
        settings: settings,
      );
    }
  }

  // Extract the actual route building logic to a separate method
  static Widget _buildRoute(RouteSettings settings, BuildContext context) {
    final routeName = settings.name;
    
    // Perbaikan: gunakan if-else alih-alih switch
    if (routeName == login) {
      return const LoginScreen();
    } else if (routeName == register) {
      return const RegisterScreen();
    } else if (routeName == otpVerification) {
      final args = settings.arguments as Map<String, dynamic>?;
      return OtpVerificationScreen(phoneNumber: args?['phoneNumber'] ?? '');
    } else if (routeName == forgotPassword) {
      return const ForgotPasswordScreen();
    } else if (routeName == home) {
      return const HomeScreen();
    } else if (routeName == settings) {
      return const SettingsScreen();
    } else if (routeName == search) {
      final args = settings.arguments as Map<String, dynamic>?;
      return SearchScreen(
        departurePort: args?['departurePort'],
        arrivalPort: args?['arrivalPort'],
        departureDate: args?['departureDate'],
      );
    } else if (routeName == ferryDetails) {
      final args = settings.arguments as Map<String, dynamic>;
      return FerryDetailsScreen(scheduleId: args['scheduleId']);
    } else if (routeName == passengerDetails) {
      final args = settings.arguments as Map<String, dynamic>?;
      return TicketCountScreen(
        scheduleId: args?['scheduleId'],
        hasVehicle: args?['hasVehicle'] ?? false,
      );
    } else if (routeName == vehicleDetails) {
      final args = settings.arguments as Map<String, dynamic>?;

      // Validasi data
      if (args == null ||
          args['scheduleId'] == null ||
          args['ticketCount'] == null) {
        return const Scaffold(
          body: Center(
            child: Text(
              'Data tidak lengkap untuk halaman ini. Silakan kembali dan coba lagi.',
            ),
          ),
        );
      }

      // Pastikan nilai yang diterima adalah int
      final int scheduleId =
          args['scheduleId'] is int
              ? args['scheduleId']
              : int.tryParse(args['scheduleId'].toString()) ?? 0;

      final int ticketCount =
          args['ticketCount'] is int
              ? args['ticketCount']
              : int.tryParse(args['ticketCount'].toString()) ?? 0;

      if (scheduleId <= 0 || ticketCount <= 0) {
        return const Scaffold(
          body: Center(
            child: Text(
              'Data tidak valid untuk halaman ini. Silakan kembali dan coba lagi.',
            ),
          ),
        );
      }

      return VehicleCountScreen(
        scheduleId: scheduleId,
        ticketCount: ticketCount,
      );
    } else if (routeName == payment) {
      final args = settings.arguments as Map<String, dynamic>?;
      if (args == null || args['bookingId'] == null) {
        return const Scaffold(
          body: Center(
            child: Text('Data pemesanan tidak valid. Silakan coba lagi.'),
          ),
        );
      }

      // Pastikan bookingId adalah int
      final int bookingId =
          args['bookingId'] is int
              ? args['bookingId']
              : int.tryParse(args['bookingId'].toString()) ?? 0;

      if (bookingId <= 0) {
        return const Scaffold(
          body: Center(
            child: Text('ID pemesanan tidak valid. Silakan coba lagi.'),
          ),
        );
      }

      return PaymentScreen(
        bookingId: bookingId,
        totalAmount: args['totalAmount'] ?? 0,
      );
    } else if (routeName == bookingConfirmation) {
      final args = settings.arguments as Map<String, dynamic>?;
      if (args == null || args['bookingId'] == null) {
        return const Scaffold(
          body: Center(
            child: Text('Data pemesanan tidak valid. Silakan coba lagi.'),
          ),
        );
      }
      return BookingConfirmationScreen(bookingId: args['bookingId']);
    } else if (routeName == ticketList) {
      return const TicketListScreen();
    } else if (routeName == ticketDetail) {
      final args = settings.arguments as Map<String, dynamic>;
      return TicketDetailScreen(ticketId: args['ticketId']);
    } else if (routeName == qrCode) {
      final args = settings.arguments as Map<String, dynamic>;
      return QrCodeScreen(ticketId: args['ticketId']);
    } else if (routeName == profile) {
      return const ProfileScreen();
    } else if (routeName == editProfile) {
      return const EditProfileScreen();
    } else if (routeName == transactionHistory) {
      return const TransactionHistoryScreen();
    } else {
      return const Scaffold(body: Center(child: Text('Route not found!')));
    }
  }

  // Navigation helper to check auth state and redirect if needed
  static Future<void> navigateTo(
    BuildContext context,
    String routeName, {
    Object? arguments,
  }) async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    // If trying to access a protected route
    if (isProtectedRoute(routeName)) {
      // Check if user is authenticated and verified
      final isLoggedIn = await authProvider.isLoggedIn();

      if (!isLoggedIn) {
        // Not logged in at all - go to login
        Navigator.pushNamedAndRemoveUntil(context, login, (route) => false);
        return;
      }

      if (authProvider.isRegisteredButNotVerified) {
        // Logged in but not verified - go to OTP verification
        Navigator.pushReplacementNamed(
          context,
          otpVerification,
          arguments: {'phoneNumber': authProvider.user?.phone ?? ''},
        );
        return;
      }
    }

    // Normal navigation if auth checks pass
    Navigator.pushNamed(context, routeName, arguments: arguments);
  }
}