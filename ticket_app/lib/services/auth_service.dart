import 'dart:async';
import 'package:flutter/foundation.dart';
import '../models/user_model.dart';
import 'api_service.dart';
import 'storage_service.dart';

class AuthService {
  final ApiService _apiService;
  final StorageService _storageService;

  AuthService(this._apiService, this._storageService);

  Future<User> login(String email, String password) async {
    try {
      final response = await _apiService.login(email, password);
      final authResponse = AuthResponse.fromJson(response);

      // Check if user is verified before storing permanent tokens
      if (!authResponse.user.isVerified) {
        // Store the email for potential OTP verification
        await _storageService.setTempPhone(email);
        await _storageService.setTempUserId(authResponse.user.id);

        // Store the user but don't store authentication tokens yet
        await _storageService.setUser(authResponse.user);

        throw Exception('Account not verified. Please verify with OTP first.');
      }

      // Store tokens and user data
      await _storageService.setAccessToken(authResponse.accessToken);
      await _storageService.setRefreshToken(authResponse.refreshToken);
      await _storageService.setTokenExpiry(
        DateTime.now().add(Duration(seconds: authResponse.expiresIn)),
      );
      await _storageService.setUser(authResponse.user);

      return authResponse.user;
    } catch (e) {
      throw Exception('Login failed: ${e.toString()}');
    }
  }

  Future<User> register(Map<String, dynamic> userData) async {
    try {
      final response = await _apiService.register(userData);
      final authResponse = AuthResponse.fromJson(response);

      // For registration, store temporary data for OTP verification
      // DO NOT store permanent tokens yet
      await _storageService.setTempPhone(userData['phone'] ?? '');
      await _storageService.setTempUserId(authResponse.user.id);

      // Store the user but mark as not fully authenticated
      await _storageService.setUser(authResponse.user);

      return authResponse.user;
    } catch (e) {
      throw Exception('Registration failed: ${e.toString()}');
    }
  }

  Future<User> verifyOtp(String phone, String otp) async {
    try {
      final response = await _apiService.verifyOtp(phone, otp);
      final authResponse = AuthResponse.fromJson(response);

      // Now that OTP is verified, store permanent tokens
      await _storageService.setAccessToken(authResponse.accessToken);
      await _storageService.setRefreshToken(authResponse.refreshToken);
      await _storageService.setTokenExpiry(
        DateTime.now().add(Duration(seconds: authResponse.expiresIn)),
      );

      // Update user with verified status
      final verifiedUser = authResponse.user.copyWith(isVerified: true);
      await _storageService.setUser(verifiedUser);

      // Clean up temporary data
      await _storageService.clearTempData();

      return verifiedUser;
    } catch (e) {
      throw Exception('OTP verification failed: ${e.toString()}');
    }
  }

  Future<bool> refreshToken() async {
    try {
      final refreshToken = await _storageService.getRefreshToken();

      if (refreshToken == null) {
        return false;
      }

      final response = await _apiService.refreshToken(refreshToken);
      final authResponse = AuthResponse.fromJson(response);

      // Store new tokens
      await _storageService.setAccessToken(authResponse.accessToken);
      await _storageService.setRefreshToken(authResponse.refreshToken);
      await _storageService.setTokenExpiry(
        DateTime.now().add(Duration(seconds: authResponse.expiresIn)),
      );

      return true;
    } catch (e) {
      await logout();
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (e) {
      debugPrint('Logout API call failed: ${e.toString()}');
    } finally {
      // Clear stored data regardless of API call success
      await _storageService.clearAuthData();
    }
  }

  Future<bool> isLoggedIn() async {
    final token = await _storageService.getAccessToken();
    final expiry = await _storageService.getTokenExpiry();
    final user = await _storageService.getUser();

    // Check for token, expiry and verified status
    if (token == null || user == null || !user.isVerified) {
      return false;
    }

    // If token is expired, try to refresh
    if (expiry != null && expiry.isBefore(DateTime.now())) {
      return await refreshToken();
    }

    return true;
  }

  Future<User?> getCurrentUser() async {
    return await _storageService.getUser();
  }

  Future<User> updateProfile(Map<String, dynamic> profileData) async {
    try {
      final response = await _apiService.updateProfile(profileData);
      final user = User.fromJson(response['user']);

      // Update stored user data
      await _storageService.setUser(user);

      return user;
    } catch (e) {
      throw Exception('Profile update failed: ${e.toString()}');
    }
  }

  Future<void> changePassword(
    String currentPassword,
    String newPassword,
  ) async {
    try {
      await _apiService.changePassword(currentPassword, newPassword);
    } catch (e) {
      throw Exception('Password change failed: ${e.toString()}');
    }
  }
}
