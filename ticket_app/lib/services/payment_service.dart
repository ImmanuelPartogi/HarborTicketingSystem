import '../models/payment_model.dart';
import 'api_service.dart';

class PaymentService {
  final ApiService _apiService;

  PaymentService(this._apiService);

  Future<Payment> createPayment({
    required int bookingId,
    required String paymentMethod,
    required String paymentType,
  }) async {
    try {
      final response = await _apiService.createPayment(
        bookingId,
        paymentMethod,
        paymentType,
      );
      
      return Payment.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to create payment: ${e.toString()}');
    }
  }

  Future<Payment> getPaymentStatus(int id) async {
    try {
      final response = await _apiService.getPaymentStatus(id);
      return Payment.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to get payment status: ${e.toString()}');
    }
  }

  // Method to check if a payment needs follow-up
  Future<bool> checkPaymentStatus(int id) async {
    try {
      final payment = await getPaymentStatus(id);
      
      // If payment is completed, return true
      return payment.isCompleted;
    } catch (e) {
      return false;
    }
  }

  // Get available payment methods
  List<Map<String, dynamic>> getAvailablePaymentMethods() {
    return [
      {
        'id': 'bni',
        'name': 'BNI',
        'type': 'bank_transfer',
        'icon': 'assets/images/payment_methods/bni.png',
      },
      {
        'id': 'bri',
        'name': 'BRI',
        'type': 'bank_transfer',
        'icon': 'assets/images/payment_methods/bri.png',
      },
      {
        'id': 'mandiri',
        'name': 'Mandiri',
        'type': 'bank_transfer',
        'icon': 'assets/images/payment_methods/mandiri.png',
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

  // Get payment instructions based on method
  Map<String, String> getPaymentInstructions(String paymentMethod, String paymentType) {
    switch (paymentType) {
      case 'bank_transfer':
        return getBankTransferInstructions(paymentMethod);
      case 'e_wallet':
        return getEWalletInstructions(paymentMethod);
      case 'virtual_account':
        return getVirtualAccountInstructions(paymentMethod);
      default:
        return {'title': 'Payment Instructions', 'steps': 'Follow the instructions on the payment page.'};
    }
  }

  Map<String, String> getBankTransferInstructions(String paymentMethod) {
    switch (paymentMethod.toLowerCase()) {
      case 'bni':
        return {
          'title': 'BNI Bank Transfer Instructions',
          'steps': '''
1. Log in to BNI Mobile Banking.
2. Select "Transfer".
3. Enter the account number provided.
4. Enter the exact amount as shown.
5. Confirm your payment.
'''
        };
      case 'bri':
        return {
          'title': 'BRI Bank Transfer Instructions',
          'steps': '''
1. Log in to BRI Mobile Banking.
2. Select "Transfer".
3. Enter the account number provided.
4. Enter the exact amount as shown.
5. Confirm your payment.
'''
        };
      case 'mandiri':
        return {
          'title': 'Mandiri Bank Transfer Instructions',
          'steps': '''
1. Log in to Mandiri Mobile Banking.
2. Select "Transfer".
3. Enter the account number provided.
4. Enter the exact amount as shown.
5. Confirm your payment.
'''
        };
      default:
        return {
          'title': 'Bank Transfer Instructions',
          'steps': '''
1. Log in to your mobile banking app.
2. Select "Transfer".
3. Enter the account number provided.
4. Enter the exact amount as shown.
5. Confirm your payment.
'''
        };
    }
  }

  Map<String, String> getEWalletInstructions(String paymentMethod) {
    switch (paymentMethod.toLowerCase()) {
      case 'dana':
        return {
          'title': 'DANA Payment Instructions',
          'steps': '''
1. Open your DANA app.
2. Scan the QR code or click the payment link.
3. Enter the exact amount as shown.
4. Confirm your payment.
'''
        };
      case 'ovo':
        return {
          'title': 'OVO Payment Instructions',
          'steps': '''
1. Open your OVO app.
2. Scan the QR code or click the payment link.
3. Enter the exact amount as shown.
4. Confirm your payment.
'''
        };
      default:
        return {
          'title': 'E-Wallet Instructions',
          'steps': '''
1. Open your e-wallet app.
2. Scan the QR code or click the payment link.
3. Enter the exact amount as shown.
4. Confirm your payment.
'''
        };
    }
  }

  Map<String, String> getVirtualAccountInstructions(String paymentMethod) {
    return {
      'title': 'Virtual Account Instructions',
      'steps': '''
1. Note your Virtual Account number.
2. Log in to your mobile banking app.
3. Select "Virtual Account Payment" or similar option.
4. Enter the Virtual Account number provided.
5. Confirm the payment details.
6. Complete the payment.
'''
    };
  }
}