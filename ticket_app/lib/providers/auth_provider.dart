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
  
  // Getters
  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _user != null;
  
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
  
  // Login with phone and password
  Future<bool> login(String phone, String password) async {
    _setLoading(true);
    _clearError();
    
    try {
      _user = await _authService.login(phone, password);
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
  Future<bool> changePassword(String currentPassword, String newPassword) async {
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