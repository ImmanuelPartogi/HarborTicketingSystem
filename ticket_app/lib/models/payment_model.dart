import 'package:flutter/material.dart';
import 'dart:convert';

class Payment {
  final int id;
  final int bookingId;
  final String paymentMethod;
  final String paymentChannel;
  final double amount;
  final String status; // 'PENDING', 'SUCCESS', 'FAILED', 'EXPIRED', 'REFUNDED'
  final String? transactionId;
  final String? paymentCode;
  final String? paymentUrl;
  final Map<String, dynamic>? paymentData;
  final DateTime expiredAt;
  final DateTime? paidAt;
  final DateTime createdAt;
  final DateTime updatedAt;

  Payment({
    required this.id,
    required this.bookingId,
    required this.paymentMethod,
    required this.paymentChannel,
    required this.amount,
    required this.status,
    this.transactionId,
    this.paymentCode,
    this.paymentUrl,
    this.paymentData,
    required this.expiredAt,
    this.paidAt,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Payment.fromJson(Map<String, dynamic> json) {
    // Helper functions untuk parsing nilai dengan aman
    double parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      return double.tryParse(value.toString()) ?? 0.0;
    }

    DateTime parseDateTime(dynamic value) {
      if (value == null) return DateTime.now();
      if (value is DateTime) return value;
      try {
        return DateTime.parse(value.toString());
      } catch (e) {
        print('Error parsing date: $e');
        return DateTime.now();
      }
    }

    DateTime? parseNullableDateTime(dynamic value) {
      if (value == null) return null;
      if (value is DateTime) return value;
      try {
        return DateTime.parse(value.toString());
      } catch (e) {
        print('Error parsing nullable date: $e');
        return null;
      }
    }

    // PERBAIKAN UTAMA: Ekstraksi data pembayaran Midtrans dari payload
    Map<String, dynamic>? parsePayloadData(dynamic value) {
      print('Parsing payload: $value');
      if (value == null) return null;

      // Jika sudah berbentuk Map, gunakan langsung
      if (value is Map<String, dynamic>) return value;

      // Jika berbentuk string JSON, parse terlebih dahulu
      if (value is String) {
        try {
          var decoded = jsonDecode(value);
          print('Decoded payload JSON: $decoded');

          if (decoded is Map<String, dynamic>) {
            // Jika ada payment_data di dalam payload, ekstrak itu
            if (decoded.containsKey('payment_data') &&
                decoded['payment_data'] != null) {
              var paymentData = decoded['payment_data'];

              // Jika payment_data masih berupa string JSON, parse lagi
              if (paymentData is String) {
                try {
                  var parsedPaymentData = jsonDecode(paymentData);
                  print('Parsed payment_data: $parsedPaymentData');
                  return parsedPaymentData;
                } catch (e) {
                  print('Error parsing payment_data string: $e');
                }
              } else if (paymentData is Map<String, dynamic>) {
                return paymentData;
              }
            }
            // Jika tidak ada payment_data, gunakan payload langsung
            return decoded;
          }
        } catch (e) {
          print('Error parsing payload JSON: $e');
        }
      }
      return null;
    }

    var payloadData = parsePayloadData(json['payload']);
    print('Final parsed payment data: $payloadData');

    return Payment(
      id: json['id'] ?? 0,
      bookingId: json['booking_id'] ?? 0,
      paymentMethod: json['payment_method'] ?? '',
      paymentChannel: json['payment_channel'] ?? '',
      amount: parseDouble(json['amount']),
      status: json['status'] ?? 'PENDING',
      transactionId: json['transaction_id'],
      paymentCode: json['payment_code'],
      paymentUrl: json['payment_url'],
      paymentData: payloadData,
      expiredAt: parseDateTime(json['expiry_date'] ?? json['expired_at']),
      paidAt: parseNullableDateTime(json['payment_date'] ?? json['paid_at']),
      createdAt: parseDateTime(json['created_at']),
      updatedAt: parseDateTime(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'booking_id': bookingId,
      'payment_method': paymentMethod,
      'payment_channel': paymentChannel,
      'amount': amount,
      'status': status,
      'transaction_id': transactionId,
      'payment_code': paymentCode,
      'payment_url': paymentUrl,
      'payment_data': paymentData,
      'expired_at': expiredAt.toIso8601String(),
      'paid_at': paidAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get statusText {
    switch (status.toUpperCase()) {
      case 'PENDING':
        return 'Pending';
      case 'SUCCESS':
        return 'Completed';
      case 'FAILED':
        return 'Failed';
      case 'EXPIRED':
        return 'Expired';
      case 'REFUNDED':
        return 'Refunded';
      default:
        return 'Unknown';
    }
  }

  Color get statusColor {
    switch (status.toUpperCase()) {
      case 'PENDING':
        return Colors.orange;
      case 'SUCCESS':
        return Colors.green;
      case 'FAILED':
        return Colors.red;
      case 'EXPIRED':
        return Colors.grey;
      case 'REFUNDED':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  String get paymentMethodText {
    switch (paymentMethod.toUpperCase()) {
      case 'VIRTUAL_ACCOUNT':
        return 'Virtual Account';
      case 'E_WALLET':
        return 'E-Wallet';
      case 'BANK_TRANSFER':
        return 'Bank Transfer';
      case 'CREDIT_CARD':
        return 'Credit Card';
      default:
        return paymentMethod;
    }
  }

  String get paymentChannelText {
    switch (paymentChannel.toUpperCase()) {
      case 'BCA':
        return 'BCA';
      case 'BNI':
        return 'BNI';
      case 'BRI':
        return 'BRI';
      case 'MANDIRI':
        return 'Mandiri';
      case 'PERMATA':
        return 'Permata';
      case 'GOPAY':
        return 'GoPay';
      case 'OVO':
        return 'OVO';
      case 'DANA':
        return 'DANA';
      case 'SHOPEEPAY':
        return 'ShopeePay';
      default:
        return paymentChannel;
    }
  }

  // PERBAIKAN: Ekstraksi VA number dari format Midtrans
  String? get vaNumber {
    if (paymentData == null) return null;

    if (paymentMethod.toUpperCase() == 'VIRTUAL_ACCOUNT') {
      print('Extracting VA for ${paymentChannel.toUpperCase()}: $paymentData');

      // Format untuk BNI atau bank lain dengan va_numbers array
      if (paymentData!.containsKey('va_numbers')) {
        final vaNumbers = paymentData!['va_numbers'] as List<dynamic>?;
        if (vaNumbers != null && vaNumbers.isNotEmpty) {
          print('Found va_numbers: $vaNumbers');
          return vaNumbers[0]['va_number']?.toString();
        }
      }

      // Alternatif format untuk BNI
      if (paymentData!.containsKey('virtual_account')) {
        print('Found virtual_account: ${paymentData!['virtual_account']}');
        return paymentData!['virtual_account']?.toString();
      }

      // Format untuk Permata
      if (paymentData!.containsKey('permata_va_number')) {
        print('Found permata_va_number: ${paymentData!['permata_va_number']}');
        return paymentData!['permata_va_number']?.toString();
      }

      // Format langsung va_number
      if (paymentData!.containsKey('va_number')) {
        print('Found va_number: ${paymentData!['va_number']}');
        return paymentData!['va_number']?.toString();
      }

      // Format untuk Mandiri Bill Payment
      if (paymentData!.containsKey('bill_key')) {
        print('Found bill_key: ${paymentData!['bill_key']}');
        return paymentData!['bill_key']?.toString();
      }
    }

    return null;
  }

  // PERBAIKAN: Getter untuk nama bank
  String? get bankName {
    if (paymentData == null) return null;

    if (paymentMethod.toUpperCase() == 'VIRTUAL_ACCOUNT') {
      if (paymentData!.containsKey('va_numbers')) {
        final vaNumbers = paymentData!['va_numbers'] as List<dynamic>?;
        if (vaNumbers != null && vaNumbers.isNotEmpty) {
          return vaNumbers[0]['bank']?.toString();
        }
      }

      if (paymentData!.containsKey('bank')) {
        return paymentData!['bank']?.toString();
      }
    }
    return paymentChannel;
  }

  // PERBAIKAN: Getter untuk URL QR Code (E-Wallet)
  String? get qrCodeUrl {
    if (paymentData == null) return null;

    if (paymentMethod.toUpperCase() == 'E_WALLET') {
      // Cek actions untuk QR Code
      if (paymentData!.containsKey('actions')) {
        final actions = paymentData!['actions'] as List<dynamic>?;
        if (actions != null) {
          for (final action in actions) {
            if (action['name'] == 'generate-qr-code') {
              print('Found QR Code URL: ${action['url']}');
              return action['url']?.toString();
            }
          }
        }
      }

      // Cek field langsung
      if (paymentData!.containsKey('qr_code_url')) {
        print('Found direct QR code URL: ${paymentData!['qr_code_url']}');
        return paymentData!['qr_code_url']?.toString();
      }
    }
    return null;
  }

  // PERBAIKAN: Getter untuk URL Deep Link (E-Wallet)
  String? get deepLinkUrl {
    if (paymentData == null) return null;

    if (paymentMethod.toUpperCase() == 'E_WALLET') {
      // Cek actions untuk deeplink
      if (paymentData!.containsKey('actions')) {
        final actions = paymentData!['actions'] as List<dynamic>?;
        if (actions != null) {
          for (final action in actions) {
            if (action['name'] == 'deeplink-redirect') {
              print('Found deeplink URL: ${action['url']}');
              return action['url']?.toString();
            }
          }
        }
      }
    }
    return null;
  }

  bool get isPending {
    return status.toUpperCase() == 'PENDING';
  }

  bool get isCompleted {
    return status.toUpperCase() == 'SUCCESS';
  }

  bool get isFailed {
    return status.toUpperCase() == 'FAILED';
  }

  bool get isExpired {
    return status.toUpperCase() == 'EXPIRED';
  }

  bool get isRefunded {
    return status.toUpperCase() == 'REFUNDED';
  }

  bool get hasVaNumber {
    return vaNumber != null && vaNumber!.isNotEmpty;
  }

  void validatePaymentData() {
    if (paymentData != null && vaNumber != null) {
      print(
        'PAYMENT VALIDATION: Transaction ID: $transactionId, VA: $vaNumber',
      );
    }
  }
}
