import 'dart:async';
import 'dart:convert';
// Import io conditionally to support web
import 'package:flutter/foundation.dart' show kIsWeb;
// Only import dart:io if not on web
import 'dart:io' if (dart.library.js) 'dart:html' as platform;

import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:http/http.dart' as http;

class NotificationModel {
  final int id;
  final String title;
  final String message;
  final String type;
  final bool isRead;
  final DateTime createdAt;

  NotificationModel({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.isRead,
    required this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'],
      title: json['title'],
      message: json['message'],
      type: json['type'],
      isRead: json['is_read'] == 1 || json['is_read'] == true,
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}

class NotificationService {
  // API Configuration
  final String _baseUrl;
  String? _authToken;

  // Local notifications
  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();

  // Notification channels
  static const String _bookingChannel = 'booking_notifications';
  static const String _paymentChannel = 'payment_notifications';
  static const String _departureChannel = 'departure_notifications';
  static const String _generalChannel = 'general_notifications';

  // Notification IDs for local notifications
  static int _notificationId = 0;

  // Stream controller for broadcasting notification events
  final StreamController<List<NotificationModel>> _notificationStreamController = 
      StreamController<List<NotificationModel>>.broadcast();
  
  // Public stream for notification events
  Stream<List<NotificationModel>> get notificationStream => _notificationStreamController.stream;

  // Callback for handling notification taps
  Function(String? payload)? onNotificationTap;

  // List of current notifications
  List<NotificationModel> _currentNotifications = [];

  NotificationService({
    required String baseUrl,
  }) : _baseUrl = baseUrl;

  Future<void> initialize() async {
    // Skip notification setup on web as it's not fully supported
    if (kIsWeb) {
      debugPrint('Web platform detected. Local notifications are limited on web.');
      return;
    }

    // Configure local notifications
    const AndroidInitializationSettings androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    final DarwinInitializationSettings iosSettings =
        DarwinInitializationSettings(
          requestSoundPermission: true,
          requestBadgePermission: true,
          requestAlertPermission: true,
        );

    final InitializationSettings initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (NotificationResponse details) {
        if (onNotificationTap != null) {
          onNotificationTap!(details.payload);
        }
      },
    );

    // Create notification channels for Android
    // Safely check if we're on Android without using Platform directly
    try {
      // Only try to create Android channels on actual Android devices
      if (!kIsWeb) {
        final androidPlugin = _localNotifications
            .resolvePlatformSpecificImplementation<
                AndroidFlutterLocalNotificationsPlugin>();
                
        if (androidPlugin != null) {
          await _createNotificationChannels(androidPlugin);
        }
      }
    } catch (e) {
      debugPrint('Error creating notification channels: $e');
    }
    
    debugPrint('HTTP Notification Service initialized successfully');
  }

  Future<void> _createNotificationChannels(
      AndroidFlutterLocalNotificationsPlugin androidPlugin) async {
    final List<AndroidNotificationChannel> channels = [
      AndroidNotificationChannel(
        _bookingChannel,
        'Booking Notifications',
        description: 'Notifications related to bookings',
        importance: Importance.high,
      ),
      AndroidNotificationChannel(
        _paymentChannel,
        'Payment Notifications',
        description: 'Notifications related to payments',
        importance: Importance.high,
      ),
      AndroidNotificationChannel(
        _departureChannel,
        'Departure Notifications',
        description: 'Notifications related to ferry departures',
        importance: Importance.high,
      ),
      AndroidNotificationChannel(
        _generalChannel,
        'General Notifications',
        description: 'General app notifications',
        importance: Importance.defaultImportance,
      ),
    ];

    for (final channel in channels) {
      await androidPlugin.createNotificationChannel(channel);
    }
  }

  // Set authentication token
  Future<void> setAuthToken(String token) async {
    _authToken = token;
    debugPrint('Auth token set successfully');
  }
  
  // Start polling for notifications
  void startPolling() {
    debugPrint('Notification polling started');
    // We'll implement this later - just a placeholder for compatibility
  }
  
  // Stop polling for notifications
  void stopPolling() {
    debugPrint('Notification polling stopped');
    // We'll implement this later - just a placeholder for compatibility
  }

  // Fetch notifications from the API - placeholder implementation
  Future<void> fetchNotifications() async {
    debugPrint('Fetching notifications (placeholder)');
    // We'll implement API fetching later
    // For now, just emit an empty list to prevent errors
    _notificationStreamController.add([]);
  }

  // Show a local notification
  Future<void> _showLocalNotification({
    required String title,
    required String body,
    String? payload,
    String channelId = 'general_notifications',
  }) async {
    // Skip on web
    if (kIsWeb) {
      debugPrint('Notification would show: $title - $body');
      return;
    }
    
    try {
      AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
        channelId,
        channelId == _bookingChannel 
            ? 'Booking Notifications'
            : channelId == _paymentChannel
                ? 'Payment Notifications'
                : channelId == _departureChannel
                    ? 'Departure Notifications'
                    : 'General Notifications',
        channelDescription: 'App notifications',
        importance: Importance.max,
        priority: Priority.high,
        showWhen: true,
      );

      const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      );

      NotificationDetails details = NotificationDetails(
        android: androidDetails,
        iOS: iosDetails,
      );

      await _localNotifications.show(
        _getNextNotificationId(),
        title,
        body,
        details,
        payload: payload,
      );
    } catch (e) {
      debugPrint('Error showing notification: $e');
    }
  }

  // Utility methods for showing specific notifications
  Future<void> showBookingConfirmationNotification(String bookingNumber) async {
    await _showLocalNotification(
      title: 'Booking Confirmed',
      body: 'Your booking $bookingNumber has been confirmed successfully.',
      channelId: _bookingChannel,
      payload: '/booking-confirmation',
    );
  }

  Future<void> showPaymentConfirmationNotification(String bookingNumber) async {
    await _showLocalNotification(
      title: 'Payment Successful',
      body: 'Your payment for booking $bookingNumber has been received.',
      channelId: _paymentChannel,
      payload: '/payment-success',
    );
  }

  Future<void> showDepartureReminderNotification(
    String departureTime,
    String route,
  ) async {
    await _showLocalNotification(
      title: 'Departure Reminder',
      body: 'Your ferry from $route departs in 1 hour at $departureTime.',
      channelId: _departureChannel,
      payload: '/ticket-detail',
    );
  }

  // Helper utilities
  Future<void> cancelAllNotifications() async {
    if (!kIsWeb) {
      await _localNotifications.cancelAll();
    }
  }

  Future<void> cancelNotification(int id) async {
    if (!kIsWeb) {
      await _localNotifications.cancel(id);
    }
  }

  int _getNextNotificationId() {
    _notificationId++;
    return _notificationId;
  }

  // Get token placeholder method for API compatibility
  Future<String?> getToken() async {
    return null; // Placeholder - we don't use Firebase tokens anymore
  }

  // Basic placeholders for compatibility
  Future<void> clearAuthToken() async {}
  Future<bool> markAsRead(int notificationId) async => true;
  Future<bool> markAllAsRead() async => true;
  int getUnreadCount() => 0;
  List<NotificationModel> getAllNotifications() => [];
}