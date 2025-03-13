/// Constants used throughout the app
class Constants {
  /// App-related constants
  static const String appName = 'Ferry Ticket App';
  static const String appVersion = '1.0.0';
  
  /// API endpoints
  static const String apiBaseUrl = 'https://api.ferryticket.com';
  
  /// Storage keys
  static const String storageUserKey = 'user';
  static const String storageAccessTokenKey = 'access_token';
  static const String storageRefreshTokenKey = 'refresh_token';
  static const String storageTokenExpiryKey = 'token_expiry';
  static const String storageThemeModeKey = 'theme_mode';
  static const String storageLanguageCodeKey = 'language_code';
  static const String storageRecentSearchesKey = 'recent_searches';
  static const String storageSavedPassengersKey = 'saved_passengers';
  static const String storageSavedVehiclesKey = 'saved_vehicles';
  
  /// Timeout durations
  static const int apiConnectTimeoutInSeconds = 30;
  static const int apiReceiveTimeoutInSeconds = 30;
  static const int sessionTimeoutInMinutes = 30;
  static const int paymentTimeoutInMinutes = 15;
  
  /// Authentication
  static const int otpCodeLength = 6;
  static const int otpResendTimeInSeconds = 60;
  static const int passwordMinLength = 8;
  
  /// Booking
  static const int maxPassengers = 50;
  static const int ticketExpiryAfterDepartureInMinutes = 30;
  
  /// UI related
  static const double splashLogoSize = 150.0;
  static const int splashScreenDurationInSeconds = 2;
  
  /// Animation durations
  static const int shortAnimationDurationInMillis = 200;
  static const int mediumAnimationDurationInMillis = 400;
  static const int longAnimationDurationInMillis = 800;
  
  /// Vehicle types
  static const List<Map<String, String>> vehicleTypes = [
    {'id': 'car', 'name': 'Car', 'description': 'Sedan, SUV, etc.'},
    {'id': 'motorcycle', 'name': 'Motorcycle', 'description': 'All types'},
    {'id': 'bus', 'name': 'Bus', 'description': 'Minibus, Standard Bus'},
    {'id': 'truck', 'name': 'Truck', 'description': 'Small truck, pickup, etc.'},
  ];
  
  /// Identity types
  static const List<Map<String, String>> identityTypes = [
    {'id': 'ktp', 'name': 'KTP', 'description': 'Kartu Tanda Penduduk'},
    {'id': 'sim', 'name': 'SIM', 'description': 'Surat Izin Mengemudi'},
    {'id': 'passport', 'name': 'Passport', 'description': 'International Passport'},
  ];
  
  /// Gender options
  static const List<Map<String, String>> genderOptions = [
    {'id': 'm', 'name': 'Male'},
    {'id': 'f', 'name': 'Female'},
  ];
  
  /// Payment types
  static const List<Map<String, String>> paymentTypes = [
    {'id': 'bank_transfer', 'name': 'Bank Transfer', 'description': 'Transfer to bank account'},
    {'id': 'e_wallet', 'name': 'E-Wallet', 'description': 'Pay using e-wallet'},
    {'id': 'virtual_account', 'name': 'Virtual Account', 'description': 'Pay using virtual account'},
  ];
  
  /// Payment methods
  static const List<Map<String, dynamic>> paymentMethods = [
    {
      'id': 'bni',
      'name': 'BNI',
      'type': 'bank_transfer',
      'icon': 'assets/images/payment_methods/bni.png',
    },
    {
      'id': 'bri',
      'name': 'BRI',
      'type': 'bank_transfer',
      'icon': 'assets/images/payment_methods/bri.png',
    },
    {
      'id': 'mandiri',
      'name': 'Mandiri',
      'type': 'bank_transfer',
      'icon': 'assets/images/payment_methods/mandiri.png',
    },
    {
      'id': 'dana',
      'name': 'DANA',
      'type': 'e_wallet',
      'icon': 'assets/images/payment_methods/dana.png',
    },
    {
      'id': 'ovo',
      'name': 'OVO',
      'type': 'e_wallet',
      'icon': 'assets/images/payment_methods/ovo.png',
    },
  ];
  
  /// Booking statuses
  static const List<Map<String, String>> bookingStatuses = [
    {'id': 'pending', 'name': 'Pending Payment'},
    {'id': 'confirmed', 'name': 'Confirmed'},
    {'id': 'cancelled', 'name': 'Cancelled'},
    {'id': 'completed', 'name': 'Completed'},
    {'id': 'refunded', 'name': 'Refunded'},
  ];
  
  /// Ticket statuses
  static const List<Map<String, String>> ticketStatuses = [
    {'id': 'active', 'name': 'Active'},
    {'id': 'used', 'name': 'Used'},
    {'id': 'expired', 'name': 'Expired'},
    {'id': 'cancelled', 'name': 'Cancelled'},
  ];
  
  /// Schedule statuses
  static const List<Map<String, String>> scheduleStatuses = [
    {'id': 'scheduled', 'name': 'On Schedule'},
    {'id': 'delayed', 'name': 'Delayed'},
    {'id': 'cancelled', 'name': 'Cancelled'},
    {'id': 'departed', 'name': 'Departed'},
    {'id': 'arrived', 'name': 'Arrived'},
  ];
  
  /// Validation error messages
  static const String errorRequiredField = 'This field is required';
  static const String errorInvalidEmail = 'Please enter a valid email address';
  static const String errorInvalidPhone = 'Please enter a valid phone number';
  static const String errorInvalidDate = 'Please enter a valid date (YYYY-MM-DD)';
  static const String errorPasswordsDoNotMatch = 'Passwords do not match';
  static const String errorPasswordTooShort = 'Password must be at least 8 characters';
  
  /// Popular ports
  static const List<Map<String, String>> popularPorts = [
    {'id': 'ajibata', 'name': 'Ajibata'},
    {'id': 'tomok', 'name': 'Tomok'},
    {'id': 'parapat', 'name': 'Parapat'},
    {'id': 'simanindo', 'name': 'Simanindo'},
    {'id': 'balige', 'name': 'Balige'},
  ];
  
  /// Popular routes
  static const List<Map<String, String>> popularRoutes = [
    {'departure': 'Ajibata', 'arrival': 'Tomok'},
    {'departure': 'Tomok', 'arrival': 'Ajibata'},
    {'departure': 'Parapat', 'arrival': 'Simanindo'},
    {'departure': 'Simanindo', 'arrival': 'Parapat'},
    {'departure': 'Balige', 'arrival': 'Tomok'},
  ];
}