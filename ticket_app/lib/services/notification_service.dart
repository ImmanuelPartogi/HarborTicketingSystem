import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class NotificationService {
  final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();
  
  // Notification channels
  static const String _bookingChannel = 'booking_notifications';
  static const String _paymentChannel = 'payment_notifications';
  static const String _departureChannel = 'departure_notifications';
  static const String _generalChannel = 'general_notifications';
  
  // Notification IDs for local notifications
  static int _notificationId = 0;
  
  // Callback for handling notification taps
  Function(String? payload)? onNotificationTap;
  
  Future<void> initialize() async {
    // Request permission for iOS
    if (Platform.isIOS) {
      await _firebaseMessaging.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );
    }
    
    // Configure local notifications
    const AndroidInitializationSettings androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    
    final DarwinInitializationSettings iosSettings = DarwinInitializationSettings(
      requestSoundPermission: true,
      requestBadgePermission: true,
      requestAlertPermission: true,
      onDidReceiveLocalNotification: _onDidReceiveLocalNotification,
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
    if (Platform.isAndroid) {
      await _createNotificationChannels();
    }
    
    // Handle FCM messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      _handleRemoteMessage(message);
    });
    
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      if (onNotificationTap != null) {
        onNotificationTap!(message.data['route']);
      }
    });
  }
  
  Future<void> _createNotificationChannels() async {
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
      await _localNotifications
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(channel);
    }
  }
  
  void _handleRemoteMessage(RemoteMessage message) {
    final notification = message.notification;
    final data = message.data;
    
    if (notification != null) {
      String channelId = _generalChannel;
      
      // Determine the channel based on notification type
      if (data.containsKey('type')) {
        switch (data['type']) {
          case 'booking':
          case 'booking_confirmed':
          case 'booking_cancelled':
            channelId = _bookingChannel;
            break;
          case 'payment':
          case 'payment_confirmed':
          case 'payment_reminder':
            channelId = _paymentChannel;
            break;
          case 'departure':
          case 'departure_reminder':
          case 'schedule_changed':
            channelId = _departureChannel;
            break;
          default:
            channelId = _generalChannel;
        }
      }
      
      // Show local notification
      _showLocalNotification(
        title: notification.title ?? 'New Notification',
        body: notification.body ?? '',
        payload: data['route'],
        channelId: channelId,
      );
    }
  }
  
  Future<void> _showLocalNotification({
    required String title,
    required String body,
    String? payload,
    String channelId = 'general_notifications',
  }) async {
    const AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
      _generalChannel,
      'General Notifications',
      channelDescription: 'General app notifications',
      importance: Importance.max,
      priority: Priority.high,
      showWhen: true,
    );
    
    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );
    
    const NotificationDetails details = NotificationDetails(
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
  }
  
  void _onDidReceiveLocalNotification(int id, String? title, String? body, String? payload) {
    debugPrint('Local notification received: $id, $title, $body, $payload');
  }
  
  Future<String?> getToken() async {
    return await _firebaseMessaging.getToken();
  }
  
  Future<void> subscribeToTopic(String topic) async {
    await _firebaseMessaging.subscribeToTopic(topic);
  }
  
  Future<void> unsubscribeFromTopic(String topic) async {
    await _firebaseMessaging.unsubscribeFromTopic(topic);
  }
  
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
  
  Future<void> showDepartureReminderNotification(String departureTime, String route) async {
    await _showLocalNotification(
      title: 'Departure Reminder',
      body: 'Your ferry from $route departs in 1 hour at $departureTime.',
      channelId: _departureChannel,
      payload: '/ticket-detail',
    );
  }
  
  Future<void> showScheduleChangeNotification(String bookingNumber, String newTime) async {
    await _showLocalNotification(
      title: 'Schedule Change',
      body: 'Your booking $bookingNumber has been rescheduled to $newTime.',
      channelId: _departureChannel,
      payload: '/booking-detail',
    );
  }
  
  Future<void> showPaymentReminderNotification(String bookingNumber, String expiryTime) async {
    await _showLocalNotification(
      title: 'Payment Reminder',
      body: 'Your payment for booking $bookingNumber will expire in $expiryTime.',
      channelId: _paymentChannel,
      payload: '/payment',
    );
  }
  
  Future<void> cancelAllNotifications() async {
    await _localNotifications.cancelAll();
  }
  
  Future<void> cancelNotification(int id) async {
    await _localNotifications.cancel(id);
  }
  
  int _getNextNotificationId() {
    _notificationId++;
    return _notificationId;
  }
}