import 'dart:convert';
import 'dart:math';
import 'dart:typed_data';
import 'package:crypto/crypto.dart';
import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../models/ticket_model.dart';
import 'api_service.dart';
import '../config/app_config.dart';

class TicketService {
  final ApiService _apiService;

  TicketService(this._apiService);

  Future<List<Ticket>> getActiveTickets() async {
    try {
      final response = await _apiService.getTickets(
        queryParams: {'status': 'active', 'upcoming_only': '1'},
      );
      
      final List<dynamic> ticketsData = response['data'];
      return ticketsData.map((json) => Ticket.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to fetch active tickets: ${e.toString()}');
    }
  }

  Future<List<Ticket>> getTicketHistory() async {
    try {
      final response = await _apiService.getTickets(
        queryParams: {'upcoming_only': '0'},
      );
      
      final List<dynamic> ticketsData = response['data'];
      return ticketsData.map((json) => Ticket.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to fetch ticket history: ${e.toString()}');
    }
  }

  Future<Ticket> getTicketDetail(int id) async {
    try {
      final response = await _apiService.getTicketDetail(id);
      return Ticket.fromJson(response['data']);
    } catch (e) {
      throw Exception('Failed to fetch ticket details: ${e.toString()}');
    }
  }

  // Generate QR code for a ticket
  Widget generateTicketQR(String ticketNumber, int ticketId, DateTime validUntil) {
    final timestamp = DateTime.now().millisecondsSinceEpoch;
    final randomSalt = _generateRandomSalt();
    
    // Create a signature that changes periodically based on current time
    final timeSegment = (timestamp / (AppConfig.ticketWatermarkRefreshInterval * 1000)).floor();
    
    // Create a dynamic signature that changes every 30 seconds
    final signatureData = '$ticketNumber:$ticketId:$timeSegment:$randomSalt';
    final signature = _generateSignature(signatureData);
    
    // Final data to encode in the QR code
    final qrData = {
      'ticket_number': ticketNumber,
      'ticket_id': ticketId,
      'valid_until': validUntil.toIso8601String(),
      'timestamp': timestamp,
      'signature': signature,
    };
    
    return QrImageView(
      data: jsonEncode(qrData),
      version: QrVersions.auto,
      size: 200.0,
      backgroundColor: Colors.white,
      errorStateBuilder: (context, error) {
        return Container(
          width: 200.0,
          height: 200.0,
          color: Colors.white,
          child: const Center(
            child: Text(
              'Error generating QR code',
              textAlign: TextAlign.center,
            ),
          ),
        );
      },
    );
  }
  
  // Generate a random salt for security
  String _generateRandomSalt() {
    final random = Random.secure();
    final values = List<int>.generate(16, (i) => random.nextInt(256));
    return base64Url.encode(values);
  }
  
  // Generate a signature for the QR code
  String _generateSignature(String data) {
    final bytes = utf8.encode(data);
    final digest = sha256.convert(bytes);
    return digest.toString();
  }
  
  // Generate dynamic watermark data
  Map<String, dynamic> generateWatermarkData(
    String ticketNumber, 
    DateTime departureDate, 
    String routeName,
  ) {
    final timestamp = DateTime.now().millisecondsSinceEpoch;
    
    // Use a time segment that changes periodically
    final timeSegment = (timestamp / (AppConfig.ticketWatermarkRefreshInterval * 1000)).floor();
    
    // Generate a pseudo-random pattern based on the ticket details and current time segment
    final seed = '$ticketNumber:$timeSegment';
    final seedBytes = utf8.encode(seed);
    final seedHash = md5.convert(seedBytes).bytes;
    
    // Use the hash to derive watermark pattern properties
    final pattern = _deriveWatermarkPattern(seedHash);
    
    return {
      'timestamp': timestamp,
      'time_segment': timeSegment,
      'route_name': routeName,
      'departure_date': departureDate.toIso8601String(),
      'pattern': pattern,
    };
  }
  
  // Derive watermark pattern from a hash
  Map<String, dynamic> _deriveWatermarkPattern(List<int> hash) {
    // Use the hash to generate visual pattern properties
    final rotation = (hash[0] % 360) * pi / 180; // Rotation angle in radians
    final waves = 3 + (hash[1] % 5); // Number of waves (3-7)
    final amplitude = 5.0 + (hash[2] % 10) / 2; // Wave amplitude (5-10)
    final frequency = 0.01 + (hash[3] % 10) / 500; // Wave frequency
    final phase = (hash[4] % 100) / 100; // Phase shift (0-1)
    
    // Color derivation
    final r = 20 + hash[5] % 50; // Light blue range
    final g = 100 + hash[6] % 100;
    final b = 180 + hash[7] % 75;
    final opacity = 0.2 + (hash[8] % 10) / 50; // Opacity (0.2-0.4)
    
    return {
      'rotation': rotation,
      'waves': waves,
      'amplitude': amplitude,
      'frequency': frequency,
      'phase': phase,
      'color': {
        'r': r,
        'g': g,
        'b': b,
        'opacity': opacity,
      },
    };
  }
  
  // Check if a ticket is valid for displaying
  bool isTicketValid(Ticket ticket) {
    // Ticket must be active
    if (!ticket.isActive) {
      return false;
    }
    
    // Schedule must exist
    if (ticket.schedule == null) {
      return false;
    }
    
    // Check if ticket is not expired
    final expiryTime = ticket.schedule!.departureTime.add(
      const Duration(minutes: AppConfig.ticketExpiryMinutesAfterDeparture),
    );
    
    return DateTime.now().isBefore(expiryTime);
  }
  
  // Group tickets by schedule
  Map<int, List<Ticket>> groupTicketsBySchedule(List<Ticket> tickets) {
    final Map<int, List<Ticket>> grouped = {};
    
    for (var ticket in tickets) {
      if (!grouped.containsKey(ticket.scheduleId)) {
        grouped[ticket.scheduleId] = [];
      }
      
      grouped[ticket.scheduleId]!.add(ticket);
    }
    
    return grouped;
  }
}