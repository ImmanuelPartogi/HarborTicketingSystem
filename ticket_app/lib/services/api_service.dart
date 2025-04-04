import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart';
import 'dart:async';
import '../config/api_config.dart';
import 'storage_service.dart';
import 'dart:convert';

class ApiService {
  final StorageService _storageService;
  final http.Client _client;

  // Track request time untuk throttling
  final Map<String, DateTime> _lastRequestTime = {};
  final int _minRequestIntervalSeconds = 30; // 30 detik

  ApiService(this._storageService) : _client = http.Client();

  // For testing purposes
  ApiService.withClient(this._storageService, this._client);

  Future<Map<String, String>> _getHeaders({bool requireAuth = true}) async {
    Map<String, String> headers = {
      ApiConfig.contentTypeHeader: ApiConfig.jsonContentType,
      ApiConfig.acceptHeader: ApiConfig.jsonContentType,
    };

    if (requireAuth) {
      final token = await _storageService.getAccessToken();
      if (token != null) {
        headers[ApiConfig.authHeader] = '${ApiConfig.bearerPrefix}$token';
      }
    }

    return headers;
  }

  // Throttling sederhana
  bool _canMakeRequest(String endpoint) {
    if (_lastRequestTime.containsKey(endpoint)) {
      final lastTime = _lastRequestTime[endpoint]!;
      final difference = DateTime.now().difference(lastTime).inSeconds;

      if (difference < _minRequestIntervalSeconds) {
        debugPrint(
          'THROTTLING: Request to $endpoint - last request was $difference seconds ago',
        );
        return false;
      }
    }
    return true;
  }

  Future<dynamic> _scheduleDelayedRequest(
    String endpoint,
    Future<dynamic> Function() requestFunc,
  ) async {
    if (!_canMakeRequest(endpoint)) {
      final lastTime = _lastRequestTime[endpoint]!;
      final difference = DateTime.now().difference(lastTime).inSeconds;
      final delaySeconds =
          _minRequestIntervalSeconds - difference + 1; // Add 1 second buffer

      debugPrint(
        'Scheduling delayed request to $endpoint in $delaySeconds seconds',
      );

      // Return a future that will complete after the delay
      return Future.delayed(
        Duration(seconds: delaySeconds),
        () => requestFunc(), // Call the original request function after delay
      );
    }

    // Execute request immediately if not throttled
    return requestFunc();
  }

  Future<dynamic> get(
    String endpoint, {
    bool requireAuth = true,
    Map<String, dynamic>? queryParams,
    bool bypassThrottling = false,
  }) async {
    try {
      if (!bypassThrottling) {
        // Use the delayed request pattern
        return _scheduleDelayedRequest(
          endpoint,
          () => _executeGetRequest(endpoint, requireAuth, queryParams),
        );
      }

      // Bypass throttling
      return _executeGetRequest(endpoint, requireAuth, queryParams);
    } catch (e) {
      // Error handling remains the same
      throw Exception('Unexpected error: $e');
    }
  }

  Future<dynamic> _executeGetRequest(
    String endpoint,
    bool requireAuth,
    Map<String, dynamic>? queryParams,
  ) async {
    // Record request time
    _lastRequestTime[endpoint] = DateTime.now();

    final headers = await _getHeaders(requireAuth: requireAuth);
    final uri = _buildUri(endpoint, queryParams);

    debugPrint('GET Request: ${uri.toString()}');
    final response = await _client
        .get(uri, headers: headers)
        .timeout(Duration(milliseconds: ApiConfig.connectTimeout));

    return _handleResponse(response, endpoint: endpoint);
  }

  // Add this helper method for URI construction
  Uri _buildUri(String endpoint, Map<String, dynamic>? queryParams) {
    // Get the base URL
    String baseUrl = ApiConfig.baseUrl;

    // Check if the endpoint already starts with the base URL
    // This can happen if someone passes a full URL instead of just an endpoint
    if (endpoint.startsWith('http')) {
      return Uri.parse(endpoint).replace(queryParameters: queryParams);
    }

    // Handle trailing slashes in baseUrl and leading slashes in endpoint
    if (baseUrl.endsWith('/') && endpoint.startsWith('/')) {
      // Remove the leading slash from endpoint if baseUrl already has trailing slash
      endpoint = endpoint.substring(1);
    } else if (!baseUrl.endsWith('/') && !endpoint.startsWith('/')) {
      // Add a slash between baseUrl and endpoint if both don't have one
      baseUrl = '$baseUrl/';
    }

    // Combine and parse the URL
    final url = baseUrl + endpoint;
    return Uri.parse(url).replace(queryParameters: queryParams);
  }

