import 'app_config.dart';

class ApiConfig {
  static const String baseUrl = AppConfig.apiBaseUrl;
  static const int connectTimeout = AppConfig.apiConnectTimeout;
  static const int receiveTimeout = AppConfig.apiReceiveTimeout;
  
  // API endpoints
  static const String login = '/api/auth/login';
  static const String register = '/api/auth/register';
  static const String verifyOtp = '/api/auth/verify-otp';
  static const String refreshToken = '/api/auth/refresh-token';
  static const String logout = '/api/auth/logout';
  
  static const String profile = '/api/profile';
  static const String updateProfile = '/api/profile/update';
  static const String changePassword = '/api/profile/change-password';
  
  static const String ferries = '/api/ferries';
  static const String routes = '/api/routes';
  static const String schedules = '/api/schedules';
  
  static const String bookings = '/api/bookings';
  static const String bookingDetail = '/api/bookings/{id}';
  static const String cancelBooking = '/api/bookings/{id}/cancel';
  static const String rescheduleBooking = '/api/bookings/{id}/reschedule';
  
  static const String payments = '/api/payments';
  static const String paymentStatus = '/api/payments/{id}/status';
  
  static const String tickets = '/api/tickets';
  static const String ticketDetail = '/api/tickets/{id}';
  static const String validateTicket = '/api/tickets/{id}/validate';
  
  static const String notifications = '/api/notifications';
  
  // HTTP Status Codes
  static const int statusOk = 200;
  static const int statusCreated = 201;
  static const int statusNoContent = 204;
  static const int statusBadRequest = 400;
  static const int statusUnauthorized = 401;
  static const int statusForbidden = 403;
  static const int statusNotFound = 404;
  static const int statusInternalServerError = 500;
  
  // Header keys
  static const String authHeader = 'Authorization';
  static const String contentTypeHeader = 'Content-Type';
  static const String acceptHeader = 'Accept';
  static const String acceptLanguageHeader = 'Accept-Language';
  
  // Header values
  static const String jsonContentType = 'application/json';
  static const String bearerPrefix = 'Bearer ';
}