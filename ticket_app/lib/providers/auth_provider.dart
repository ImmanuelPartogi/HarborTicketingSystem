import 'dart:async';
import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/storage_service.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../config/debug_config.dart';

class AuthProvider extends ChangeNotifier {
  final StorageService _storageService;
  late ApiService _apiService;
  late AuthService _authService;

  User? _user;
  bool _isLoading = false;
  String? _token;
  String? _error;

  // Add caching to prevent repeated JSON conversion
  String? _lastUserJsonString;

  // Tracking throttling
  DateTime? lastProfileFetchTime;
  Timer? _profileDebounceTimer;
  bool _isRefreshingProfile = false;

  // Getters - IMPORTANT: Remove any debug prints from getters!
  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get token => _token;
  String? get error => _error;
  bool get isAuthenticated => _user != null && _user!.isVerified;
  bool get isRegisteredButNotVerified => _user != null && !_user!.isVerified;

  // Constructor
  AuthProvider(this._storageService) {
    _apiService = ApiService(_storageService);
    _authService = AuthService(_apiService, _storageService);
    _loadUserFromStorage();
    _initializeToken();
  }

  // Consolidated setState helper to reduce notifyListeners() calls
  void setState(VoidCallback updateFunction) {
    updateFunction();
    notifyListeners();
  }

  // Load user and token from storage on app start
  Future<void> _loadUserFromStorage() async {
    if (_isLoading) return;
    
    _setLoading(true);
    try {
      _user = await _storageService.getUser();
      
      // Cache the user JSON string to avoid repeated conversion
      if (_user != null) {
        _lastUserJsonString = jsonEncode(_user!.toJson());
      }
      
      _setLoading(false);
    } catch (e) {
      _setError('Failed to load user data');
      _setLoading(false);
    }
  }

  // Initialize token from storage
  Future<void> _initializeToken() async {
    try {
      _token = await _storageService.getAccessToken();
      debugPrint('Token initialized: ${_token != null}');
    } catch (e) {
      debugPrint('Failed to initialize token: $e');
    }
  }

  // Get current user data from server and update - with throttling and caching
  Future<bool> getCurrentUser() async {
    // Skip if in debug mode
    if (DebugConfig.shouldSkipDataLoad('user profile')) {
      return false;
    }
    
    if (_isRefreshingProfile) return false;
    
    // Throttle requests
    if (lastProfileFetchTime != null) {
      final difference = DateTime.now().difference(lastProfileFetchTime!);
      if (difference.inSeconds < 60) {
        debugPrint('Profile refresh throttled: ${60 - difference.inSeconds}s remaining');
        return false;
      }
    }
    
    _isRefreshingProfile = true;
    _error = null;
    
    try {
      final token = await _storageService.getAccessToken();
      
      if (token == null) {
        _error = 'No authentication token';
        _isRefreshingProfile = false;
        return false;
      }
      
      final url = Uri.parse('${ApiConfig.baseUrl}/api/v1/profile');
      final response = await http.get(
        url,
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);
        
        if (responseData['success'] == true) {
          final userJson = responseData['data']['user'];
          final newUserJsonString = json.encode(userJson);
          
          // Only update if the data actually changed
          final shouldUpdate = _lastUserJsonString != newUserJsonString;
          
          if (shouldUpdate) {
            final newUser = User.fromJson(userJson);
            setState(() {
              _user = newUser;
              _lastUserJsonString = newUserJsonString;
            });
          }
          
          lastProfileFetchTime = DateTime.now();
          _isRefreshingProfile = false;
          return true;
        } else {
          _error = responseData['message'];
          _isRefreshingProfile = false;
          return false;
        }
      } else {
        _error = 'Error: ${response.statusCode}';
        _isRefreshingProfile = false;
        return false;
      }
    } catch (e) {
      _error = e.toString();
      _isRefreshingProfile = false;
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
      
      // Cache the user JSON
      if (_user != null) {
        _lastUserJsonString = jsonEncode(_user!.toJson());
      }
      
      // Update token after successful login
      _token = await _storageService.getAccessToken();
      lastProfileFetchTime = DateTime.now(); // Set fetch time after successful login
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
      
      // Cache the user JSON
      if (_user != null) {
        _lastUserJsonString = jsonEncode(_user!.toJson());
      }
      
      // Update token if available after registration
      _token = await _storageService.getAccessToken();
      lastProfileFetchTime = DateTime.now(); // Set fetch time after successful registration
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
      
      // Cache the user JSON
      if (_user != null) {
        _lastUserJsonString = jsonEncode(_user!.toJson());
      }
      
      // Update token after verification
      _token = await _storageService.getAccessToken();
      lastProfileFetchTime = DateTime.now(); // Set fetch time after successful verification
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
      setState(() {
        _user = null;
        _token = null;
        _lastUserJsonString = null;
        lastProfileFetchTime = null; // Reset fetch time on logout
      });
    } catch (e) {
      _setError('Logout failed: ${e.toString()}');
    } finally {
      _setLoading(false);
    }
  }

  // Fixed method to update user profile
  Future<bool> updateProfile(Map<String, dynamic> profileData) async {
    _error = null;
    _setLoading(true);
    
    try {
      // Get token first
      final token = await _storageService.getAccessToken();
      
      if (token == null) {
        _error = 'No authentication token';
        _setLoading(false);
        return false;
      }
      
      // Ensure field names match what backend expects
      final convertedData = {
        'name': profileData['name'],
        'phone': profileData['phone'],
        'id_number': profileData['identity_number'],
        'id_type': profileData['identity_type'],
        'dob': profileData['date_of_birth'],
        'gender': profileData['gender'],
        'address': profileData['address'],
      };

      debugPrint('Updating profile with data: ${json.encode(convertedData)}');
      
      // Use POST method to match backend route definition
      final url = Uri.parse('${ApiConfig.baseUrl}/api/v1/profile');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode(convertedData),
      );

      _setLoading(false);

      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);

        if (responseData['success'] == true) {
          // Update the user object
          final userJson = responseData['data']['user'];
          final newUserJsonString = json.encode(userJson);
          
          // Only update if data changed
          if (_lastUserJsonString != newUserJsonString) {
            final updatedUser = User.fromJson(userJson);
            setState(() {
              _user = updatedUser;
              _lastUserJsonString = newUserJsonString;
            });
          }
          
          return true;
        } else {
          _error = responseData['message'] ?? 'Failed to update profile';
          return false;
        }
      } else if (response.statusCode == 422) {
        // Validation error
        final responseData = json.decode(response.body);
        if (responseData.containsKey('errors')) {
          final errors = responseData['errors'];
          if (errors is Map && errors.isNotEmpty) {
            final firstErrorField = errors.keys.first;
            final firstErrorMessages = errors[firstErrorField];
            
            if (firstErrorMessages is List && firstErrorMessages.isNotEmpty) {
              _error = 'Validation error: ${firstErrorMessages.first}';
            } else {
              _error = 'Validation error on field: $firstErrorField';
            }
          } else {
            _error = 'Validation failed';
          }
        } else {
          _error = responseData['message'] ?? 'Validation failed';
        }
        return false;
      } else {
        _error = 'Error: ${response.statusCode}';
        return false;
      }
    } catch (e) {
      _setLoading(false);
      _error = e.toString();
      debugPrint('Exception when updating profile: $e');
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
      
      // Cache the user JSON
      if (_user != null) {
        _lastUserJsonString = jsonEncode(_user!.toJson());
      }
      
      _token = await _storageService.getAccessToken();
      lastProfileFetchTime = DateTime.now(); // Set fetch time when retrieving user
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