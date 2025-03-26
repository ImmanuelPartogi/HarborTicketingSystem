import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/auth/otp_verification_screen.dart';
import '../screens/home/home_screen.dart';
import '../screens/home/search_screen.dart';
import '../screens/home/ferry_details_screen.dart';
import '../screens/booking/passenger_details_screen.dart';
import '../screens/booking/vehicle_details_screen.dart';
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
    switch (settings.name) {
      case AppRoutes.home:
        return MaterialPageRoute(
          builder: (context) => const HomeScreen(),
          settings: settings,
        );

      case AppRoutes.routes:
        return MaterialPageRoute(
          builder: (context) => const RoutesScreen(),
          settings: settings,
        );

      // Tambahkan case untuk rute lainnya
      case AppRoutes.login:
        return MaterialPageRoute(
          builder: (context) => const LoginScreen(),
          settings: settings,
        );

      // Default case
      default:
        // Handle route protection
        if (isProtectedRoute(settings.name)) {
          return MaterialPageRoute(
            builder:
                (context) => AuthGuard(child: _buildRoute(settings, context)),
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
    switch (settings.name) {
      case login:
        return const LoginScreen();

      case register:
        return const RegisterScreen();

      case otpVerification:
        final args = settings.arguments as Map<String, dynamic>?;
        return OtpVerificationScreen(phoneNumber: args?['phoneNumber'] ?? '');

      case forgotPassword:
        return const ForgotPasswordScreen();

      case home:
        return const HomeScreen();

      case search:
        final args = settings.arguments as Map<String, dynamic>?;
        return SearchScreen(
          departurePort: args?['departurePort'],
          arrivalPort: args?['arrivalPort'],
          departureDate: args?['departureDate'],
        );

      case ferryDetails:
        final args = settings.arguments as Map<String, dynamic>;
        return FerryDetailsScreen(scheduleId: args['scheduleId']);

      case passengerDetails:
        final args = settings.arguments as Map<String, dynamic>;
        return PassengerDetailsScreen(
          scheduleId: args['scheduleId'],
          passengerCount: args['passengerCount'],
          hasVehicle: args['hasVehicle'] ?? false,
        );

      case vehicleDetails:
        final args = settings.arguments as Map<String, dynamic>;
        return VehicleDetailsScreen(
          scheduleId: args['scheduleId'],
          passengerIds: args['passengerIds'],
        );

      case payment:
        final args = settings.arguments as Map<String, dynamic>;
        return PaymentScreen(
          bookingId: args['bookingId'],
          totalAmount: args['totalAmount'],
        );

      case bookingConfirmation:
        final args = settings.arguments as Map<String, dynamic>;
        return BookingConfirmationScreen(bookingId: args['bookingId']);

      case ticketList:
        return const TicketListScreen();

      case ticketDetail:
        final args = settings.arguments as Map<String, dynamic>;
        return TicketDetailScreen(ticketId: args['ticketId']);

      case qrCode:
        final args = settings.arguments as Map<String, dynamic>;
        return QrCodeScreen(ticketId: args['ticketId']);

      case profile:
        return const ProfileScreen();

      case editProfile:
        return const EditProfileScreen();

      case transactionHistory:
        return const TransactionHistoryScreen();

      default:
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