  Future<dynamic> post(
    String endpoint, {
    dynamic body,
    bool requireAuth = true,
    bool bypassThrottling = false,
  }) async {
    int retryCount = 0;
    const maxRetries = 3;
    const initialDelay = 2000; // 2 seconds

    while (true) {
      try {
        // Throttling check (skip if bypassThrottling is true or we're retrying)
        if (!bypassThrottling &&
            retryCount == 0 &&
            !_canMakeRequest(endpoint)) {
          throw Exception('Request throttled. Please try again later.');
        }

        // Record request time
        _lastRequestTime[endpoint] = DateTime.now();

        final headers = await _getHeaders(requireAuth: requireAuth);
        final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint');

        debugPrint('POST Request: ${uri.toString()}');
        if (body != null) {
          debugPrint('POST Body: ${json.encode(body)}');
        }

        final response = await _client
            .post(uri, headers: headers, body: json.encode(body))
            .timeout(Duration(milliseconds: ApiConfig.connectTimeout));

        return _handleResponse(response, endpoint: endpoint);
      } on SocketException {
        throw Exception('No Internet connection');
      } on http.ClientException {
        throw Exception('Connection error');
      } on TimeoutException {
        throw Exception('Connection timeout');
      } catch (e) {
        bool isThrottlingError = e.toString().contains("throttled");

        // Handle throttling with retries
        if (isThrottlingError && retryCount < maxRetries) {
          retryCount++;
          final delay = initialDelay * retryCount;
          debugPrint(
            'Request throttled, retrying in ${delay}ms... (Attempt ${retryCount} of ${maxRetries})',
          );
          await Future.delayed(Duration(milliseconds: delay));
        } else {
          // Either not a throttling error or max retries exceeded
          if (isThrottlingError && retryCount >= maxRetries) {
            throw Exception('Request throttled. Max retries exceeded.');
          } else {
            throw Exception('Unexpected error: $e');
          }
        }
      }
    }
  }

  Future<dynamic> put(
    String endpoint, {
    dynamic body,
    bool requireAuth = true,
    bool bypassThrottling = false,
  }) async {
    try {
      // Throttling check
      if (!bypassThrottling && !_canMakeRequest(endpoint)) {
        throw Exception('Request throttled. Please try again later.');
      }

      // Record request time
      _lastRequestTime[endpoint] = DateTime.now();

      final headers = await _getHeaders(requireAuth: requireAuth);
      final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint');

      debugPrint('PUT Request: ${uri.toString()}');
      if (body != null) {
        debugPrint('PUT Body: ${json.encode(body)}');
      }

      final response = await _client
          .put(uri, headers: headers, body: json.encode(body))
          .timeout(Duration(milliseconds: ApiConfig.connectTimeout));

      return _handleResponse(response, endpoint: endpoint);
    } on SocketException {
      throw Exception('No Internet connection');
    } on http.ClientException {
      throw Exception('Connection error');
    } on TimeoutException {
      throw Exception('Connection timeout');
    } catch (e) {
      throw Exception('Unexpected error: $e');
    }
  }

  Future<dynamic> patch(
    String endpoint, {
    dynamic body,
    bool requireAuth = true,
    bool bypassThrottling = false,
  }) async {
    try {
      // Throttling check
      if (!bypassThrottling && !_canMakeRequest(endpoint)) {
        throw Exception('Request throttled. Please try again later.');
      }

      // Record request time
      _lastRequestTime[endpoint] = DateTime.now();

      final headers = await _getHeaders(requireAuth: requireAuth);
      final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint');

      final response = await _client
          .patch(uri, headers: headers, body: json.encode(body))
          .timeout(Duration(milliseconds: ApiConfig.connectTimeout));

      return _handleResponse(response, endpoint: endpoint);
    } on SocketException {
      throw Exception('No Internet connection');
    } on http.ClientException {
      throw Exception('Connection error');
    } on TimeoutException {
      throw Exception('Connection timeout');
    } catch (e) {
      throw Exception('Unexpected error: $e');
    }
  }

