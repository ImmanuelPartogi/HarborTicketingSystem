import 'dart:async';
import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/storage_service.dart';

class AuthProvider extends ChangeNotifier {
  final StorageService _storageService;
  late ApiService _apiService;
  late AuthService _authService;

  User? _user;
  bool _isLoading = false;
  String? _error;
  
  // Tambahkan property baru untuk tracking waktu terakhir fetch profile
  DateTime? lastProfileFetchTime;
  
  // Tambahkan debounce timer
  Timer? _profileDebounceTimer;

  // Getters
  User? get user => _user;
  bool get isLoading => _isLoading;
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
  Future<void> getCurrentUser() async {
    // Hindari multiple calls dalam 2 detik
    if (_profileDebounceTimer != null && _profileDebounceTimer!.isActive) {
      return;
    }
    
    // Set debounce timer
    _profileDebounceTimer = Timer(const Duration(seconds: 2), () {});
    
    _setLoading(true);
    _clearError();

    try {
      // Try to get user from local storage first
      User? localUser = await _authService.getCurrentUser();

      // If user exists in local storage but not in provider, set it
      if (localUser != null && _user == null) {
        _user = localUser;
      }

      // Only try to get profile from server if user is verified 
      // DAN jika waktu terakhir fetch sudah lewat 5 menit
      bool shouldFetchFromServer = localUser != null && 
                                  localUser.isVerified &&
                                  (lastProfileFetchTime == null || 
                                   DateTime.now().difference(lastProfileFetchTime!).inMinutes > 5);
                                   
      if (shouldFetchFromServer) {
        // Get latest data from server
        final response = await _apiService.getProfile();
        if (response != null && response['user'] != null) {
          final updatedUser = User.fromJson(response['user']);

          // Update user in provider and storage
          _user = updatedUser;
          await _storageService.setUser(updatedUser);
          
          // Update last fetch time
          lastProfileFetchTime = DateTime.now();
        }
      }

      _setLoading(false);
    } catch (e) {
      // If failed to get from server, keep using local data
      // and don't set error (for better UX)
      _setLoading(false);

      // Log error but don't show to user if local data is available
      if (_user == null) {
        _setError('Failed to get user profile: ${e.toString()}');
      }
    }
  }

  // Login with email and password
  Future<bool> login(String email, String password) async {
    _setLoading(true);
    _clearError();

    try {
      // Pass 'email' instead of 'phone' to match the API expectation
      _user = await _authService.login(email, password);
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
      _user = null;
      lastProfileFetchTime = null; // Reset fetch time on logout
      _setLoading(false);
      notifyListeners();
    } catch (e) {
      _setError('Logout failed: ${e.toString()}');
      _setLoading(false);
    }
  }

  // Update user profile
  Future<bool> updateProfile(Map<String, dynamic> profileData) async {
    _setLoading(true);
    _clearError();

    try {
      _user = await _authService.updateProfile(profileData);
      lastProfileFetchTime = DateTime.now(); // Set fetch time after profile update
      _setLoading(false);
      notifyListeners();
      return true;
    } catch (e) {
      _setError('Profile update failed: ${e.toString()}');
      _setLoading(false);
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