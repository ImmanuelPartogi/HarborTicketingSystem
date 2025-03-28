import 'dart:async';
import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/storage_service.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';

class AuthProvider extends ChangeNotifier {
  final StorageService _storageService;
  late ApiService _apiService;
  late AuthService _authService;

  User? _user;
  bool _isLoading = false;
  String? _token;
  String? _error;

  // Tambahkan property baru untuk tracking waktu terakhir fetch profile
  DateTime? lastProfileFetchTime;

  // Tambahkan debounce timer
  Timer? _profileDebounceTimer;

  // Getters
  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get token => _token;
  String? get error => _error;
  bool get isAuthenticated =>
      _user != null && _user!.isVerified; // Check verification status
  bool get isRegisteredButNotVerified => _user != null && !_user!.isVerified;

  AuthProvider(this._storageService) {
    _apiService = ApiService(_storageService);
    _authService = AuthService(_apiService, _storageService);
    _loadUserFromStorage();
  }

  // Load user from storage on app start
  Future<void> _loadUserFromStorage() async {
    _setLoading(true);
    try {
      _user = await _storageService.getUser();
      _setLoading(false);
    } catch (e) {
      _setError('Failed to load user data');
      _setLoading(false);
    }
  }

  // Get current user data from server and update
  Future<bool> getCurrentUser() async {
    _error = null;
    try {
      final url = Uri.parse('${ApiConfig.baseUrl}/api/v1/profile');
      final response = await http.get(
        url,
        headers: {'Authorization': 'Bearer $_token'},
      );

      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);

        if (responseData['success'] == true) {
          _user = User.fromJson(responseData['data']['user']);
          notifyListeners();
          return true;
        } else {
          _error = responseData['message'];
          return false;
        }
      } else {
        _error = 'Error: ${response.statusCode}';
        return false;
      }
    } catch (e) {
      _error = e.toString();
      return false;
    }
  }

  // Login with email and password
  Future<bool> login(String email, String password) async {
    _setLoading(true);
    _clearError();

    try {
      // Pass 'email' instead of 'phone' to match the API expectation
      _user = await _authService.login(email, password);
      lastProfileFetchTime =
          DateTime.now(); // Set fetch time after successful login
      _setLoading(false);
      notifyListeners();
      return true;
    } catch (e) {
      _setError('Login failed: ${e.toString()}');
      _setLoading(false);
      return false;
    }
  }

  // Register a new user
  Future<bool> register(Map<String, dynamic> userData) async {
    _setLoading(true);
    _clearError();

    try {
      _user = await _authService.register(userData);
      lastProfileFetchTime =
          DateTime.now(); // Set fetch time after successful registration
      _setLoading(false);
      notifyListeners();
      return true;
    } catch (e) {
      _setError('Registration failed: ${e.toString()}');
      _setLoading(false);
      return false;
    }
  }

  // Verify OTP
  Future<bool> verifyOtp(String phone, String otp) async {
    _setLoading(true);
    _clearError();

    try {
      _user = await _authService.verifyOtp(phone, otp);
      lastProfileFetchTime =
          DateTime.now(); // Set fetch time after successful verification
      _setLoading(false);
      notifyListeners();
      return true;
    } catch (e) {
      _setError('OTP verification failed: ${e.toString()}');
      _setLoading(false);
      return false;
    }
  }

  // Logout user
  Future<void> logout() async {
    _setLoading(true);
    _clearError();

    try {
      await _authService.logout();
      _user = null;
      lastProfileFetchTime = null; // Reset fetch time on logout
      _setLoading(false);
      notifyListeners();
    } catch (e) {
      _setError('Logout failed: ${e.toString()}');
      _setLoading(false);
    }
  }

  // Fixed method to update user profile
  Future<bool> updateProfile(Map<String, dynamic> profileData) async {
    _error = null;
    try {
      // Keep the same field mappings if necessary
      final convertedData = {
        'name': profileData['name'],
        'email': profileData['email'],
        'phone': profileData['phone'],
        'identity_number': profileData['identity_number'],
        'identity_type': profileData['identity_type'],
        'date_of_birth': profileData['date_of_birth'],
        'gender': profileData['gender'],
        'address': profileData['address'],
      };

      // Use the correct endpoint with POST method
      final url = Uri.parse('${ApiConfig.baseUrl}/api/v1/profile');

      // Changed from PUT to POST
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $_token',
        },
        body: json.encode(convertedData),
      );

      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);

        if (responseData['success'] == true) {
          // Update the local user object
          _user = User.fromJson(responseData['data']['user']);
          notifyListeners();
          return true;
        } else {
          _error = responseData['message'] ?? 'Failed to update profile';
          return false;
        }
      } else {
        _error = 'Error: ${response.statusCode}';
        return false;
      }
    } catch (e) {
      _error = e.toString();
      return false;
    }
  }

  // Change password
  Future<bool> changePassword(
    String currentPassword,
    String newPassword,
  ) async {
    _setLoading(true);
    _clearError();

    try {
      await _authService.changePassword(currentPassword, newPassword);
      _setLoading(false);
      return true;
    } catch (e) {
      _setError('Password change failed: ${e.toString()}');
      _setLoading(false);
      return false;
    }
  }

  // Check if user is logged in
  Future<bool> isLoggedIn() async {
    final isLoggedIn = await _authService.isLoggedIn();

    if (isLoggedIn && _user == null) {
      _user = await _storageService.getUser();
      lastProfileFetchTime =
          DateTime.now(); // Set fetch time when retrieving user
      notifyListeners();
    }

    return isLoggedIn;
  }

  // Helper methods
  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String error) {
    _error = error;
    notifyListeners();
  }

  void _clearError() {
    _error = null;
    notifyListeners();
  }
}