  Future<dynamic> delete(
    String endpoint, {
    bool requireAuth = true,
    bool bypassThrottling = false,
  }) async {
    try {
      // Throttling check
      if (!bypassThrottling && !_canMakeRequest(endpoint)) {
        throw Exception('Request throttled. Please try again later.');
      }

      // Record request time
      _lastRequestTime[endpoint] = DateTime.now();

      final headers = await _getHeaders(requireAuth: requireAuth);
      final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint');

      final response = await _client
          .delete(uri, headers: headers)
          .timeout(Duration(milliseconds: ApiConfig.connectTimeout));

      return _handleResponse(response, endpoint: endpoint);
    } on SocketException {
      throw Exception('No Internet connection');
    } on http.ClientException {
      throw Exception('Connection error');
    } on TimeoutException {
      throw Exception('Connection timeout');
    } catch (e) {
      throw Exception('Unexpected error: $e');
    }
  }

  dynamic _handleResponse(http.Response response, {String endpoint = ''}) {
    try {
      debugPrint('API Response [${response.statusCode}] from $endpoint');

      // Log response body for debugging (truncate if too long)
      final responsePreview =
          response.body.length > 300
              ? '${response.body.substring(0, 300)}...'
              : response.body;
      debugPrint('Response preview: $responsePreview');

      switch (response.statusCode) {
        case ApiConfig.statusOk:
        case ApiConfig.statusCreated:
          final responseBody = utf8.decode(response.bodyBytes);
          if (responseBody.isEmpty) {
            return {'success': true};
          }
          return json.decode(responseBody);
        case ApiConfig.statusNoContent:
          return {'success': true};
        case ApiConfig.statusBadRequest:
          final responseBody = utf8.decode(response.bodyBytes);
          final errorData = json.decode(responseBody);
          throw Exception(errorData['message'] ?? 'Bad request');
        case ApiConfig.statusUnauthorized:
          // Handle token refresh or logout
          throw Exception('Unauthorized');
        case ApiConfig.statusForbidden:
          throw Exception('Access denied');
        case ApiConfig.statusNotFound:
          throw Exception('Resource not found');
        case 422: // Unprocessable Entity - validation errors
          final responseBody = utf8.decode(response.bodyBytes);
          final errorData = json.decode(responseBody);

          // Log the complete error response for debugging
          debugPrint('Validation Error Details: $errorData');

          // Laravel validation errors typically come in 'errors' field
          if (errorData.containsKey('errors')) {
            final errors = errorData['errors'];
            if (errors is Map && errors.isNotEmpty) {
              // Get first error message for simplicity
              final firstErrorField = errors.keys.first;
              final firstErrorMessages = errors[firstErrorField];

              if (firstErrorMessages is List && firstErrorMessages.isNotEmpty) {
                return throw Exception(
                  'Validation error: ${firstErrorMessages.first}',
                );
              }
            }
          }

          // If we can't extract specific error message, use the message field or default
          throw Exception(errorData['message'] ?? 'Validation failed');

        case ApiConfig.statusInternalServerError:
          throw Exception('Server error');
        default:
          throw Exception('Request failed with status: ${response.statusCode}');
      }
    } catch (e) {
      debugPrint('Error handling response: $e');
      rethrow;
    }
  }

  // Helper method to replace path parameters in URL
  String _replacePathParams(String endpoint, Map<String, dynamic> pathParams) {
    String result = endpoint;
    pathParams.forEach((key, value) {
      result = result.replaceAll('{$key}', value.toString());
    });
    return result;
  }

  // Auth methods
  Future<dynamic> login(String email, String password) {
    return post(
      ApiConfig.login,
      body: {'email': email, 'password': password},
      requireAuth: false,
      bypassThrottling: true, // Bypass throttling for login
    );
  }

  Future<dynamic> register(Map<String, dynamic> userData) {
    return post(
      ApiConfig.register,
      body: userData,
      requireAuth: false,
      bypassThrottling: true, // Bypass throttling for registration
    );
  }

  Future<dynamic> verifyOtp(String phone, String otp) {
    return post(
      ApiConfig.verifyOtp,
      body: {'phone': phone, 'otp': otp},
      requireAuth: false,
      bypassThrottling: true, // Bypass throttling for OTP verification
    );
  }

