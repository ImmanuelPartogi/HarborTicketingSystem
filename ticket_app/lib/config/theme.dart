import 'package:flutter/material.dart';

class AppTheme {
  // Color constants
  static const Color primaryColor = Color(0xFF1976D2);
  static const Color accentColor = Color(0xFF03A9F4);
  static const Color secondaryColor = Color(0xFF0D47A1);
  static const Color errorColor = Color(0xFFD32F2F);
  static const Color successColor = Color(0xFF388E3C);
  static const Color warningColor = Color(0xFFFFA000);
  static const Color infoColor = Color(0xFF0288D1);
  
  // Text colors
  static const Color textPrimaryColor = Color(0xFF212121);
  static const Color textSecondaryColor = Color(0xFF757575);
  static const Color textLightColor = Color(0xFFFFFFFF);
  
  // Background colors
  static const Color scaffoldLightColor = Color(0xFFF5F5F5);
  static const Color scaffoldDarkColor = Color(0xFF121212);
  static const Color cardLightColor = Color(0xFFFFFFFF);
  static const Color cardDarkColor = Color(0xFF1E1E1E);
  
  // Gradient colors
  static const List<Color> primaryGradient = [
    Color(0xFF1976D2),
    Color(0xFF0D47A1),
  ];
  
  // Ticket status colors
  static const Color activeTicketColor = Color(0xFF1976D2);
  static const Color usedTicketColor = Color(0xFF388E3C);
  static const Color expiredTicketColor = Color(0xFF757575);
  static const Color cancelledTicketColor = Color(0xFFD32F2F);
  static const Color pendingPaymentTicketColor = Color(0xFFFFA000);
  
  // Font sizes
  static const double fontSizeXSmall = 10.0;
  static const double fontSizeSmall = 12.0;
  static const double fontSizeRegular = 14.0;
  static const double fontSizeMedium = 16.0;
  static const double fontSizeLarge = 18.0;
  static const double fontSizeXLarge = 20.0;
  static const double fontSizeXXLarge = 24.0;
  
  // Border radius
  static const double borderRadiusSmall = 4.0;
  static const double borderRadiusRegular = 8.0;
  static const double borderRadiusMedium = 12.0;
  static const double borderRadiusLarge = 16.0;
  static const double borderRadiusXLarge = 24.0;
  static const double borderRadiusRound = 50.0;
  
  // Elevation
  static const double elevationSmall = 2.0;
  static const double elevationRegular = 4.0;
  static const double elevationMedium = 8.0;
  static const double elevationLarge = 16.0;
  
  // Padding & margin
  static const double paddingXSmall = 4.0;
  static const double paddingSmall = 8.0;
  static const double paddingRegular = 12.0;
  static const double paddingMedium = 16.0;
  static const double paddingLarge = 24.0;
  static const double paddingXLarge = 32.0;
  static const double paddingXXLarge = 48.0;
  
