import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mockito/mockito.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

// Import aplikasi utama
import 'package:ticket_app/app.dart';
import 'package:ticket_app/providers/auth_provider.dart';
import 'package:ticket_app/providers/booking_provider.dart';
import 'package:ticket_app/providers/ferry_provider.dart';
import 'package:ticket_app/providers/ticket_provider.dart';
import 'package:ticket_app/services/storage_service.dart';
import 'package:ticket_app/services/notification_service.dart';

// Mock classes
class MockAuthProvider extends Mock implements AuthProvider {
  @override
  Future<bool> isLoggedIn() async => false;
}

class MockBookingProvider extends Mock implements BookingProvider {}
class MockFerryProvider extends Mock implements FerryProvider {}
class MockTicketProvider extends Mock implements TicketProvider {}
class MockStorageService extends Mock implements StorageService {}
class MockNotificationService extends Mock implements NotificationService {}

void main() {
  // Setup untuk pengujian
  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  testWidgets('Aplikasi ferry ticket dapat dimuat tanpa crash', (WidgetTester tester) async {
    // Siapkan mock providers
    final mockAuthProvider = MockAuthProvider();
    final mockBookingProvider = MockBookingProvider();
    final mockFerryProvider = MockFerryProvider();
    final mockTicketProvider = MockTicketProvider();
    final mockStorageService = MockStorageService();
    final mockNotificationService = MockNotificationService();

    // Build aplikasi dengan providers yang sudah di-mock
    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AuthProvider>.value(value: mockAuthProvider),
          ChangeNotifierProvider<BookingProvider>.value(value: mockBookingProvider),
          ChangeNotifierProvider<FerryProvider>.value(value: mockFerryProvider),
          ChangeNotifierProvider<TicketProvider>.value(value: mockTicketProvider),
          Provider<StorageService>.value(value: mockStorageService),
          Provider<NotificationService>.value(value: mockNotificationService),
        ],
        child: const FerryTicketApp(),
      ),
    );

    // Verifikasi bahwa aplikasi dimuat tanpa crash
    expect(find.byType(MaterialApp), findsOneWidget);
    
    // Tunggu semua animasi selesai
    await tester.pumpAndSettle();
    
    // Karena kita mengatur auth provider untuk return false pada isLoggedIn,
    // seharusnya aplikasi menampilkan layar login
    expect(find.byType(CircularProgressIndicator), findsNothing);
    // Tambahkan pengujian lebih spesifik sesuai dengan UI login Anda
    // misalnya: expect(find.text('Masuk'), findsOneWidget);
  });

  // Anda dapat menambahkan lebih banyak pengujian untuk fitur-fitur khusus
}