import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';

import '../models/payment_model.dart';
import '../config/api_config.dart';
import 'api_service.dart';

class PaymentService {
  final ApiService _apiService;

  PaymentService(this._apiService);

  Future<Map<String, dynamic>> createPayment({
    required String bookingIdentifier,
    required String paymentMethod,
    required String paymentChannel,
  }) async {
    // PERBAIKAN: Pastikan endpoint menggunakan booking code
    print('Creating payment for booking: $bookingIdentifier');
    print('Menunggu sebelum mencoba memproses pembayaran...');
    await Future.delayed(Duration(seconds: 2));

    try {
      final response = await _apiService.post(
        '/api/v1/bookings/$bookingIdentifier/pay',
        body: {
          'payment_method': paymentMethod,
          'payment_channel': paymentChannel,
        },
      );

      return response;
    } catch (e) {
      print('Error processing payment, retrying in 2s (1/3)');
      await Future.delayed(Duration(seconds: 2));

      try {
        // Coba alternatif endpoint jika yang pertama gagal
        print(
          'Trying alternative endpoint: /api/v1/payments/booking/$bookingIdentifier',
        );
        final response = await _apiService.post(
          '/api/v1/payments/booking/$bookingIdentifier',
          body: {
            'payment_method': paymentMethod,
            'payment_channel': paymentChannel,
          },
        );

        return response;
      } catch (innerError) {
        print('Error processing payment, retrying in 2s (2/3)');
        await Future.delayed(Duration(seconds: 2));

        try {
          // Coba sekali lagi dengan endpoint orisinal
          final response = await _apiService.post(
            '/api/v1/bookings/$bookingIdentifier/pay',
            body: {
              'payment_method': paymentMethod,
              'payment_channel': paymentChannel,
            },
            bypassThrottling: true,
          );

          return response;
        } catch (finalError) {
          print('Max retries exceeded for payment processing');
          throw Exception('Unexpected error: $finalError');
        }
      }
    }
  }

  Future<bool> openPaymentUrl(String url) async {
    try {
      if (await canLaunch(url)) {
        return await launch(
          url,
          forceSafariVC: true,
          forceWebView: false,
          enableJavaScript: true,
        );
      } else {
        debugPrint('Could not launch URL: $url');
        return false;
      }
    } catch (e) {
      debugPrint('Error launching payment URL: $e');
      return false;
    }
  }

  Future<bool> checkPaymentStatus(int paymentId) async {
    try {
      final response = await _apiService.get(
        ApiConfig.bookings + '/payment-status',
        queryParams: {'payment_id': paymentId.toString()},
      );

      if (response['success'] == true && response['data'] != null) {
        final status = response['data']['status'];
        return status == 'SUCCESS'; // Return true if payment is successful
      }

      return false;
    } catch (e) {
      debugPrint('Error checking payment status: $e');
      return false;
    }
  }

  // Get available payment methods with proper configuration for Midtrans
  List<Map<String, dynamic>> getAvailablePaymentMethods() {
    return [
      // Virtual Account methods
      {
        'id': 'bca',
        'name': 'BCA Virtual Account',
        'type': 'virtual_account',
        'icon': 'assets/images/payment_methods/bca.png',
      },
      {
        'id': 'bni',
        'name': 'BNI Virtual Account',
        'type': 'virtual_account',
        'icon': 'assets/images/payment_methods/bni.png',
      },
      {
        'id': 'bri',
        'name': 'BRI Virtual Account',
        'type': 'virtual_account',
        'icon': 'assets/images/payment_methods/bri.png',
      },
      {
        'id': 'mandiri',
        'name': 'Mandiri Bill Payment',
        'type': 'virtual_account',
        'icon': 'assets/images/payment_methods/mandiri.png',
      },
      {
        'id': 'permata',
        'name': 'Permata Virtual Account',
        'type': 'virtual_account',
        'icon': 'assets/images/payment_methods/permata.png',
      },

      // E-Wallet methods
      {
        'id': 'gopay',
        'name': 'GoPay',
        'type': 'e_wallet',
        'icon': 'assets/images/payment_methods/gopay.png',
      },
      {
        'id': 'shopeepay',
        'name': 'ShopeePay',
        'type': 'e_wallet',
        'icon': 'assets/images/payment_methods/shopeepay.png',
      },
      {
        'id': 'dana',
        'name': 'DANA',
        'type': 'e_wallet',
        'icon': 'assets/images/payment_methods/dana.png',
      },
      {
        'id': 'ovo',
        'name': 'OVO',
        'type': 'e_wallet',
        'icon': 'assets/images/payment_methods/ovo.png',
      },
    ];
  }

  // Filter payment methods by type
  List<Map<String, dynamic>> getPaymentMethodsByType(String type) {
    final methods = getAvailablePaymentMethods();
    return methods.where((method) => method['type'] == type).toList();
  }

  // Get payment instructions based on method and channel
  Map<String, String> getPaymentInstructions(
    String paymentMethod,
    String paymentType,
  ) {
    if (paymentType == 'virtual_account') {
      return getVirtualAccountInstructions(paymentMethod);
    } else if (paymentType == 'e_wallet') {
      return getEWalletInstructions(paymentMethod);
    } else {
      return {
        'title': 'Payment Instructions',
        'steps': 'Follow the instructions on the payment page.',
      };
    }
  }

