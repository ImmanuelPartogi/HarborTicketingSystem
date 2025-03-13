import 'dart:math';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../config/app_config.dart';
import '../../config/theme.dart';
import '../../models/ticket_model.dart';
import 'watermark_painter.dart';

class AnimatedTicket extends StatefulWidget {
  final Ticket ticket;
  final VoidCallback? onTap;
  
  const AnimatedTicket({
    Key? key,
    required this.ticket,
    this.onTap,
  }) : super(key: key);

  @override
  State<AnimatedTicket> createState() => _AnimatedTicketState();
}

class _AnimatedTicketState extends State<AnimatedTicket> with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _animation;
  late Map<String, dynamic> _watermarkPattern;
  late Timer _refreshTimer;
  
  @override
  void initState() {
    super.initState();
    
    // Initialize animation controller
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 10),
    )..repeat();
    
    // Create animation
    _animation = Tween<double>(begin: 0, end: 2 * pi).animate(_animationController);
    
    // Initialize watermark pattern
    _generateWatermarkPattern();
    
    // Set up timer to refresh the watermark periodically
    _refreshTimer = Timer.periodic(
      Duration(seconds: AppConfig.ticketWatermarkRefreshInterval),
      (_) => _refreshWatermark(),
    );
  }
  
  @override
  void dispose() {
    _animationController.dispose();
    _refreshTimer.cancel();
    super.dispose();
  }
  
  void _refreshWatermark() {
    setState(() {
      _generateWatermarkPattern();
    });
  }
  
  void _generateWatermarkPattern() {
    // Generate a seed based on ticket number and current time segment
    final timestamp = DateTime.now().millisecondsSinceEpoch;
    final timeSegment = (timestamp / (AppConfig.ticketWatermarkRefreshInterval * 1000)).floor();
    
    final seed = '${widget.ticket.ticketNumber}:$timeSegment';
    final random = Random(seed.hashCode);
    
    // Generate watermark pattern properties
    _watermarkPattern = {
      'rotation': random.nextDouble() * 2 * pi,
      'waves': 3 + random.nextInt(5),
      'amplitude': 5.0 + random.nextDouble() * 5.0,
      'frequency': 0.01 + random.nextDouble() * 0.02,
      'phase': random.nextDouble(),
      'color': {
        'r': 20 + random.nextInt(50),
        'g': 100 + random.nextInt(100),
        'b': 180 + random.nextInt(75),
        'opacity': 0.2 + random.nextDouble() * 0.2,
      },
    };
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final hasSchedule = widget.ticket.schedule != null;
    final hasPassenger = widget.ticket.passenger != null;
    final hasRoute = hasSchedule && widget.ticket.schedule!.route != null;
    final hasFerry = hasSchedule && widget.ticket.schedule!.ferry != null;
    
    // Format date and time
    final dateFormat = DateFormat('EEE, dd MMM yyyy');
    final timeFormat = DateFormat('HH:mm');
    
    // Determine the status color overlay
    Color statusColor = widget.ticket.statusColor.withOpacity(0.2);
    
    if (widget.ticket.status == 'expired' || widget.ticket.status == 'cancelled') {
      statusColor = widget.ticket.statusColor.withOpacity(0.5);
    }
    
    return InkWell(
      onTap: widget.onTap,
      child: Container(
        margin: const EdgeInsets.all(AppTheme.paddingMedium),
        decoration: BoxDecoration(
          color: theme.cardColor,
          borderRadius: BorderRadius.circular(AppTheme.borderRadiusLarge),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.2),
              blurRadius: 10,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(AppTheme.borderRadiusLarge),
          child: Stack(
            children: [
              // Animated watermark background
              Positioned.fill(
                child: AnimatedBuilder(
                  animation: _animation,
                  builder: (context, child) {
                    return CustomPaint(
                      painter: WatermarkPainter(
                        animation: _animation.value,
                        pattern: _watermarkPattern,
                      ),
                    );
                  },
                ),
              ),
              
              // Status color overlay for expired/cancelled tickets
              if (widget.ticket.status == 'expired' || widget.ticket.status == 'cancelled')
                Positioned.fill(
                  child: Container(
                    color: statusColor,
                    child: widget.ticket.status == 'expired' 
                        ? Center(
                            child: Transform.rotate(
                              angle: -pi / 12,
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: AppTheme.paddingXLarge,
                                  vertical: AppTheme.paddingSmall,
                                ),
                                decoration: BoxDecoration(
                                  border: Border.all(
                                    color: widget.ticket.statusColor,
                                    width: 4,
                                  ),
                                ),
                                child: Text(
                                  'EXPIRED',
                                  style: TextStyle(
                                    color: widget.ticket.statusColor,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 36,
                                  ),
                                ),
                              ),
                            ),
                          )
                        : Center(
                            child: Transform.rotate(
                              angle: -pi / 12,
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: AppTheme.paddingXLarge,
                                  vertical: AppTheme.paddingSmall,
                                ),
                                decoration: BoxDecoration(
                                  border: Border.all(
                                    color: widget.ticket.statusColor,
                                    width: 4,
                                  ),
                                ),
                                child: Text(
                                  'CANCELLED',
                                  style: TextStyle(
                                    color: widget.ticket.statusColor,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 36,
                                  ),
                                ),
                              ),
                            ),
                          ),
                  ),
                ),
              
              // Ticket content
              Column(
                children: [
                  // Top part with route and status
                  Container(
                    padding: const EdgeInsets.all(AppTheme.paddingMedium),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                hasRoute 
                                    ? widget.ticket.schedule!.route!.routeName
                                    : 'Unknown Route',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: AppTheme.fontSizeLarge,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: AppTheme.paddingRegular,
                                vertical: AppTheme.paddingXSmall,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(AppTheme.borderRadiusRound),
                              ),
                              child: Text(
                                widget.ticket.statusText,
                                style: TextStyle(
                                  color: widget.ticket.statusColor,
                                  fontWeight: FontWeight.bold,
                                  fontSize: AppTheme.fontSizeSmall,
                                ),
                              ),
                            ),
                          ],
                        ),
                        if (hasSchedule)
                          Text(
                            dateFormat.format(widget.ticket.schedule!.departureTime),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: AppTheme.fontSizeMedium,
                            ),
                          ),
                      ],
                    ),
                  ),
                  
                  // Ticket body
                  Padding(
                    padding: const EdgeInsets.all(AppTheme.paddingMedium),
                    child: Column(
                      children: [
                        // Passenger info
                        if (hasPassenger) ...[
                          Row(
                            children: [
                              const Icon(Icons.person, size: 20),
                              const SizedBox(width: AppTheme.paddingRegular),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Passenger',
                                      style: TextStyle(
                                        color: theme.hintColor,
                                        fontSize: AppTheme.fontSizeSmall,
                                      ),
                                    ),
                                    Text(
                                      widget.ticket.passenger!.name,
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: AppTheme.fontSizeMedium,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: AppTheme.paddingRegular),
                          
                          Row(
                            children: [
                              const Icon(Icons.credit_card, size: 20),
                              const SizedBox(width: AppTheme.paddingRegular),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'ID',
                                      style: TextStyle(
                                        color: theme.hintColor,
                                        fontSize: AppTheme.fontSizeSmall,
                                      ),
                                    ),
                                    Text(
                                      '${widget.ticket.passenger!.identityTypeText}: ${widget.ticket.passenger!.identityNumber}',
                                      style: const TextStyle(
                                        fontSize: AppTheme.fontSizeRegular,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: AppTheme.paddingMedium),
                          const Divider(),
                          const SizedBox(height: AppTheme.paddingMedium),
                        ],
                        
                        // Departure info
                        if (hasSchedule) ...[
                          Row(
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Departure Time',
                                      style: TextStyle(
                                        color: theme.hintColor,
                                        fontSize: AppTheme.fontSizeSmall,
                                      ),
                                    ),
                                    Text(
                                      timeFormat.format(widget.ticket.schedule!.departureTime),
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: AppTheme.fontSizeLarge,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              
                              if (hasFerry)
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Ferry',
                                        style: TextStyle(
                                          color: theme.hintColor,
                                          fontSize: AppTheme.fontSizeSmall,
                                        ),
                                      ),
                                      Text(
                                        widget.ticket.schedule!.ferry!.name,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w500,
                                          fontSize: AppTheme.fontSizeMedium,
                                        ),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ],
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: AppTheme.paddingRegular),
                        ],
                        
                        // Ticket number and QR info
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Column(
                              children: [
                                Text(
                                  'Ticket Number',
                                  style: TextStyle(
                                    color: theme.hintColor,
                                    fontSize: AppTheme.fontSizeSmall,
                                  ),
                                ),
                                Text(
                                  widget.ticket.ticketNumber,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: AppTheme.fontSizeMedium,
                                  ),
                                ),
                                const SizedBox(height: AppTheme.paddingRegular),
                                if (widget.ticket.isActive)
                                  Text(
                                    'Tap to show QR code',
                                    style: TextStyle(
                                      color: AppTheme.primaryColor,
                                      fontSize: AppTheme.fontSizeRegular,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}