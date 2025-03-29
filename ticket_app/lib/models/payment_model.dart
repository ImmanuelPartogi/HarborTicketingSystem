import 'package:flutter/material.dart';
import 'dart:convert'; // Menambahkan import untuk jsonDecode

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
    // Helper functions to safely parse values
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

    Map<String, dynamic>? parsePaymentData(dynamic value) {
      if (value == null) return null;
      if (value is Map<String, dynamic>) return value;
      if (value is String) {
        try {
          // Menggunakan jsonDecode dari dart:convert, bukan json.decode
          final decoded = jsonDecode(value);
          if (decoded is Map<String, dynamic>) {
            return decoded;
          }
        } catch (e) {
          print('Error parsing payment data: $e');
        }
      }
      return null;
    }

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
      paymentData: parsePaymentData(json['payment_data']),
      expiredAt: parseDateTime(json['expired_at']),
      paidAt: parseNullableDateTime(json['paid_at']),
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

  // Getters for payment information
  String? get vaNumber {
    if (paymentData == null) return null;
    
    if (paymentMethod.toUpperCase() == 'VIRTUAL_ACCOUNT') {
      return paymentData!['va_number'] as String?;
    }
    return null;
  }

  String? get bankName {
    if (paymentData == null) return null;
    
    if (paymentMethod.toUpperCase() == 'VIRTUAL_ACCOUNT') {
      return paymentData!['bank'] as String?;
    }
    return paymentChannel;
  }

  String? get qrCodeUrl {
    if (paymentData == null) return null;
    
    if (paymentMethod.toUpperCase() == 'E_WALLET') {
      final actions = paymentData!['actions'] as List<dynamic>?;
      if (actions != null) {
        for (final action in actions) {
          if (action['name'] == 'generate-qr-code') {
            return action['url'] as String?;
          }
        }
      }
    }
    return null;
  }

  String? get deepLinkUrl {
    if (paymentData == null) return null;
    
    if (paymentMethod.toUpperCase() == 'E_WALLET') {
      final actions = paymentData!['actions'] as List<dynamic>?;
      if (actions != null) {
        for (final action in actions) {
          if (action['name'] == 'deeplink-redirect') {
            return action['url'] as String?;
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
}