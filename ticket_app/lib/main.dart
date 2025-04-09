import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
// import 'package:firebase_core/firebase_core.dart';
// import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;

import 'app.dart';
import 'services/storage_service.dart';
// Komentar impor lama
// import 'services/notification_service.dart';
// Impor versi baru dari notification service (pastikan path sesuai)
import 'services/notification_service.dart';
import 'providers/auth_provider.dart';
import 'providers/booking_provider.dart';
import 'providers/ferry_provider.dart';
import 'providers/ticket_provider.dart';
// Import ThemeProvider
import 'providers/theme_provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize shared preferences
  final prefs = await SharedPreferences.getInstance();
  final storageService = StorageService(prefs);

  // Initialize notification service with HTTP polling instead of Firebase
  final notificationService = NotificationService(
    baseUrl: 'http://127.0.0.1:8000/api/v1', // Ganti dengan URL API Anda
  );
  await notificationService.initialize();

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider(storageService)),
        // Meneruskan storageService ke FerryProvider
        ChangeNotifierProvider(create: (_) => FerryProvider(storageService)),
        ChangeNotifierProvider(create: (_) => BookingProvider()),
        // Meneruskan storageService ke TicketProvider
        ChangeNotifierProvider(create: (_) => TicketProvider(storageService)),
        // Tambahkan ThemeProvider
        ChangeNotifierProvider(create: (_) => ThemeProvider(storageService)),
        Provider<StorageService>.value(value: storageService),
        Provider<NotificationService>.value(value: notificationService),
      ],
      child: const FerryTicketApp(),
    ),
  );
}