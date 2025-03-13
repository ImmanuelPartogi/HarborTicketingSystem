/// Utility class that provides validation functions for forms
class Validators {
  /// Validates if the value is not null or empty
  static String? required(String? value, {String? message}) {
    if (value == null || value.isEmpty) {
      return message ?? 'This field is required';
    }
    return null;
  }

  /// Validates if the value is a valid email address
  static String? email(String? value, {String? message}) {
    if (value == null || value.isEmpty) {
      return null; // Skip validation if empty (use required validator separately)
    }

    final regex = RegExp(r'^[^@]+@[^@]+\.[^@]+');
    if (!regex.hasMatch(value)) {
      return message ?? 'Enter a valid email address';
    }
    return null;
  }

  /// Validates if the value is a valid phone number
  static String? phone(String? value, {String? message, int minLength = 10}) {
    if (value == null || value.isEmpty) {
      return null; // Skip validation if empty (use required validator separately)
    }

    if (value.length < minLength) {
      return message ?? 'Phone number must be at least $minLength digits';
    }
    
    // Allow only digits, plus sign, and parentheses
    final regex = RegExp(r'^[0-9+() -]+$');
    if (!regex.hasMatch(value)) {
      return message ?? 'Enter a valid phone number';
    }
    
    return null;
  }

  /// Validates if the value is a valid password
  static String? password(String? value, {String? message, int minLength = 8}) {
    if (value == null || value.isEmpty) {
      return null; // Skip validation if empty (use required validator separately)
    }

    if (value.length < minLength) {
      return message ?? 'Password must be at least $minLength characters';
    }
    
    // Check for uppercase letters
    if (!RegExp(r'[A-Z]').hasMatch(value)) {
      return message ?? 'Password must contain at least one uppercase letter';
    }
    
    // Check for digits
    if (!RegExp(r'[0-9]').hasMatch(value)) {
      return message ?? 'Password must contain at least one number';
    }
    
    return null;
  }

  /// Validates if the value matches a confirm value
  static String? confirmPassword(String? value, String? confirmValue, {String? message}) {
    if (value == null || value.isEmpty || confirmValue == null || confirmValue.isEmpty) {
      return null; // Skip validation if either is empty
    }

    if (value != confirmValue) {
      return message ?? 'Passwords do not match';
    }
    return null;
  }

  /// Validates if the value is a valid date in the format 'yyyy-MM-dd'
  static String? date(String? value, {String? message}) {
    if (value == null || value.isEmpty) {
      return null; // Skip validation if empty
    }

    final regex = RegExp(r'^\d{4}-\d{2}-\d{2}$');
    if (!regex.hasMatch(value)) {
      return message ?? 'Date format should be YYYY-MM-DD';
    }
    
    try {
      // Try to parse the date to ensure it's valid
      final dateParts = value.split('-');
      final year = int.parse(dateParts[0]);
      final month = int.parse(dateParts[1]);
      final day = int.parse(dateParts[2]);
      
      if (month < 1 || month > 12) {
        return message ?? 'Month must be between 1 and 12';
      }
      
      // Check if the day is valid for the given month and year
      final daysInMonth = [31, _isLeapYear(year) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
      if (day < 1 || day > daysInMonth[month - 1]) {
        return message ?? 'Invalid day for the month';
      }
    } catch (e) {
      return message ?? 'Invalid date';
    }
    
    return null;
  }

  /// Validates if the value is a valid license plate number
  static String? licensePlate(String? value, {String? message}) {
    if (value == null || value.isEmpty) {
      return null; // Skip validation if empty
    }

    // This is a simple validation for Indonesian license plates
    // Adjust according to the format requirements
    final regex = RegExp(r'^[A-Z0-9 ]{4,10}$');
    if (!regex.hasMatch(value)) {
      return message ?? 'Enter a valid license plate number';
    }
    return null;
  }

  /// Validates if the value is a valid number within the specified range
  static String? numberInRange(String? value, {double? min, double? max, String? message}) {
    if (value == null || value.isEmpty) {
      return null; // Skip validation if empty
    }

    double? numValue;
    try {
      numValue = double.parse(value);
    } catch (e) {
      return message ?? 'Enter a valid number';
    }

    if (min != null && numValue < min) {
      return message ?? 'Value must be at least $min';
    }

    if (max != null && numValue > max) {
      return message ?? 'Value must be at most $max';
    }

    return null;
  }

  // Helper function to check if a year is a leap year
  static bool _isLeapYear(int year) {
    if (year % 400 == 0) return true;
    if (year % 100 == 0) return false;
    return year % 4 == 0;
  }
}