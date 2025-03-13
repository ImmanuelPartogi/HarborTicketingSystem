import 'dart:async';
import 'package:flutter/foundation.dart';
import '../models/user_model.dart';
import 'api_service.dart';
import 'storage_service.dart';

class AuthService {
  final ApiService _apiService;
  final StorageService _storageService;

  AuthService(this._apiService, this._storageService);

  Future<User> login(String phone, String password) async {
    try {
      final response = await _apiService.login(phone, password);
      final authResponse = AuthResponse.fromJson(response);
      
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
      
      // Store tokens and user data
      await _storageService.setAccessToken(authResponse.accessToken);
      await _storageService.setRefreshToken(authResponse.refreshToken);
      await _storageService.setTokenExpiry(
        DateTime.now().add(Duration(seconds: authResponse.expiresIn)),
      );
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
      
      // Store tokens and user data
      await _storageService.setAccessToken(authResponse.accessToken);
      await _storageService.setRefreshToken(authResponse.refreshToken);
      await _storageService.setTokenExpiry(
        DateTime.now().add(Duration(seconds: authResponse.expiresIn)),
      );
      await _storageService.setUser(authResponse.user);
      
      return authResponse.user;
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
    
    if (token == null) {
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

  Future<void> changePassword(String currentPassword, String newPassword) async {
    try {
      await _apiService.changePassword(currentPassword, newPassword);
    } catch (e) {
      throw Exception('Password change failed: ${e.toString()}');
    }
  }
}