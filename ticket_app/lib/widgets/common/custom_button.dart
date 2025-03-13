import 'package:flutter/material.dart';

import '../../config/theme.dart';

enum ButtonType { primary, secondary, outline, text }
enum ButtonSize { small, medium, large }

class CustomButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final ButtonType type;
  final ButtonSize size;
  final IconData? icon;
  final bool isLoading;
  final bool isFullWidth;
  final EdgeInsets? padding;
  final BorderRadius? borderRadius;

  const CustomButton({
    Key? key,
    required this.text,
    required this.onPressed,
    this.type = ButtonType.primary,
    this.size = ButtonSize.medium,
    this.icon,
    this.isLoading = false,
    this.isFullWidth = false,
    this.padding,
    this.borderRadius,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    // Define button styles based on type
    Color backgroundColor;
    Color textColor;
    Color borderColor;
    
    switch (type) {
      case ButtonType.primary:
        backgroundColor = AppTheme.primaryColor;
        textColor = Colors.white;
        borderColor = Colors.transparent;
        break;
      case ButtonType.secondary:
        backgroundColor = AppTheme.accentColor;
        textColor = Colors.white;
        borderColor = Colors.transparent;
        break;
      case ButtonType.outline:
        backgroundColor = Colors.transparent;
        textColor = AppTheme.primaryColor;
        borderColor = AppTheme.primaryColor;
        break;
      case ButtonType.text:
        backgroundColor = Colors.transparent;
        textColor = AppTheme.primaryColor;
        borderColor = Colors.transparent;
        break;
    }
    
    // Define button padding based on size
    EdgeInsets buttonPadding;
    double fontSize;
    double iconSize;
    
    switch (size) {
      case ButtonSize.small:
        buttonPadding = padding ?? const EdgeInsets.symmetric(
          horizontal: AppTheme.paddingMedium,
          vertical: AppTheme.paddingSmall,
        );
        fontSize = AppTheme.fontSizeSmall;
        iconSize = 16.0;
        break;
      case ButtonSize.medium:
        buttonPadding = padding ?? const EdgeInsets.symmetric(
          horizontal: AppTheme.paddingLarge,
          vertical: AppTheme.paddingRegular,
        );
        fontSize = AppTheme.fontSizeRegular;
        iconSize = 20.0;
        break;
      case ButtonSize.large:
        buttonPadding = padding ?? const EdgeInsets.symmetric(
          horizontal: AppTheme.paddingXLarge,
          vertical: AppTheme.paddingMedium,
        );
        fontSize = AppTheme.fontSizeMedium;
        iconSize = 24.0;
        break;
    }
    
    // Build button content
    Widget buttonContent;
    
    if (isLoading) {
      buttonContent = SizedBox(
        height: 20,
        width: 20,
        child: CircularProgressIndicator(
          strokeWidth: 2.0,
          valueColor: AlwaysStoppedAnimation<Color>(textColor),
        ),
      );
    } else if (icon != null) {
      buttonContent = Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: iconSize, color: textColor),
          const SizedBox(width: AppTheme.paddingSmall),
          Text(
            text,
            style: TextStyle(
              color: textColor,
              fontSize: fontSize,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      );
    } else {
      buttonContent = Text(
        text,
        style: TextStyle(
          color: textColor,
          fontSize: fontSize,
          fontWeight: FontWeight.w600,
        ),
      );
    }
    
    // Build button
    final button = Container(
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: borderRadius ?? BorderRadius.circular(AppTheme.borderRadiusRegular),
        border: Border.all(
          color: borderColor,
          width: type == ButtonType.outline ? 1.5 : 0,
        ),
        boxShadow: type == ButtonType.text || type == ButtonType.outline
            ? null
            : [
                BoxShadow(
                  color: AppTheme.primaryColor.withOpacity(0.3),
                  offset: const Offset(0, 2),
                  blurRadius: 4,
                ),
              ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: isLoading ? null : onPressed,
          borderRadius: borderRadius ?? BorderRadius.circular(AppTheme.borderRadiusRegular),
          child: Padding(
            padding: buttonPadding,
            child: Center(child: buttonContent),
          ),
        ),
      ),
    );
    
    if (isFullWidth) {
      return button;
    } else {
      return IntrinsicWidth(child: button);
    }
  }
}