  Map<String, String> getVirtualAccountInstructions(String bank) {
    switch (bank.toLowerCase()) {
      case 'bca':
        return {
          'title': 'BCA Virtual Account Payment Instructions',
          'steps': '''
1. Login to your BCA Mobile Banking app or Internet Banking.
2. Choose "Transfer" > "Virtual Account".
3. Enter BCA Virtual Account number as shown above.
4. Confirm the payment details and total amount to be paid.
5. Enter your PIN or password to authorize the payment.
6. Save your payment receipt as proof of transaction.
''',
        };
      case 'bni':
        return {
          'title': 'BNI Virtual Account Payment Instructions',
          'steps': '''
1. Login to your BNI Mobile Banking app or Internet Banking.
2. Choose "Transfer" > "Virtual Account".
3. Enter BNI Virtual Account number as shown above.
4. Confirm the payment details and total amount to be paid.
5. Enter your PIN or password to authorize the payment.
6. Save your payment receipt as proof of transaction.
''',
        };
      case 'bri':
        return {
          'title': 'BRI Virtual Account Payment Instructions',
          'steps': '''
1. Login to your BRI Mobile Banking app or Internet Banking.
2. Choose "Transfer" > "Virtual Account".
3. Enter BRI Virtual Account number as shown above.
4. Confirm the payment details and total amount to be paid.
5. Enter your PIN or password to authorize the payment.
6. Save your payment receipt as proof of transaction.
''',
        };
      case 'mandiri':
        return {
          'title': 'Mandiri Bill Payment Instructions',
          'steps': '''
1. Login to your Mandiri Mobile Banking app or Internet Banking.
2. Choose "Bill Payment" > "Multi Payment".
3. Select "Ferry Company" as the biller.
4. Enter your payment code as shown above.
5. Confirm the payment details and total amount to be paid.
6. Enter your PIN or password to authorize the payment.
7. Save your payment receipt as proof of transaction.
''',
        };
      case 'permata':
        return {
          'title': 'Permata Virtual Account Payment Instructions',
          'steps': '''
1. Login to your Permata Mobile Banking app or Internet Banking.
2. Choose "Transfer" > "Virtual Account".
3. Enter Permata Virtual Account number as shown above.
4. Confirm the payment details and total amount to be paid.
5. Enter your PIN or password to authorize the payment.
6. Save your payment receipt as proof of transaction.
''',
        };
      default:
        return {
          'title': 'Virtual Account Payment Instructions',
          'steps': '''
1. Login to your mobile banking app or internet banking.
2. Choose "Transfer" > "Virtual Account" or similar option.
3. Enter the Virtual Account number as shown above.
4. Confirm the payment details and total amount to be paid.
5. Complete the transaction by following your bank's security procedures.
6. Save your payment receipt as proof of transaction.
''',
        };
    }
  }

  Map<String, String> getEWalletInstructions(String wallet) {
    switch (wallet.toLowerCase()) {
      case 'gopay':
        return {
          'title': 'GoPay Payment Instructions',
          'steps': '''
1. Tap the "Pay Now" button below.
2. You'll be redirected to the GoPay app or a QR code page.
3. If using the app, confirm the payment details and complete the payment.
4. If using QR code, open your GoPay app, tap "Pay", and scan the QR code.
5. Enter your PIN to authorize the payment.
6. Wait for confirmation, and you'll be redirected back to this app.
''',
        };
      case 'shopeepay':
        return {
          'title': 'ShopeePay Payment Instructions',
          'steps': '''
1. Tap the "Pay Now" button below.
2. You'll be redirected to the Shopee app.
3. Confirm the payment details in the Shopee app.
4. Enter your PIN to authorize the payment.
5. Wait for confirmation, and you'll be redirected back to this app.
''',
        };
      case 'dana':
        return {
          'title': 'DANA Payment Instructions',
          'steps': '''
1. Tap the "Pay Now" button below.
2. You'll be redirected to the DANA app or website.
3. Login to your DANA account if needed.
4. Confirm the payment details and complete the payment.
5. Enter your PIN to authorize the payment.
6. Wait for confirmation, and you'll be redirected back to this app.
''',
        };
      case 'ovo':
        return {
          'title': 'OVO Payment Instructions',
          'steps': '''
1. Tap the "Pay Now" button below.
2. You'll be redirected to the OVO app or a QR code page.
3. If using the app, confirm the payment details and complete the payment.
4. If using QR code, open your OVO app, tap "Scan", and scan the QR code.
5. Enter your PIN to authorize the payment.
6. Wait for confirmation, and you'll be redirected back to this app.
''',
        };
      default:
        return {
          'title': 'E-Wallet Payment Instructions',
          'steps': '''
1. Tap the "Pay Now" button below.
2. You'll be redirected to your e-wallet app or website.
3. Login to your account if needed.
4. Confirm the payment details and complete the payment.
5. Wait for confirmation, and you'll be redirected back to this app.
''',
        };
    }
  }
}
