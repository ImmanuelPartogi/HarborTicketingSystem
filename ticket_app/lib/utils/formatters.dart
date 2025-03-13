import 'package:flutter/services.dart';
import 'package:intl/intl.dart';

/// Utility class that provides text input formatters and formatting functions
class Formatters {
  /// Format a date as a human-readable string
  static String formatDate(DateTime date, {String format = 'EEE, dd MMM yyyy'}) {
    return DateFormat(format).format(date);
  }
  
  /// Format a time as a human-readable string
  static String formatTime(DateTime time, {String format = 'HH:mm'}) {
    return DateFormat(format).format(time);
  }
  
  /// Format a datetime as a human-readable string
  static String formatDateTime(DateTime dateTime, {String format = 'EEE, dd MMM yyyy HH:mm'}) {
    return DateFormat(format).format(dateTime);
  }
  
  /// Format a currency value
  static String formatCurrency(double amount, {String locale = 'id', String symbol = 'Rp ', int decimalDigits = 0}) {
    final formatter = NumberFormat.currency(
      locale: locale,
      symbol: symbol,
      decimalDigits: decimalDigits,
    );
    return formatter.format(amount);
  }
  
  /// Format a number with thousands separator
  static String formatNumber(num number, {int decimalDigits = 0}) {
    final formatter = NumberFormat.decimalPattern()
      ..minimumFractionDigits = decimalDigits
      ..maximumFractionDigits = decimalDigits;
    return formatter.format(number);
  }
  
  /// Format a duration in minutes to a human-readable string
  static String formatDuration(int minutes) {
    final hours = minutes ~/ 60;
    final mins = minutes % 60;
    
    if (hours > 0) {
      return '$hours h${mins > 0 ? ' $mins min' : ''}';
    } else {
      return '$mins min';
    }
  }
  
  /// Format a phone number for display
  static String formatPhoneNumber(String phoneNumber) {
    // This is a simple formatter for Indonesian phone numbers
    // Adjust according to the format requirements
    if (phoneNumber.isEmpty) return '';
    
    // Strip any non-digit characters
    final digits = phoneNumber.replaceAll(RegExp(r'[^\d]'), '');
    
    // Format based on length
    if (digits.length <= 4) {
      return digits;
    } else if (digits.length <= 7) {
      return '${digits.substring(0, 4)}-${digits.substring(4)}';
    } else if (digits.length <= 10) {
      return '${digits.substring(0, 4)}-${digits.substring(4, 7)}-${digits.substring(7)}';
    } else {
      return '${digits.substring(0, 4)}-${digits.substring(4, 7)}-${digits.substring(7, 10)}-${digits.substring(10)}';
    }
  }
  
  /// Format a license plate for display
  static String formatLicensePlate(String licensePlate) {
    // This is a simple formatter for Indonesian license plates
    // Adjust according to the format requirements
    return licensePlate.toUpperCase();
  }
}

/// Custom text input formatter for currency
class CurrencyInputFormatter extends TextInputFormatter {
  final String locale;
  final String symbol;
  final int decimalDigits;
  
  CurrencyInputFormatter({
    this.locale = 'id',
    this.symbol = 'Rp ',
    this.decimalDigits = 0,
  });
  
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    if (newValue.text.isEmpty) {
      return newValue;
    }
    
    // Only allow digits
    final onlyDigits = newValue.text.replaceAll(RegExp(r'[^\d]'), '');
    
    // Convert to numeric value
    final numValue = int.tryParse(onlyDigits) ?? 0;
    double value = numValue / (decimalDigits > 0 ? pow(10, decimalDigits) : 1);
    
    // Format using NumberFormat
    final formatter = NumberFormat.currency(
      locale: locale,
      symbol: symbol,
      decimalDigits: decimalDigits,
    );
    final formattedValue = formatter.format(value);
    
    return TextEditingValue(
      text: formattedValue,
      selection: TextSelection.collapsed(offset: formattedValue.length),
    );
  }
  
  // Helper function for exponentiation
  double pow(num x, num exponent) {
    num result = 1;
    for (int i = 0; i < exponent; i++) {
      result *= x;
    }
    return result.toDouble();
  }
}

/// Custom text input formatter for date (YYYY-MM-DD)
class DateInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    // Only allow digits and hyphens
    final newText = newValue.text.replaceAll(RegExp(r'[^\d-]'), '');
    
    if (newText.isEmpty) {
      return newValue.copyWith(text: '');
    }
    
    // Handle deleting characters
    if (oldValue.text.length > newText.length) {
      return newValue;
    }
    
    // Format as YYYY-MM-DD
    var formattedValue = '';
    var cursorPosition = newValue.selection.end;
    
    // Remove existing hyphens for consistent formatting
    final digitsOnly = newText.replaceAll('-', '');
    
    if (digitsOnly.length <= 4) {
      // Year part
      formattedValue = digitsOnly;
    } else if (digitsOnly.length <= 6) {
      // Year and month
      formattedValue = '${digitsOnly.substring(0, 4)}-${digitsOnly.substring(4)}';
      if (newText.length < formattedValue.length) {
        cursorPosition++;
      }
    } else {
      // Year, month, and day
      formattedValue = '${digitsOnly.substring(0, 4)}-${digitsOnly.substring(4, 6)}-${digitsOnly.substring(6, min(8, digitsOnly.length))}';
      if (newText.length < formattedValue.length) {
        cursorPosition += 2;
      }
    }
    
    return TextEditingValue(
      text: formattedValue,
      selection: TextSelection.collapsed(offset: min(formattedValue.length, cursorPosition)),
    );
  }
  
  // Helper function for minimum value
  int min(int a, int b) {
    return a < b ? a : b;
  }
}

/// Custom text input formatter for license plates
class LicensePlateInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    // Convert to uppercase
    final upperCaseText = newValue.text.toUpperCase();
    
    // Only allow letters, numbers, and spaces
    final validChars = upperCaseText.replaceAll(RegExp(r'[^A-Z0-9 ]'), '');
    
    return TextEditingValue(
      text: validChars,
      selection: TextSelection.collapsed(offset: validChars.length),
    );
  }
}

/// Custom text input formatter for phone numbers
class PhoneNumberInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    // Only allow digits, plus sign at the beginning, and spaces
    final validChars = newValue.text.replaceAll(RegExp(r'[^\d+ ]'), '');
    
    // Ensure plus sign is only at the beginning
    if (validChars.contains('+') && validChars.indexOf('+') > 0) {
      final withoutPlus = validChars.replaceAll('+', '');
      return TextEditingValue(
        text: '+$withoutPlus',
        selection: TextSelection.collapsed(offset: min(validChars.length, '+$withoutPlus'.length)),
      );
    }
    
    return TextEditingValue(
      text: validChars,
      selection: TextSelection.collapsed(offset: validChars.length),
    );
  }
  
  // Helper function for minimum value
  int min(int a, int b) {
    return a < b ? a : b;
  }
}