// Di ApiConfig.dart

class ApiConfig {
  // Base URL - Pastikan tidak ada trailing slash
  static const String baseUrl = 'http://localhost:8000';
  
  // Auth endpoints
  static const String login = '/api/v1/auth/login';
  static const String register = '/api/v1/auth/register';
  static const String verifyOtp = '/api/v1/auth/verify-otp';
  static const String refreshToken = '/api/v1/auth/refresh';
  static const String logout = '/api/v1/auth/logout';
  
  // User profile endpoints
  static const String profile = '/api/v1/user/profile';
  static const String updateProfile = '/api/v1/user/profile';
  static const String changePassword = '/api/v1/user/password';
  
  // Ferry endpoints
  static const String ferries = '/api/v1/ferries';
  
  // Route endpoints
  static const String routes = '/api/v1/routes';
  
  // Schedule endpoints
  static const String schedules = '/api/v1/schedules';
  
  // Booking endpoints
  static const String bookings = '/api/v1/bookings';
  static const String cancelBooking = '/api/v1/bookings/{id}/cancel';
  static const String rescheduleBooking = '/api/v1/bookings/{id}/reschedule';
  static const String generateTickets = '/api/v1/bookings/{id}/generate-tickets';
  
  // Payment endpoints
  static const String payments = '/api/v1/payments';
  static const String paymentStatus = '/api/v1/payments/{id}/status';
  
  // Ticket endpoints
  static const String tickets = '/api/v1/tickets';
  static const String ticketDetail = '/api/v1/tickets/{id}';
  static const String validateTicket = '/api/v1/tickets/{id}/validate';
  
  // Notification endpoints
  static const String notifications = '/api/v1/notifications';
  
  // HTTP Headers
  static const String contentTypeHeader = 'Content-Type';
  static const String acceptHeader = 'Accept';
  static const String authHeader = 'Authorization';
  static const String bearerPrefix = 'Bearer ';
  static const String jsonContentType = 'application/json';
  
  // HTTP Status Codes
  static const int statusOk = 200;
  static const int statusCreated = 201;
  static const int statusNoContent = 204;
  static const int statusBadRequest = 400;
  static const int statusUnauthorized = 401;
  static const int statusForbidden = 403;
  static const int statusNotFound = 404;
  static const int statusInternalServerError = 500;
  
  // Timeout settings (in milliseconds)
  static const int connectTimeout = 30000; // 30 seconds
  static const int receiveTimeout = 30000; // 30 seconds
  
  // Retry settings
  static const int maxRetries = 3;
  static const int retryInterval = 2000; // 2 seconds
  
  // Throttling settings
  static const int minRequestInterval = 30; // 30 seconds between same endpoint requests
}