  // Light theme
  static final ThemeData lightTheme = ThemeData(
    primaryColor: primaryColor,
    colorScheme: ColorScheme.light(
      primary: primaryColor,
      secondary: accentColor,
      error: errorColor,
      background: scaffoldLightColor,
      surface: cardLightColor,
    ),
    scaffoldBackgroundColor: scaffoldLightColor,
    cardColor: cardLightColor,
    textTheme: const TextTheme(
      displayLarge: TextStyle(
        color: textPrimaryColor,
        fontSize: 26.0,
        fontWeight: FontWeight.bold,
      ),
      displayMedium: TextStyle(
        color: textPrimaryColor,
        fontSize: 22.0,
        fontWeight: FontWeight.bold,
      ),
      displaySmall: TextStyle(
        color: textPrimaryColor,
        fontSize: 18.0,
        fontWeight: FontWeight.w600,
      ),
      headlineMedium: TextStyle(
        color: textPrimaryColor,
        fontSize: 16.0,
        fontWeight: FontWeight.w600,
      ),
      titleLarge: TextStyle(
        color: textPrimaryColor,
        fontSize: 14.0,
        fontWeight: FontWeight.w600,
      ),
      bodyLarge: TextStyle(
        color: textPrimaryColor,
        fontSize: 14.0,
        fontWeight: FontWeight.normal,
      ),
      bodyMedium: TextStyle(
        color: textSecondaryColor,
        fontSize: 14.0,
        fontWeight: FontWeight.normal,
      ),
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: primaryColor,
      elevation: elevationRegular,
      centerTitle: true,
      titleTextStyle: TextStyle(
        color: textLightColor,
        fontSize: fontSizeLarge,
        fontWeight: FontWeight.w600,
      ),
      iconTheme: IconThemeData(
        color: textLightColor,
      ),
    ),
    buttonTheme: ButtonThemeData(
      buttonColor: primaryColor,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
      ),
      textTheme: ButtonTextTheme.primary,
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: primaryColor,
        foregroundColor: textLightColor,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(borderRadiusRegular),
        ),
        padding: const EdgeInsets.symmetric(
          horizontal: paddingMedium,
          vertical: paddingRegular,
        ),
        textStyle: const TextStyle(
          fontSize: fontSizeMedium,
          fontWeight: FontWeight.w600,
        ),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: primaryColor,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(borderRadiusRegular),
        ),
        side: const BorderSide(
          color: primaryColor,
          width: 1.5,
        ),
        padding: const EdgeInsets.symmetric(
          horizontal: paddingMedium,
          vertical: paddingRegular,
        ),
        textStyle: const TextStyle(
          fontSize: fontSizeMedium,
          fontWeight: FontWeight.w600,
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: cardLightColor,
      contentPadding: const EdgeInsets.all(paddingMedium),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: textSecondaryColor,
          width: 1.0,
        ),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: textSecondaryColor,
          width: 1.0,
        ),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: primaryColor,
          width: 1.5,
        ),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: errorColor,
          width: 1.0,
        ),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: errorColor,
          width: 1.5,
        ),
      ),
      labelStyle: const TextStyle(
        color: textSecondaryColor,
        fontSize: fontSizeRegular,
      ),
      hintStyle: const TextStyle(
        color: textSecondaryColor,
        fontSize: fontSizeRegular,
      ),
      errorStyle: const TextStyle(
        color: errorColor,
        fontSize: fontSizeSmall,
      ),
    ),
    cardTheme: CardTheme(
      color: cardLightColor,
      elevation: elevationSmall,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
      ),
      margin: const EdgeInsets.all(paddingSmall),
    ),
    dividerTheme: const DividerThemeData(
      color: textSecondaryColor,
      thickness: 0.5,
      space: paddingMedium,
    ),
    bottomNavigationBarTheme: const BottomNavigationBarThemeData(
      backgroundColor: cardLightColor,
      selectedItemColor: primaryColor,
      unselectedItemColor: textSecondaryColor,
      type: BottomNavigationBarType.fixed,
      showUnselectedLabels: true,
      selectedLabelStyle: TextStyle(
        fontSize: fontSizeXSmall,
        fontWeight: FontWeight.w600,
      ),
      unselectedLabelStyle: TextStyle(
        fontSize: fontSizeXSmall,
        fontWeight: FontWeight.normal,
      ),
    ),
    chipTheme: ChipThemeData(
      backgroundColor: scaffoldLightColor,
      disabledColor: scaffoldLightColor,
      selectedColor: primaryColor,
      secondarySelectedColor: accentColor,
      padding: const EdgeInsets.symmetric(
        horizontal: paddingSmall,
        vertical: paddingXSmall,
      ),
      labelStyle: const TextStyle(
        color: textSecondaryColor,
        fontSize: fontSizeSmall,
      ),
      secondaryLabelStyle: const TextStyle(
        color: textLightColor,
        fontSize: fontSizeSmall,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(borderRadiusRound),
      ),
    ),
    tabBarTheme: const TabBarTheme(
      labelColor: primaryColor,
      unselectedLabelColor: textSecondaryColor,
      labelStyle: TextStyle(
        fontSize: fontSizeRegular,
        fontWeight: FontWeight.w600,
      ),
      unselectedLabelStyle: TextStyle(
        fontSize: fontSizeRegular,
        fontWeight: FontWeight.normal,
      ),
      indicator: UnderlineTabIndicator(
        borderSide: BorderSide(
          color: primaryColor,
          width: 2.0,
        ),
      ),
    ),
  );
  
  // Dark theme
  static final ThemeData darkTheme = ThemeData(
    primaryColor: primaryColor,
    colorScheme: ColorScheme.dark(
      primary: primaryColor,
      secondary: accentColor,
      error: errorColor,
      background: scaffoldDarkColor,
      surface: cardDarkColor,
    ),
    scaffoldBackgroundColor: scaffoldDarkColor,
    cardColor: cardDarkColor,
    textTheme: const TextTheme(
      displayLarge: TextStyle(
        color: textLightColor,
        fontSize: 26.0,
        fontWeight: FontWeight.bold,
      ),
      displayMedium: TextStyle(
        color: textLightColor,
        fontSize: 22.0,
        fontWeight: FontWeight.bold,
      ),
      displaySmall: TextStyle(
        color: textLightColor,
        fontSize: 18.0,
        fontWeight: FontWeight.w600,
      ),
      headlineMedium: TextStyle(
        color: textLightColor,
        fontSize: 16.0,
        fontWeight: FontWeight.w600,
      ),
      titleLarge: TextStyle(
        color: textLightColor,
        fontSize: 14.0,
        fontWeight: FontWeight.w600,
      ),
      bodyLarge: TextStyle(
        color: textLightColor,
        fontSize: 14.0,
        fontWeight: FontWeight.normal,
      ),
      bodyMedium: TextStyle(
        color: textSecondaryColor,
        fontSize: 14.0,
        fontWeight: FontWeight.normal,
      ),
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: primaryColor,
      elevation: elevationRegular,
      centerTitle: true,
      titleTextStyle: TextStyle(
        color: textLightColor,
        fontSize: fontSizeLarge,
        fontWeight: FontWeight.w600,
      ),
      iconTheme: IconThemeData(
        color: textLightColor,
      ),
    ),
    buttonTheme: ButtonThemeData(
      buttonColor: primaryColor,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
      ),
      textTheme: ButtonTextTheme.primary,
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: primaryColor,
        foregroundColor: textLightColor,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(borderRadiusRegular),
        ),
        padding: const EdgeInsets.symmetric(
          horizontal: paddingMedium,
          vertical: paddingRegular,
        ),
        textStyle: const TextStyle(
          fontSize: fontSizeMedium,
          fontWeight: FontWeight.w600,
        ),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: textLightColor,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(borderRadiusRegular),
        ),
        side: const BorderSide(
          color: primaryColor,
          width: 1.5,
        ),
        padding: const EdgeInsets.symmetric(
          horizontal: paddingMedium,
          vertical: paddingRegular,
        ),
        textStyle: const TextStyle(
          fontSize: fontSizeMedium,
          fontWeight: FontWeight.w600,
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: cardDarkColor,
      contentPadding: const EdgeInsets.all(paddingMedium),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: textSecondaryColor,
          width: 1.0,
        ),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: textSecondaryColor,
          width: 1.0,
        ),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: primaryColor,
          width: 1.5,
        ),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: errorColor,
          width: 1.0,
        ),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
        borderSide: const BorderSide(
          color: errorColor,
          width: 1.5,
        ),
      ),
      labelStyle: const TextStyle(
        color: textSecondaryColor,
        fontSize: fontSizeRegular,
      ),
      hintStyle: const TextStyle(
        color: textSecondaryColor,
        fontSize: fontSizeRegular,
      ),
      errorStyle: const TextStyle(
        color: errorColor,
        fontSize: fontSizeSmall,
      ),
    ),
    cardTheme: CardTheme(
      color: cardDarkColor,
      elevation: elevationSmall,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(borderRadiusRegular),
      ),
      margin: const EdgeInsets.all(paddingSmall),
    ),
    dividerTheme: const DividerThemeData(
      color: textSecondaryColor,
      thickness: 0.5,
      space: paddingMedium,
    ),
    bottomNavigationBarTheme: const BottomNavigationBarThemeData(
      backgroundColor: cardDarkColor,
      selectedItemColor: primaryColor,
      unselectedItemColor: textSecondaryColor,
      type: BottomNavigationBarType.fixed,
      showUnselectedLabels: true,
      selectedLabelStyle: TextStyle(
        fontSize: fontSizeXSmall,
        fontWeight: FontWeight.w600,
      ),
      unselectedLabelStyle: TextStyle(
        fontSize: fontSizeXSmall,
        fontWeight: FontWeight.normal,
      ),
    ),
    chipTheme: ChipThemeData(
      backgroundColor: scaffoldDarkColor,
      disabledColor: scaffoldDarkColor,
      selectedColor: primaryColor,
      secondarySelectedColor: accentColor,
      padding: const EdgeInsets.symmetric(
        horizontal: paddingSmall,
        vertical: paddingXSmall,
      ),
      labelStyle: const TextStyle(
        color: textSecondaryColor,
        fontSize: fontSizeSmall,
      ),
      secondaryLabelStyle: const TextStyle(
        color: textLightColor,
        fontSize: fontSizeSmall,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(borderRadiusRound),
      ),
    ),
    tabBarTheme: const TabBarTheme(
      labelColor: primaryColor,
      unselectedLabelColor: textSecondaryColor,
      labelStyle: TextStyle(
        fontSize: fontSizeRegular,
        fontWeight: FontWeight.w600,
      ),
      unselectedLabelStyle: TextStyle(
        fontSize: fontSizeRegular,
        fontWeight: FontWeight.normal,
      ),
      indicator: UnderlineTabIndicator(
        borderSide: BorderSide(
          color: primaryColor,
          width: 2.0,
        ),
      ),
    ),
  );
}