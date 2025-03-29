// lib/config/debug_config.dart

class DebugConfig {
  // Setting this to true will disable some background data fetching
  // to help identify UI loop issues
  static const bool disableAutomaticDataRefresh = false;
  
  // Controls how verbose logging is
  static const bool verboseLogging = false;
  
  // Only enable these logs in debug mode and when verbose logging is on
  static void log(String message) {
    if (verboseLogging) {
      print(message);
    }
  }
  
  // Add this method to any data loading function to easily disable it
  static bool shouldSkipDataLoad(String dataType) {
    if (disableAutomaticDataRefresh) {
      print('DEBUG: Skipping $dataType refresh due to debug configuration');
      return true;
    }
    return false;
  }
}