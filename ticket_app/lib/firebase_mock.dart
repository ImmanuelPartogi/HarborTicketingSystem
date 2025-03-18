// Mock file for Firebase on web platform
// Create this file in your project's lib folder

import 'dart:async';

// Mock Firebase class
class Firebase {
  static Future<void> initializeApp() async {
    // Do nothing, this is a mock
    print('Mock Firebase initializeApp called');
    return;
  }
}

// Mock FirebaseMessaging class
class FirebaseMessaging {
  static FirebaseMessaging get instance => FirebaseMessaging();

  Future<void> requestPermission() async {
    // Do nothing, this is a mock
    print('Mock Firebase requestPermission called');
    return;
  }

  static void onBackgroundMessage(Function handler) {
    // Do nothing, this is a mock
    print('Mock Firebase onBackgroundMessage setup');
  }
}

// Mock RemoteMessage class
class RemoteMessage {
  final String? messageId;
  
  RemoteMessage({this.messageId});
}