  Future<dynamic> refreshToken(String refreshToken) {
    return post(
      ApiConfig.refreshToken,
      body: {'refresh_token': refreshToken},
      requireAuth: false,
      bypassThrottling: true, // Bypass throttling for token refresh
    );
  }

  Future<dynamic> logout() {
    return post(
      ApiConfig.logout,
      bypassThrottling: true, // Bypass throttling for logout
    );
  }

  // Profile endpoints
  Future<dynamic> getProfile() {
    return get(ApiConfig.profile);
  }

  Future<dynamic> updateProfile(Map<String, dynamic> profileData) {
    return put(
      ApiConfig.updateProfile,
      body: profileData,
      bypassThrottling: true, // Bypass throttling for important user actions
    );
  }

  Future<dynamic> changePassword(String currentPassword, String newPassword) {
    return post(
      ApiConfig.changePassword,
      body: {
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': newPassword,
      },
      bypassThrottling: true, // Bypass throttling for important user actions
    );
  }

  // Ferry endpoints
  Future<dynamic> getFerries({Map<String, dynamic>? queryParams}) {
    return get(ApiConfig.ferries, queryParams: queryParams);
  }

  // Route endpoints
  Future<dynamic> getRoutes({Map<String, dynamic>? queryParams}) {
    return get(ApiConfig.routes, queryParams: queryParams);
  }

  // Schedule endpoints
  Future<dynamic> getSchedules({Map<String, dynamic>? queryParams}) {
    return get(ApiConfig.schedules, queryParams: queryParams);
  }

  // Booking endpoints
  Future<dynamic> createBooking(Map<String, dynamic> bookingData) async {
    try {
      final response = await post('/api/v1/bookings', body: bookingData);

      // Log the full response for debugging
      print('API response for createBooking:');
      print(jsonEncode(response));

      return response;
    } catch (e) {
      print('API error in createBooking: $e');
      throw Exception('Failed to create booking: $e');
    }
  }

  Future<dynamic> getBookings({Map<String, dynamic>? queryParams}) {
    return get(ApiConfig.bookings, queryParams: queryParams);
  }

  Future<dynamic> getBookingDetail(dynamic bookingIdentifier) async {
    try {
      print('Fetching booking details for: $bookingIdentifier');

      // Format URL yang benar tanpa prefix - perbaiki endpoint
      String endpoint = '/api/v1/bookings/$bookingIdentifier';

      // Tambahkan retry dengan backoff otomatis
      int retryCount = 0;
      const maxRetries = 3;
      Duration delay = Duration(seconds: 2);

      while (true) {
        try {
          // Tambah delay pada percobaan pertama
          if (retryCount == 0) {
            print('Delaying initial request to allow data synchronization...');
            await Future.delayed(Duration(seconds: 1));
          }

          final response = await get(
            endpoint,
            bypassThrottling: retryCount > 0,
          );
          return response;
        } catch (e) {
          retryCount++;
          if (retryCount >= maxRetries) {
            print('Max retries exceeded for booking detail request');
            throw e; // Lempar error jika sudah mencapai batas retry
          }

          print(
            'Error fetching booking, retrying in ${delay.inSeconds}s (${retryCount}/${maxRetries})',
          );
          await Future.delayed(delay);

          // Exponential backoff: meningkatkan delay untuk retry berikutnya
          delay = Duration(milliseconds: (delay.inMilliseconds * 1.5).round());
        }
      }
    } catch (e) {
      print('API error in getBookingDetail: $e');
      throw Exception('Failed to get booking details: $e');
    }
  }

  Future<dynamic> cancelBooking(int id, {String? reason}) {
    return post(
      _replacePathParams(ApiConfig.cancelBooking, {'id': id}),
      body: reason != null ? {'reason': reason} : null,
      bypassThrottling: true, // Bypass throttling for booking cancellation
    );
  }

  Future<dynamic> rescheduleBooking(int id, int newScheduleId) {
    return post(
      _replacePathParams(ApiConfig.rescheduleBooking, {'id': id}),
      body: {'schedule_id': newScheduleId},
      bypassThrottling: true, // Bypass throttling for booking rescheduling
    );
  }

