import 'package:flutter/material.dart';

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

class AppRoutes {
  // Route names
  static const String login = '/login';
  static const String register = '/register';
  static const String otpVerification = '/otp-verification';
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

  static Route<dynamic> onGenerateRoute(RouteSettings settings) {
    switch (settings.name) {
      case login:
        return MaterialPageRoute(
          builder: (_) => const LoginScreen(),
          settings: settings,
        );
      case register:
        return MaterialPageRoute(
          builder: (_) => const RegisterScreen(),
          settings: settings,
        );
      case otpVerification:
        final args = settings.arguments as Map<String, dynamic>?;
        return MaterialPageRoute(
          builder: (_) => OtpVerificationScreen(
            phoneNumber: args?['phoneNumber'] ?? '',
          ),
          settings: settings,
        );
      case home:
        return MaterialPageRoute(
          builder: (_) => const HomeScreen(),
          settings: settings,
        );
      case search:
        final args = settings.arguments as Map<String, dynamic>?;
        return MaterialPageRoute(
          builder: (_) => SearchScreen(
            departurePort: args?['departurePort'],
            arrivalPort: args?['arrivalPort'],
            departureDate: args?['departureDate'],
          ),
          settings: settings,
        );
      case ferryDetails:
        final args = settings.arguments as Map<String, dynamic>;
        return MaterialPageRoute(
          builder: (_) => FerryDetailsScreen(
            scheduleId: args['scheduleId'],
          ),
          settings: settings,
        );
      case passengerDetails:
        final args = settings.arguments as Map<String, dynamic>;
        return MaterialPageRoute(
          builder: (_) => PassengerDetailsScreen(
            scheduleId: args['scheduleId'],
            passengerCount: args['passengerCount'],
            hasVehicle: args['hasVehicle'] ?? false,
          ),
          settings: settings,
        );
      case vehicleDetails:
        final args = settings.arguments as Map<String, dynamic>;
        return MaterialPageRoute(
          builder: (_) => VehicleDetailsScreen(
            scheduleId: args['scheduleId'],
            passengerIds: args['passengerIds'],
          ),
          settings: settings,
        );
      case payment:
        final args = settings.arguments as Map<String, dynamic>;
        return MaterialPageRoute(
          builder: (_) => PaymentScreen(
            bookingId: args['bookingId'],
            totalAmount: args['totalAmount'],
          ),
          settings: settings,
        );
      case bookingConfirmation:
        final args = settings.arguments as Map<String, dynamic>;
        return MaterialPageRoute(
          builder: (_) => BookingConfirmationScreen(
            bookingId: args['bookingId'],
          ),
          settings: settings,
        );
      case ticketList:
        return MaterialPageRoute(
          builder: (_) => const TicketListScreen(),
          settings: settings,
        );
      case ticketDetail:
        final args = settings.arguments as Map<String, dynamic>;
        return MaterialPageRoute(
          builder: (_) => TicketDetailScreen(
            ticketId: args['ticketId'],
          ),
          settings: settings,
        );
      case qrCode:
        final args = settings.arguments as Map<String, dynamic>;
        return MaterialPageRoute(
          builder: (_) => QrCodeScreen(
            ticketId: args['ticketId'],
          ),
          settings: settings,
        );
      case profile:
        return MaterialPageRoute(
          builder: (_) => const ProfileScreen(),
          settings: settings,
        );
      case editProfile:
        return MaterialPageRoute(
          builder: (_) => const EditProfileScreen(),
          settings: settings,
        );
      case transactionHistory:
        return MaterialPageRoute(
          builder: (_) => const TransactionHistoryScreen(),
          settings: settings,
        );
      default:
        return MaterialPageRoute(
          builder: (_) => const Scaffold(
            body: Center(
              child: Text('Route not found!'),
            ),
          ),
          settings: settings,
        );
    }
  }
}