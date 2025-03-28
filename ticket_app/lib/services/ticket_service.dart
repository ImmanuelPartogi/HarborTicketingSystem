import 'dart:convert';
import 'dart:math';
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
      
      print('API Response for getActiveTickets:');
      print('Response type: ${response.runtimeType}');
      if (response['data'] != null) {
        print('Data type: ${response['data'].runtimeType}');
      } else {
        print('Data is null in response');
        return [];
      }
      
      // Handle different response formats
      final dynamic data = response['data'];
      List<dynamic> ticketsData;
      
      if (data is Map<String, dynamic>) {
        print('Data is Map in getActiveTickets with keys: ${data.keys.toList()}');
        // Jika data adalah Map, coba temukan array yang mungkin berisi data tickets
        if (data.containsKey('tickets')) {
          ticketsData = data['tickets'] as List<dynamic>;
        } else if (data.containsKey('items')) {
          ticketsData = data['items'] as List<dynamic>;
        } else {
          // Jika tidak ditemukan, buat list dengan satu item
          print('Converting Map to single-item List in getActiveTickets');
          ticketsData = [data];
        }
      } else if (data is List<dynamic>) {
        // Format yang diharapkan
        print('Data is List in getActiveTickets with length: ${data.length}');
        ticketsData = data;
      } else {
        // Jika format tak terduga, kembalikan list kosong
        print('Error: Unexpected data format in getActiveTickets: ${data?.runtimeType}');
        return [];
      }
      
      try {
        final tickets = <Ticket>[];
        for (var i = 0; i < ticketsData.length; i++) {
          try {
            final ticket = Ticket.fromJson(ticketsData[i]);
            tickets.add(ticket);
          } catch (e) {
            print('Error parsing ticket at index $i: $e');
            // Continue with next ticket instead of failing completely
          }
        }
        
        print('Successfully parsed ${tickets.length} active tickets out of ${ticketsData.length}');
        return tickets;
      } catch (e) {
        print('Error converting JSON to Ticket objects: $e');
        return []; // Return empty list rather than throwing exception
      }
    } catch (e) {
      print('Error in getActiveTickets: ${e.toString()}');
      return []; // Return empty list rather than throwing exception
    }
  }

  Future<List<Ticket>> getTicketHistory() async {
    try {
      final response = await _apiService.getTickets(
        queryParams: {'upcoming_only': '0'},
      );
      
      print('API Response for getTicketHistory:');
      print('Response type: ${response.runtimeType}');
      if (response['data'] != null) {
        print('Data type: ${response['data'].runtimeType}');
      } else {
        print('Data is null in response');
        return [];
      }
      
      // Handle different response formats
      final dynamic data = response['data'];
      List<dynamic> ticketsData;
      
      if (data is Map<String, dynamic>) {
        print('Data is Map in getTicketHistory with keys: ${data.keys.toList()}');
        // Jika data adalah Map, coba temukan array yang mungkin berisi data tickets
        if (data.containsKey('tickets')) {
          ticketsData = data['tickets'] as List<dynamic>;
        } else if (data.containsKey('items')) {
          ticketsData = data['items'] as List<dynamic>;
        } else {
          // Jika tidak ditemukan, buat list dengan satu item
          print('Converting Map to single-item List in getTicketHistory');
          ticketsData = [data];
        }
      } else if (data is List<dynamic>) {
        // Format yang diharapkan
        print('Data is List in getTicketHistory with length: ${data.length}');
        ticketsData = data;
      } else {
        // Jika format tak terduga, kembalikan list kosong
        print('Error: Unexpected data format in getTicketHistory: ${data?.runtimeType}');
        return [];
      }
      
      try {
        final tickets = <Ticket>[];
        for (var i = 0; i < ticketsData.length; i++) {
          try {
            final ticket = Ticket.fromJson(ticketsData[i]);
            tickets.add(ticket);
          } catch (e) {
            print('Error parsing ticket at index $i: $e');
            // Continue with next ticket instead of failing completely
          }
        }
        
        print('Successfully parsed ${tickets.length} history tickets out of ${ticketsData.length}');
        return tickets;
      } catch (e) {
        print('Error converting JSON to Ticket objects: $e');
        return []; // Return empty list rather than throwing exception
      }
    } catch (e) {
      print('Error in getTicketHistory: ${e.toString()}');
      return []; // Return empty list rather than throwing exception
    }
  }

  Future<Ticket?> getTicketDetail(int id) async {
    try {
      final response = await _apiService.getTicketDetail(id);
      print('API Response for getTicketDetail:');
      print('Response type: ${response.runtimeType}');
      
      if (response['data'] == null) {
        print('Ticket data is null for id: $id');
        return null;
      }
      
      try {
        return Ticket.fromJson(response['data']);
      } catch (e) {
        print('Error converting JSON to Ticket object: $e');
        print('Ticket data: ${response['data']}');
        return null;
      }
    } catch (e) {
      print('Error in getTicketDetail: ${e.toString()}');
      return null; // Return null rather than throwing exception
    }
  }

  Future<List<Ticket>> getTicketsByBookingId(int bookingId) async {
  try {
    final response = await _apiService.getTickets(
      queryParams: {'booking_id': bookingId.toString()},
    );
    
    // Proses hasil untuk mengekstrak tiket
    final dynamic data = response['data'];
    List<dynamic> ticketsData;
    
    if (data is Map<String, dynamic>) {
      if (data.containsKey('tickets')) {
        ticketsData = data['tickets'] as List<dynamic>;
      } else if (data.containsKey('items')) {
        ticketsData = data['items'] as List<dynamic>;
      } else {
        ticketsData = [data];
      }
    } else if (data is List<dynamic>) {
      ticketsData = data;
    } else {
      return [];
    }
    
    // Konversi ke objek Ticket
    final tickets = <Ticket>[];
    for (var ticketData in ticketsData) {
      try {
        final ticket = Ticket.fromJson(ticketData);
        tickets.add(ticket);
      } catch (e) {
        print('Error parsing ticket: $e');
      }
    }
    
    return tickets;
  } catch (e) {
    print('Error getting tickets by booking ID: $e');
    return [];
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
      if (ticket.scheduleId == null) continue;
      
      if (!grouped.containsKey(ticket.scheduleId)) {
        grouped[ticket.scheduleId!] = [];
      }
      
      grouped[ticket.scheduleId!]!.add(ticket);
    }
    
    return grouped;
  }
}