  Future<dynamic> getBookingByCode(String bookingCode) async {
    if (bookingCode.isEmpty) {
      throw Exception('Invalid booking code');
    }

    try {
      final response = await get('/api/v1/bookings/code/$bookingCode');

      // Log the full response for debugging
      print('API response for getBookingByCode:');
      print(jsonEncode(response));

      return response;
    } catch (e) {
      print('API error in getBookingByCode: $e');
      throw Exception('Failed to get booking by code: $e');
    }
  }

  Future<dynamic> processPayment(
    dynamic bookingIdentifier,
    String paymentMethod,
    String paymentChannel,
  ) async {
    try {
      print('Creating payment for booking: $bookingIdentifier');

      // Format URL yang benar tanpa prefix /code/ - perbaiki endpoint
      String endpoint = '/api/v1/bookings/$bookingIdentifier/pay';

      // Tambahkan delay sebelum memproses pembayaran
      print('Menunggu sebelum memproses pembayaran...');
      await Future.delayed(Duration(seconds: 2));

      // Tambahkan retry mechanism dengan backoff
      int retryCount = 0;
      const maxRetries = 3;
      Duration delay = Duration(seconds: 2);

      while (true) {
        try {
          final response = await post(
            endpoint,
            body: {
              'payment_method': paymentMethod.toUpperCase(),
              'payment_channel': paymentChannel,
            },
            bypassThrottling: retryCount > 0, // Bypass throttling untuk retry
          );
          return response;
        } catch (e) {
          retryCount++;

          // Cek jika error adalah 404 (Not Found)
          bool isNotFound =
              e.toString().contains("Resource not found") ||
              e.toString().contains("404");

          // Coba format URL alternatif jika endpoint pertama tidak ditemukan
          // dan ini adalah percobaan pertama
          if (isNotFound && retryCount == 1) {
            // Coba format URL alternatif - tanpa /pay
            endpoint = '/api/v1/payments/booking/$bookingIdentifier';
            print('Trying alternative endpoint: $endpoint');
            continue; // Skip delay dan langsung coba endpoint baru
          }

          if (retryCount >= maxRetries) {
            print('Max retries exceeded for payment processing');
            throw e; // Lempar error jika sudah mencapai batas retry
          }

          print(
            'Error processing payment, retrying in ${delay.inSeconds}s (${retryCount}/${maxRetries})',
          );
          await Future.delayed(delay);

          // Exponential backoff: meningkatkan delay untuk retry berikutnya
          delay = Duration(milliseconds: (delay.inMilliseconds * 1.5).round());
        }
      }
    } catch (e) {
      print('API error in processPayment: $e');
      throw Exception('Failed to process payment: $e');
    }
  }

  // Payment endpoints
  Future<dynamic> createPayment(
    int bookingId,
    String paymentMethod,
    String paymentType,
  ) {
    return post(
      ApiConfig.payments,
      body: {
        'booking_id': bookingId,
        'payment_method': paymentMethod,
        'payment_type': paymentType,
      },
      bypassThrottling: true, // Bypass throttling for payment creation
    );
  }

  Future<dynamic> getPaymentStatus(int id) {
    return get(_replacePathParams(ApiConfig.paymentStatus, {'id': id}));
  }

  // Ticket endpoints
  Future<dynamic> getTickets({Map<String, dynamic>? queryParams}) {
    return get(ApiConfig.tickets, queryParams: queryParams);
  }

  Future<dynamic> getTicketDetail(int id) {
    return get(_replacePathParams(ApiConfig.ticketDetail, {'id': id}));
  }

  Future<dynamic> validateTicket(int id) {
    return post(
      _replacePathParams(ApiConfig.validateTicket, {'id': id}),
      bypassThrottling: true, // Bypass throttling for ticket validation
    );
  }

  // Notification endpoints
  Future<dynamic> getNotifications() {
    return get(ApiConfig.notifications);
  }

  Future<dynamic> generateTicketsForBooking(int bookingId) {
    return post(
      _replacePathParams(ApiConfig.generateTickets, {'id': bookingId}),
      body: {},
      bypassThrottling: true, // Bypass throttling for ticket generation
    );
  }

  // Clear throttling data - call on logout
  void clearThrottlingData() {
    _lastRequestTime.clear();
  }
}
