class Payment {
  final int id;
  final int bookingId;
  final String paymentMethod;
  final String paymentType; // 'bank_transfer', 'e_wallet', 'virtual_account'
  final double amount;
  final String status; // 'pending', 'completed', 'failed', 'expired', 'refunded'
  final String? transactionId;
  final String? paymentCode;
  final String? paymentUrl;
  final DateTime expiredAt;
  final DateTime? paidAt;
  final DateTime createdAt;
  final DateTime updatedAt;

  Payment({
    required this.id,
    required this.bookingId,
    required this.paymentMethod,
    required this.paymentType,
    required this.amount,
    required this.status,
    this.transactionId,
    this.paymentCode,
    this.paymentUrl,
    required this.expiredAt,
    this.paidAt,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Payment.fromJson(Map<String, dynamic> json) {
    return Payment(
      id: json['id'],
      bookingId: json['booking_id'],
      paymentMethod: json['payment_method'],
      paymentType: json['payment_type'],
      amount: json['amount'].toDouble(),
      status: json['status'],
      transactionId: json['transaction_id'],
      paymentCode: json['payment_code'],
      paymentUrl: json['payment_url'],
      expiredAt: DateTime.parse(json['expired_at']),
      paidAt: json['paid_at'] != null ? DateTime.parse(json['paid_at']) : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'booking_id': bookingId,
      'payment_method': paymentMethod,
      'payment_type': paymentType,
      'amount': amount,
      'status': status,
      'transaction_id': transactionId,
      'payment_code': paymentCode,
      'payment_url': paymentUrl,
      'expired_at': expiredAt.toIso8601String(),
      'paid_at': paidAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get statusText {
    switch (status) {
      case 'pending':
        return 'Pending';
      case 'completed':
        return 'Completed';
      case 'failed':
        return 'Failed';
      case 'expired':
        return 'Expired';
      case 'refunded':
        return 'Refunded';
      default:
        return 'Unknown';
    }
  }

  String get paymentMethodText {
    switch (paymentMethod.toLowerCase()) {
      case 'bni':
        return 'BNI';
      case 'bri':
        return 'BRI';
      case 'mandiri':
        return 'Mandiri';
      case 'dana':
        return 'DANA';
      case 'ovo':
        return 'OVO';
      default:
        return paymentMethod;
    }
  }

  String get paymentTypeText {
    switch (paymentType) {
      case 'bank_transfer':
        return 'Bank Transfer';
      case 'e_wallet':
        return 'E-Wallet';
      case 'virtual_account':
        return 'Virtual Account';
      default:
        return paymentType;
    }
  }

  bool get isPending {
    return status == 'pending';
  }

  bool get isCompleted {
    return status == 'completed';
  }

  bool get isFailed {
    return status == 'failed';
  }

  bool get isExpired {
    return status == 'expired';
  }

  bool get isRefunded {
    return status == 'refunded';
  }
}