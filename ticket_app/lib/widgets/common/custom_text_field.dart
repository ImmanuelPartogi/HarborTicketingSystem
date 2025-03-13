import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../config/theme.dart';

class CustomTextField extends StatefulWidget {
  final String label;
  final String? hintText;
  final TextEditingController? controller;
  final TextInputType keyboardType;
  final bool obscureText;
  final bool readOnly;
  final bool isRequired;
  final String? Function(String?)? validator;
  final Function(String)? onChanged;
  final Function(String)? onSubmitted;
  final List<TextInputFormatter>? inputFormatters;
  final int? maxLines;
  final int? minLines;
  final int? maxLength;
  final IconData? prefixIcon;
  final IconData? suffixIcon;
  final VoidCallback? onSuffixIconPressed;
  final FocusNode? focusNode;
  final TextCapitalization textCapitalization;
  final EdgeInsets? contentPadding;
  final bool autofocus;
  final bool enabled;
  final TextStyle? style;
  final String? errorText;
  final String? initialValue;
  final TextInputAction? textInputAction;
  final AutovalidateMode? autovalidateMode;

  const CustomTextField({
    Key? key,
    required this.label,
    this.hintText,
    this.controller,
    this.keyboardType = TextInputType.text,
    this.obscureText = false,
    this.readOnly = false,
    this.isRequired = false,
    this.validator,
    this.onChanged,
    this.onSubmitted,
    this.inputFormatters,
    this.maxLines = 1,
    this.minLines,
    this.maxLength,
    this.prefixIcon,
    this.suffixIcon,
    this.onSuffixIconPressed,
    this.focusNode,
    this.textCapitalization = TextCapitalization.none,
    this.contentPadding,
    this.autofocus = false,
    this.enabled = true,
    this.style,
    this.errorText,
    this.initialValue,
    this.textInputAction,
    this.autovalidateMode,
  }) : super(key: key);

  @override
  State<CustomTextField> createState() => _CustomTextFieldState();
}

class _CustomTextFieldState extends State<CustomTextField> {
  late bool _obscureText;

  @override
  void initState() {
    super.initState();
    _obscureText = widget.obscureText;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    // Build label with required indicator if needed
    Widget labelWidget = Row(
      children: [
        Text(
          widget.label,
          style: TextStyle(
            color: theme.textTheme.bodyLarge?.color,
            fontSize: AppTheme.fontSizeRegular,
            fontWeight: FontWeight.w500,
          ),
        ),
        if (widget.isRequired) ...[
          const SizedBox(width: 4),
          Text(
            '*',
            style: TextStyle(
              color: AppTheme.errorColor,
              fontSize: AppTheme.fontSizeRegular,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ],
    );
    
    // Build suffix icon
    Widget? suffixIconWidget;
    
    if (widget.obscureText) {
      // Password toggle icon
      suffixIconWidget = IconButton(
        icon: Icon(
          _obscureText ? Icons.visibility_off : Icons.visibility,
          color: theme.hintColor,
          size: 20,
        ),
        onPressed: () {
          setState(() {
            _obscureText = !_obscureText;
          });
        },
      );
    } else if (widget.suffixIcon != null) {
      // Custom suffix icon
      suffixIconWidget = IconButton(
        icon: Icon(
          widget.suffixIcon,
          color: theme.hintColor,
          size: 20,
        ),
        onPressed: widget.onSuffixIconPressed,
      );
    }
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        labelWidget,
        const SizedBox(height: 8),
        TextFormField(
          controller: widget.controller,
          initialValue: widget.initialValue,
          keyboardType: widget.keyboardType,
          obscureText: _obscureText,
          readOnly: widget.readOnly,
          textCapitalization: widget.textCapitalization,
          maxLines: widget.maxLines,
          minLines: widget.minLines,
          maxLength: widget.maxLength,
          focusNode: widget.focusNode,
          autofocus: widget.autofocus,
          enabled: widget.enabled,
          inputFormatters: widget.inputFormatters,
          textInputAction: widget.textInputAction,
          autovalidateMode: widget.autovalidateMode,
          style: widget.style ?? TextStyle(
            color: theme.textTheme.bodyLarge?.color,
            fontSize: AppTheme.fontSizeRegular,
          ),
          decoration: InputDecoration(
            hintText: widget.hintText,
            errorText: widget.errorText,
            contentPadding: widget.contentPadding ?? const EdgeInsets.all(AppTheme.paddingMedium),
            prefixIcon: widget.prefixIcon != null
                ? Icon(
                    widget.prefixIcon,
                    color: theme.hintColor,
                    size: 20,
                  )
                : null,
            suffixIcon: suffixIconWidget,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              borderSide: BorderSide(
                color: theme.dividerColor,
                width: 1.0,
              ),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              borderSide: BorderSide(
                color: theme.dividerColor,
                width: 1.0,
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              borderSide: BorderSide(
                color: AppTheme.primaryColor,
                width: 1.5,
              ),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              borderSide: BorderSide(
                color: AppTheme.errorColor,
                width: 1.0,
              ),
            ),
            focusedErrorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
              borderSide: BorderSide(
                color: AppTheme.errorColor,
                width: 1.5,
              ),
            ),
            filled: true,
            fillColor: widget.enabled
                ? (widget.readOnly ? theme.dividerColor.withOpacity(0.1) : theme.cardColor)
                : theme.disabledColor.withOpacity(0.1),
          ),
          validator: widget.validator,
          onChanged: widget.onChanged,
          onFieldSubmitted: widget.onSubmitted,
        ),
      ],
    );
  }
}