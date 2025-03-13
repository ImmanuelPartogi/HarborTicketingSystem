import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart';

import '../config/api_config.dart';
import 'storage_service.dart';

class ApiService {
  final StorageService _storageService;
  final http.Client _client;

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

  Future<dynamic> get(String endpoint, {bool requireAuth = true, Map<String, dynamic>? queryParams}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      
      final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint')
          .replace(queryParameters: queryParams);
      
      final response = await _client.get(uri, headers: headers)
          .timeout(Duration(milliseconds: ApiConfig.connectTimeout));

      return _handleResponse(response);
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

  Future<dynamic> post(String endpoint, {dynamic body, bool requireAuth = true}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      
      final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint');
      
      final response = await _client.post(
        uri,
        headers: headers,
        body: json.encode(body),
      ).timeout(Duration(milliseconds: ApiConfig.connectTimeout));

      return _handleResponse(response);
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

  Future<dynamic> put(String endpoint, {dynamic body, bool requireAuth = true}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      
      final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint');
      
      final response = await _client.put(
        uri,
        headers: headers,
        body: json.encode(body),
      ).timeout(Duration(milliseconds: ApiConfig.connectTimeout));

      return _handleResponse(response);
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

  Future<dynamic> patch(String endpoint, {dynamic body, bool requireAuth = true}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      
      final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint');
      
      final response = await _client.patch(
        uri,
        headers: headers,
        body: json.encode(body),
      ).timeout(Duration(milliseconds: ApiConfig.connectTimeout));

      return _handleResponse(response);
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

  Future<dynamic> delete(String endpoint, {bool requireAuth = true}) async {
    try {
      final headers = await _getHeaders(requireAuth: requireAuth);
      
      final uri = Uri.parse('${ApiConfig.baseUrl}$endpoint');
      
      final response = await _client.delete(
        uri,
        headers: headers,
      ).timeout(Duration(milliseconds: ApiConfig.connectTimeout));

      return _handleResponse(response);
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

  dynamic _handleResponse(http.Response response) {
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
      case ApiConfig.statusInternalServerError:
        throw Exception('Server error');
      default:
        throw Exception('Request failed with status: ${response.statusCode}');
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

  // Specific API methods using the generic HTTP methods

  // Auth endpoints
  Future<dynamic> login(String phone, String password) {
    return post(
      ApiConfig.login,
      body: {'phone': phone, 'password': password},
      requireAuth: false,
    );
  }

  Future<dynamic> register(Map<String, dynamic> userData) {
    return post(
      ApiConfig.register,
      body: userData,
      requireAuth: false,
    );
  }

  Future<dynamic> verifyOtp(String phone, String otp) {
    return post(
      ApiConfig.verifyOtp,
      body: {'phone': phone, 'otp': otp},
      requireAuth: false,
    );
  }

  Future<dynamic> refreshToken(String refreshToken) {
    return post(
      ApiConfig.refreshToken,
      body: {'refresh_token': refreshToken},
      requireAuth: false,
    );
  }

  Future<dynamic> logout() {
    return post(ApiConfig.logout);
  }

  // Profile endpoints
  Future<dynamic> getProfile() {
    return get(ApiConfig.profile);
  }

  Future<dynamic> updateProfile(Map<String, dynamic> profileData) {
    return put(ApiConfig.updateProfile, body: profileData);
  }

  Future<dynamic> changePassword(String currentPassword, String newPassword) {
    return post(
      ApiConfig.changePassword,
      body: {
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': newPassword,
      },
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
  Future<dynamic> createBooking(Map<String, dynamic> bookingData) {
    return post(ApiConfig.bookings, body: bookingData);
  }

  Future<dynamic> getBookings({Map<String, dynamic>? queryParams}) {
    return get(ApiConfig.bookings, queryParams: queryParams);
  }

  Future<dynamic> getBookingDetail(int id) {
    return get(_replacePathParams(ApiConfig.bookingDetail, {'id': id}));
  }

  Future<dynamic> cancelBooking(int id, {String? reason}) {
    return post(
      _replacePathParams(ApiConfig.cancelBooking, {'id': id}),
      body: reason != null ? {'reason': reason} : null,
    );
  }

  Future<dynamic> rescheduleBooking(int id, int newScheduleId) {
    return post(
      _replacePathParams(ApiConfig.rescheduleBooking, {'id': id}),
      body: {'schedule_id': newScheduleId},
    );
  }

  // Payment endpoints
  Future<dynamic> createPayment(int bookingId, String paymentMethod, String paymentType) {
    return post(
      ApiConfig.payments,
      body: {
        'booking_id': bookingId,
        'payment_method': paymentMethod,
        'payment_type': paymentType,
      },
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
    return post(_replacePathParams(ApiConfig.validateTicket, {'id': id}));
  }

  // Notification endpoints
  Future<dynamic> getNotifications() {
    return get(ApiConfig.notifications);
  }
}