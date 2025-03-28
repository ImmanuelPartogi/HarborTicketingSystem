class AppConfig {
  // API configuration - UPDATED FOR DIFFERENT ENVIRONMENTS
  
  // For Android Emulator:
  // static const String apiBaseUrl = 'http://10.0.2.2:8000';
  
  // For iOS Simulator (uncomment if needed):
  static const String apiBaseUrl = 'http://localhost:8000';
  
  // For Physical Device (uncomment and update IP):
  // static const String apiBaseUrl = 'http://192.168.1.x:8000'; // Replace with your PC's IP
  
  static const String apiPrefix = '/api/v1';
  static const int apiConnectTimeout = 30000; // 30 seconds
  static const int apiReceiveTimeout = 30000; // 30 seconds

  // The rest of your AppConfig remains unchanged
  static const String appName = 'Ferry Ticket App';
  static const String appVersion = '1.0.0';
  
  // Feature flags
  static const bool enablePushNotifications = true;
  static const bool enableInAppReview = true;
  static const bool enableBiometricLogin = true;

  // Timeout values
  static const int sessionTimeoutMinutes = 30;
  static const int paymentTimeoutMinutes = 15;

  // Animation durations
  static const int shortAnimationDuration = 200; // milliseconds
  static const int mediumAnimationDuration = 400; // milliseconds
  static const int longAnimationDuration = 800; // milliseconds

  // Default values
  static const int ticketWatermarkRefreshInterval = 30; // seconds
  static const int ticketExpiryMinutesAfterDeparture = 30;

  // Cache TTL values
  static const int scheduleCacheTTLMinutes = 5;
  static const int routeCacheTTLHours = 24;
  static const int ferryCacheTTLHours = 24;
}