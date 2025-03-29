import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import 'package:flutter/foundation.dart';

class StorageService {
  final SharedPreferences _prefs;
  // Storage keys
  static const String _accessTokenKey = 'access_token';
  static const String _refreshTokenKey = 'refresh_token';
  static const String _tokenExpiryKey = 'token_expiry';
  static const String _userKey = 'user';
  static const String _tempPhoneKey = 'temp_phone';
  static const String _tempUserIdKey = 'temp_user_id';
  static const String _themeModeKey = 'theme_mode';
  static const String _languageCodeKey = 'language_code';
  static const String _recentSearchesKey = 'recent_searches';
  static const String _savedPassengersKey = 'saved_passengers';
  static const String _savedVehiclesKey = 'saved_vehicles';
  static const String _notificationEnabledKey = 'notification_enabled';
  static const String _biometricEnabledKey = 'biometric_enabled';

  StorageService(this._prefs);

  // Auth data methods
  Future<void> setAccessToken(String token) async {
    await _prefs.setString(_accessTokenKey, token);
  }

  Future<String?> getAccessToken() async {
    return _prefs.getString(_accessTokenKey);
  }

  Future<void> setRefreshToken(String token) async {
    await _prefs.setString(_refreshTokenKey, token);
  }

  Future<String?> getRefreshToken() async {
    return _prefs.getString(_refreshTokenKey);
  }

  Future<void> setTokenExpiry(DateTime expiry) async {
    await _prefs.setString(_tokenExpiryKey, expiry.toIso8601String());
  }

  Future<DateTime?> getTokenExpiry() async {
    final expiryStr = _prefs.getString(_tokenExpiryKey);
    if (expiryStr == null) {
      return null;
    }
    return DateTime.parse(expiryStr);
  }

  Future<void> setUser(User user) async {
    await _prefs.setString(_userKey, jsonEncode(user.toJson()));
  }

  Future<User?> getUser() async {
    try {
      final userString = _prefs.getString(_userKey);
      if (userString == null) return null;
      
      // REMOVED: Debug printing of user JSON that caused loops
      
      final userJson = json.decode(userString);
      return User.fromJson(userJson);
    } catch (e) {
      debugPrint('Error getting user from storage: $e');
      return null;
    }
  }

  // Temporary data for registration/verification flow
  Future<void> setTempPhone(String phone) async {
    await _prefs.setString(_tempPhoneKey, phone);
  }

  Future<String?> getTempPhone() async {
    return _prefs.getString(_tempPhoneKey);
  }

  Future<void> setTempUserId(int id) async {
    await _prefs.setInt(_tempUserIdKey, id);
  }

  Future<int?> getTempUserId() async {
    return _prefs.getInt(_tempUserIdKey);
  }

  // Clear methods
  Future<void> clearAuthData() async {
    await _prefs.remove(_accessTokenKey);
    await _prefs.remove(_refreshTokenKey);
    await _prefs.remove(_tokenExpiryKey);
    await _prefs.remove(_userKey);
    await clearTempData();
  }

  Future<void> clearTempData() async {
    await _prefs.remove(_tempPhoneKey);
    await _prefs.remove(_tempUserIdKey);
  }
  
  // App settings methods
  Future<void> setThemeMode(String mode) async {
    await _prefs.setString(_themeModeKey, mode);
  }

  String getThemeMode() {
    return _prefs.getString(_themeModeKey) ?? 'system';
  }

  Future<void> setLanguageCode(String code) async {
    await _prefs.setString(_languageCodeKey, code);
  }

  String getLanguageCode() {
    return _prefs.getString(_languageCodeKey) ?? 'en';
  }

  Future<void> setNotificationEnabled(bool enabled) async {
    await _prefs.setBool(_notificationEnabledKey, enabled);
  }

  bool getNotificationEnabled() {
    return _prefs.getBool(_notificationEnabledKey) ?? true;
  }

  Future<void> setBiometricEnabled(bool enabled) async {
    await _prefs.setBool(_biometricEnabledKey, enabled);
  }

  bool getBiometricEnabled() {
    return _prefs.getBool(_biometricEnabledKey) ?? false;
  }

  // Recent searches
  Future<void> addRecentSearch(Map<String, dynamic> search) async {
    final searches = getRecentSearches();
    // Remove duplicate if exists
    searches.removeWhere(
      (s) =>
          s['departure_port'] == search['departure_port'] &&
          s['arrival_port'] == search['arrival_port'],
    );
    // Add new search at the beginning
    searches.insert(0, search);
    // Limit to 10 recent searches
    if (searches.length > 10) {
      searches.removeLast();
    }
    await _prefs.setString(_recentSearchesKey, jsonEncode(searches));
  }

  List<Map<String, dynamic>> getRecentSearches() {
    final searchesJson = _prefs.getString(_recentSearchesKey);
    if (searchesJson == null) {
      return [];
    }
    return List<Map<String, dynamic>>.from(
      jsonDecode(searchesJson).map((x) => Map<String, dynamic>.from(x)),
    );
  }

  Future<void> clearRecentSearches() async {
    await _prefs.remove(_recentSearchesKey);
  }

  // Saved passengers
  Future<void> savePassenger(Map<String, dynamic> passenger) async {
    final passengers = getSavedPassengers();
    // Update if exists, add if new
    final index = passengers.indexWhere((p) => p['id'] == passenger['id']);
    if (index >= 0) {
      passengers[index] = passenger;
    } else {
      passengers.add(passenger);
    }
    await _prefs.setString(_savedPassengersKey, jsonEncode(passengers));
  }

  List<Map<String, dynamic>> getSavedPassengers() {
    final passengersJson = _prefs.getString(_savedPassengersKey);
    if (passengersJson == null) {
      return [];
    }
    return List<Map<String, dynamic>>.from(
      jsonDecode(passengersJson).map((x) => Map<String, dynamic>.from(x)),
    );
  }

  Future<void> removePassenger(int id) async {
    final passengers = getSavedPassengers();
    passengers.removeWhere((p) => p['id'] == id);
    await _prefs.setString(_savedPassengersKey, jsonEncode(passengers));
  }

  // Saved vehicles
  Future<void> saveVehicle(Map<String, dynamic> vehicle) async {
    final vehicles = getSavedVehicles();
    // Update if exists, add if new
    final index = vehicles.indexWhere(
      (v) => v['license_plate'] == vehicle['license_plate'],
    );
    if (index >= 0) {
      vehicles[index] = vehicle;
    } else {
      vehicles.add(vehicle);
    }
    await _prefs.setString(_savedVehiclesKey, jsonEncode(vehicles));
  }

  List<Map<String, dynamic>> getSavedVehicles() {
    final vehiclesJson = _prefs.getString(_savedVehiclesKey);
    if (vehiclesJson == null) {
      return [];
    }
    return List<Map<String, dynamic>>.from(
      jsonDecode(vehiclesJson).map((x) => Map<String, dynamic>.from(x)),
    );
  }

  Future<void> removeVehicle(String licensePlate) async {
    final vehicles = getSavedVehicles();
    vehicles.removeWhere((v) => v['license_plate'] == licensePlate);
    await _prefs.setString(_savedVehiclesKey, jsonEncode(vehicles));
  }

  // Clear all data
  Future<void> clearAll() async {
    await _prefs.clear();
  }
}