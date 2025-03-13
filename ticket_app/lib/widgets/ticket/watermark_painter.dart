import 'dart:math';
import 'package:flutter/material.dart';

class WatermarkPainter extends CustomPainter {
  final double animation;
  final Map<String, dynamic> pattern;
  
  WatermarkPainter({
    required this.animation,
    required this.pattern,
  });
  
  @override
  void paint(Canvas canvas, Size size) {
    // Extract pattern parameters
    final double rotation = pattern['rotation'] ?? 0;
    final int waves = pattern['waves'] ?? 5;
    final double amplitude = pattern['amplitude'] ?? 8.0;
    final double frequency = pattern['frequency'] ?? 0.02;
    final double phase = pattern['phase'] ?? 0.0;
    
    // Extract color parameters
    final Map<String, dynamic> colorData = pattern['color'] ?? 
      {'r': 30, 'g': 150, 'b': 220, 'opacity': 0.2};
    
    final color = Color.fromRGBO(
      colorData['r'] ?? 30,
      colorData['g'] ?? 150,
      colorData['b'] ?? 220,
      colorData['opacity'] ?? 0.2,
    );
    
    // Set up paint
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.5;
    
    // Rotate canvas
    final centerX = size.width / 2;
    final centerY = size.height / 2;
    
    canvas.save();
    canvas.translate(centerX, centerY);
    canvas.rotate(rotation + animation * 0.1); // Slow rotation
    canvas.translate(-centerX, -centerY);
    
    // Draw wave pattern lines
    final spacing = size.height / (waves + 1);
    
    for (int i = 0; i <= waves; i++) {
      final y = spacing * (i + 0.5);
      final path = Path();
      
      path.moveTo(0, y);
      
      for (double x = 0; x <= size.width; x++) {
        final sinInput = (x * frequency) + phase + animation;
        final dy = amplitude * sin(sinInput);
        path.lineTo(x, y + dy);
      }
      
      canvas.drawPath(path, paint);
    }
    
    // Draw crossing lines
    final crossSpacing = size.width / (waves + 1);
    
    for (int i = 0; i <= waves; i++) {
      final x = crossSpacing * (i + 0.5);
      final path = Path();
      
      path.moveTo(x, 0);
      
      for (double y = 0; y <= size.height; y++) {
        final sinInput = (y * frequency) + phase + animation;
        final dx = amplitude * sin(sinInput);
        path.lineTo(x + dx, y);
      }
      
      canvas.drawPath(path, paint);
    }
    
    // Draw text watermark
    final textPainter = TextPainter(
      text: TextSpan(
        text: 'FERRY TICKET',
        style: TextStyle(
          color: color,
          fontSize: 32,
          fontWeight: FontWeight.bold,
        ),
      ),
      textDirection: TextDirection.ltr,
    );
    
    textPainter.layout();
    
    // Calculate positions for diagonal repeating pattern
    final textWidth = textPainter.width;
    final textHeight = textPainter.height;
    final diagonalSpacing = (textWidth + textHeight) / 2;
    
    for (double x = -diagonalSpacing; x < size.width + diagonalSpacing; x += diagonalSpacing * 1.5) {
      for (double y = -diagonalSpacing; y < size.height + diagonalSpacing; y += diagonalSpacing * 1.5) {
        // Apply subtle animation to text positioning
        final animX = x + sin(animation + y * 0.01) * 5;
        final animY = y + cos(animation + x * 0.01) * 5;
        
        canvas.save();
        canvas.translate(animX, animY);
        canvas.rotate(pi / 4); // 45 degree angle
        textPainter.paint(canvas, Offset(-textWidth / 2, -textHeight / 2));
        canvas.restore();
      }
    }
    
    canvas.restore();
  }
  
  @override
  bool shouldRepaint(WatermarkPainter oldDelegate) {
    return oldDelegate.animation != animation;
  }
}

class WatermarkClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    final path = Path();
    
    // Top edge with wave
    path.moveTo(0, 0);
    path.lineTo(size.width, 0);
    
    // Right edge with wave
    path.lineTo(size.width, size.height);
    
    // Bottom edge with wave
    path.lineTo(0, size.height);
    
    // Left edge
    path.close();
    
    return path;
  }
  
  @override
  bool shouldReclip(CustomClipper<Path> oldClipper) => false;
}