import 'app_config.dart';

class ApiConfig {
  static const String baseUrl = AppConfig.apiBaseUrl;
  static const int connectTimeout = AppConfig.apiConnectTimeout;
  static const int receiveTimeout = AppConfig.apiReceiveTimeout;

  // Endpoint API
  static const String login = '/api/v1/login';
  static const String register = '/api/v1/register';
  static const String verifyOtp = '/api/v1/verify-otp';
  static const String refreshToken = '/api/v1/refresh-token';
  static const String logout = '/api/v1/logout';

  // Endpoint profil
  static const String profile = '/api/v1/profile';
  static const String updateProfile = '/api/v1/profile';
  static const String changePassword = '/api/v1/change-password';

  // Endpoint lainnya
  static const String ferries = '/api/v1/ferries';
  static const String routes = '/api/v1/routes';
  static const String schedules = '/api/v1/schedules';

  static const String bookings = '/api/v1/bookings';
  // Diperbaiki untuk menggunakan format /id/
  static const String bookingDetail = '/api/v1/bookings/id/{id}';
  // Diperbaiki jika cancelBooking dan rescheduleBooking menggunakan ID numerik
  static const String cancelBooking = '/api/v1/bookings/id/{id}/cancel';
  static const String rescheduleBooking = '/api/v1/bookings/id/{id}/reschedule';
  static const String generateTickets =
      '/api/v1/bookings/id/{id}/generate-tickets';

  static const String payments = '/api/v1/payments';
  // Diperbaiki untuk menggunakan endpoint yang benar
  static const String paymentStatus = '/api/v1/bookings/id/{id}/payment-status';

  static const String tickets = '/api/v1/tickets';
  static const String ticketDetail = '/api/v1/tickets/{id}';
  static const String validateTicket = '/api/v1/tickets/{id}/validate';
  static const String groupedTickets = '/tickets/grouped';

  static const String notifications = '/api/v1/notifications';

  // Kode Status HTTP dan header tidak perlu diubah
  static const int statusOk = 200;
  static const int statusCreated = 201;
  static const int statusNoContent = 204;
  static const int statusBadRequest = 400;
  static const int statusUnauthorized = 401;
  static const int statusForbidden = 403;
  static const int statusNotFound = 404;
  static const int statusInternalServerError = 500;

  static const String authHeader = 'Authorization';
  static const String contentTypeHeader = 'Content-Type';
  static const String acceptHeader = 'Accept';
  static const String acceptLanguageHeader = 'Accept-Language';

  static const String jsonContentType = 'application/json';
  static const String bearerPrefix = 'Bearer ';
}
