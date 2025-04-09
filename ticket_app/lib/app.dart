import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'config/routes.dart';
import 'config/theme.dart';
import 'providers/auth_provider.dart';
// Import ThemeProvider
import 'providers/theme_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/otp_verification_screen.dart';
import 'screens/home/home_screen.dart';
import 'widgets/auth_guard.dart';

class FerryTicketApp extends StatelessWidget {
  const FerryTicketApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // Gunakan Consumer untuk ThemeProvider
    return Consumer2<AuthProvider, ThemeProvider>(
      builder: (_, authProvider, themeProvider, __) {
        return MaterialApp(
          title: 'Ferry Ticket App',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.lightTheme,
          darkTheme: AppTheme.darkTheme,
          // Gunakan themeMode dari ThemeProvider
          themeMode: themeProvider.themeMode,
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          supportedLocales: const [Locale('en', ''), Locale('id', '')],
          home: FutureBuilder<bool>(
            future: authProvider.isLoggedIn(),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const Scaffold(
                  body: Center(child: CircularProgressIndicator()),
                );
              }

              final isLoggedIn = snapshot.data ?? false;

              // Tambahkan flag untuk mencegah redirect berulang
              // Check if user is registered but not verified
              if (authProvider.isRegisteredButNotVerified) {
                // Redirect to OTP verification
                return OtpVerificationScreen(
                  phoneNumber: authProvider.user?.phone ?? '',
                );
              }

              // Jika properti isAuthenticated berubah, Widget akan di-rebuild
              // If properly logged in, show home screen with auth guard
              if (isLoggedIn) {
                return const AuthGuard(child: HomeScreen());
              }

              // Not logged in, show login screen
              return const LoginScreen();
            },
          ),
          onGenerateRoute: (settings) {
            // Get the list of public routes
            final publicRoutes = [
              AppRoutes.login,
              AppRoutes.register,
              AppRoutes.otpVerification,
              AppRoutes.forgotPassword,
            ];

            // Check if route needs to be protected
            final isProtected =
                settings.name != null && !publicRoutes.contains(settings.name);

            // Create the appropriate route
            if (isProtected) {
              // For protected routes, create a new route with AuthGuard
              switch (settings.name) {
                case AppRoutes.home:
                  return MaterialPageRoute(
                    builder: (context) => const AuthGuard(child: HomeScreen()),
                    settings: settings,
                  );
                // Add other protected routes as cases...
                default:
                  // Create a fallback route for unknown routes
                  final originalRoute = AppRoutes.onGenerateRoute(settings);
                  // For unknown routes, we can't wrap with AuthGuard since we don't know the widget
                  return originalRoute;
              }
            } else {
              // For public routes, just use the default route generator
              return AppRoutes.onGenerateRoute(settings);
            }
          },
        );
      },
    );
  }
}