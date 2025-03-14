import 'package:flutter/material.dart';
import '../config/theme.dart';

/// Utility class that provides helper methods
class Helpers {
  /// Show a simple message dialog
  static Future<void> showMessageDialog({
    required BuildContext context,
    required String title,
    required String message,
    String buttonText = 'OK',
    VoidCallback? onPressed,
  }) async {
    return showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          title: Text(title),
          content: SingleChildScrollView(
            child: Text(message),
          ),
          actions: <Widget>[
            TextButton(
              onPressed: onPressed ?? () {
                Navigator.of(context).pop();
              },
              child: Text(buttonText),
            ),
          ],
        );
      },
    );
  }
  
  /// Show a confirmation dialog
  static Future<bool> showConfirmationDialog({
    required BuildContext context,
    required String title,
    required String message,
    String confirmText = 'Yes',
    String cancelText = 'No',
    bool isDangerous = false,
  }) async {
    final result = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          title: Text(title),
          content: SingleChildScrollView(
            child: Text(message),
          ),
          actions: <Widget>[
            TextButton(
              onPressed: () {
                Navigator.of(context).pop(false);
              },
              child: Text(cancelText),
            ),
            TextButton(
              onPressed: () {
                Navigator.of(context).pop(true);
              },
              style: TextButton.styleFrom(
                foregroundColor: isDangerous ? Colors.red : AppTheme.primaryColor,
              ),
              child: Text(confirmText),
            ),
          ],
        );
      },
    );
    
    return result ?? false;
  }
  
  /// Show a loading dialog
  static Future<void> showLoadingDialog({
    required BuildContext context,
    String message = 'Loading...',
  }) async {
    return showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          content: Row(
            children: [
              const CircularProgressIndicator(),
              const SizedBox(width: AppTheme.paddingMedium),
              Expanded(
                child: Text(message),
              ),
            ],
          ),
        );
      },
    );
  }
  
  /// Show a snackbar message
  static void showSnackBar({
    required BuildContext context,
    required String message,
    bool isError = false,
    Duration duration = const Duration(seconds: 3),
  }) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red : Colors.green,
        duration: duration,
      ),
    );
  }
  
  /// Show a bottom sheet with options
  static Future<T?> showOptionsBottomSheet<T>({
    required BuildContext context,
    required String title,
    required List<Map<String, dynamic>> options,
    String cancelText = 'Cancel',
  }) async {
    return showModalBottomSheet<T>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(AppTheme.borderRadiusLarge),
          topRight: Radius.circular(AppTheme.borderRadiusLarge),
        ),
      ),
      builder: (context) {
        return Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Text(
                title,
                style: const TextStyle(
                  fontSize: AppTheme.fontSizeLarge,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            Divider(color: Colors.grey.shade300, height: 1),
            Expanded(
              child: ListView.builder(
                shrinkWrap: true,
                itemCount: options.length,
                itemBuilder: (context, index) {
                  final option = options[index];
                  return ListTile(
                    leading: option['icon'] != null
                        ? Icon(option['icon'] as IconData)
                        : null,
                    title: Text(option['title'] as String),
                    subtitle: option['subtitle'] != null
                        ? Text(option['subtitle'] as String)
                        : null,
                    onTap: () {
                      Navigator.pop(context, option['value']);
                    },
                  );
                },
              ),
            ),
            Divider(color: Colors.grey.shade300, height: 1),
            SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(AppTheme.paddingMedium),
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(context);
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.grey.shade200,
                      foregroundColor: Colors.black87,
                    ),
                    child: Text(cancelText),
                  ),
                ),
              ),
            ),
          ],
        );
      },
    );
  }
  
  /// Get status color based on status
  static Color getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'active':
      case 'confirmed':
      case 'completed':
        return Colors.green;
      case 'pending':
        return Colors.amber;
      case 'cancelled':
      case 'expired':
      case 'failed':
        return Colors.red;
      case 'used':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }
  
  /// Get vehicle icon based on type
  static IconData getVehicleIcon(String type) {
    switch (type.toLowerCase()) {
      case 'car':
        return Icons.directions_car;
      case 'motorcycle':
        return Icons.two_wheeler;
      case 'bus':
        return Icons.directions_bus;
      case 'truck':
        return Icons.local_shipping;
      default:
        return Icons.directions_car;
    }
  }
  
  /// Get readable file size
  static String getReadableFileSize(int bytes, {int decimals = 1}) {
    if (bytes <= 0) return '0 B';
    const suffixes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    final i = (log(bytes) / log(1024)).floor();
    // Konversi hasil pembagian ke double agar bisa menggunakan toStringAsFixed
    return '${(bytes / pow(1024, i)).toDouble().toStringAsFixed(decimals)} ${suffixes[i]}';
  }
  
  // Helper functions
  static double log(num x) => _log(x, e);
  static double _log(num x, num base) => log10(x) / log10(base);
  static double log10(num x) => _customLog10(x);
  
  static double _customLog10(num x) {
    if (x <= 0) return 0;
    
    // Simple approximation of log10
    int exponent = 0;
    double value = x.toDouble(); // Konversi x ke double
    
    if (value >= 1) {
      while (value >= 10) {
        value /= 10;
        exponent++;
      }
    } else {
      while (value < 1) {
        value *= 10;
        exponent--;
      }
    }
    
    return exponent.toDouble();
  }
  
  // Alternatif: mengubah pow untuk mengembalikan double
  static double pow(num x, int exponent) {
    double result = 1.0; // Ubah tipe menjadi double
    
    if (exponent >= 0) {
      for (int i = 0; i < exponent; i++) {
        result *= x;
      }
    } else {
      for (int i = 0; i < -exponent; i++) {
        result /= x;
      }
    }
    
    return result; // Sekarang mengembalikan double
  }
  
  static const double e = 2.718281828459045;
}