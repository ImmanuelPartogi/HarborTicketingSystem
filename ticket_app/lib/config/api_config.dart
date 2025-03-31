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

  // Endpoint profil - DIPERBAIKI agar sesuai dengan rute Laravel
  static const String profile = '/api/v1/profile';
  // ⚠️ Menghapus '/api/v1/profile/update' yang salah dan menggunakan '/api/v1/profile' untuk update
  static const String updateProfile = '/api/v1/profile';
  static const String changePassword =
      '/api/v1/change-password'; // Juga diperbarui agar sesuai dengan rute

  // Endpoint lainnya tetap tidak berubah
  static const String ferries = '/api/v1/ferries';
  static const String routes = '/api/v1/routes';
  static const String schedules = '/api/v1/schedules';

  static const String bookings = '/api/v1/bookings';
  static const String bookingDetail = '/api/v1/bookings/{id}';
  static const String cancelBooking = '/api/v1/bookings/{id}/cancel';
  static const String rescheduleBooking = '/api/v1/bookings/{id}/reschedule';
  static const String generateTickets = 'bookings/{id}/generate-tickets';

  static const String payments = '/api/v1/payments';
  static const String paymentStatus = '/api/v1/payments/{id}/status';

  static const String tickets = '/api/v1/tickets';
  static const String ticketDetail = '/api/v1/tickets/{id}';
  static const String validateTicket = '/api/v1/tickets/{id}/validate';

  static const String notifications = '/api/v1/notifications';

  // Kode Status HTTP dan header tetap tidak berubah
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