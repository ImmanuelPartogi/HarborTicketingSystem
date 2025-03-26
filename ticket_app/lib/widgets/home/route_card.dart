import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../models/route_model.dart';

class RouteCard extends StatelessWidget {
  final RouteModel route;
  final VoidCallback? onTap;

  const RouteCard({Key? key, required this.route, this.onTap})
    : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final currencyFormat = NumberFormat.currency(
      locale: 'id',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    return Card(
      elevation: 2,
      margin: const EdgeInsets.symmetric(
        horizontal: AppTheme.paddingMedium,
        vertical: AppTheme.paddingSmall,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
        child: Padding(
          padding: const EdgeInsets.all(AppTheme.paddingMedium),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Route name and active indicator
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      route.routeName,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: AppTheme.fontSizeMedium,
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
                      color: route.isActive ? Colors.green : Colors.red,
                      borderRadius: BorderRadius.circular(
                        AppTheme.borderRadiusRound,
                      ),
                    ),
                    child: Text(
                      route.isActive ? 'Active' : 'Inactive',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: AppTheme.fontSizeSmall,
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: AppTheme.paddingRegular),

              // Route details
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildInfoRow(
                          icon: Icons.timelapse,
                          label: 'Duration',
                          value: route.formattedDuration,
                          theme: theme,
                        ),
                        const SizedBox(height: AppTheme.paddingSmall),
                        _buildInfoRow(
                          icon: Icons.straighten,
                          label: 'Distance',
                          value: '${route.distance.toStringAsFixed(1)} km',
                          theme: theme,
                        ),
                      ],
                    ),
                  ),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildInfoRow(
                          icon: Icons.person,
                          label: 'Base Price',
                          value: currencyFormat.format(route.basePrice),
                          theme: theme,
                        ),
                        const SizedBox(height: AppTheme.paddingSmall),
                        _buildInfoRow(
                          icon: Icons.directions_car,
                          label: 'Car Price',
                          value: currencyFormat.format(route.carPrice),
                          theme: theme,
                        ),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: AppTheme.paddingRegular),

              // Action button
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton.icon(
                    onPressed: onTap,
                    icon: const Icon(Icons.search, size: 18),
                    label: const Text('Find Schedules'),
                    style: TextButton.styleFrom(
                      foregroundColor: AppTheme.primaryColor,
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

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required String value,
    required ThemeData theme,
  }) {
    return Row(
      children: [
        Icon(icon, size: 16, color: theme.hintColor),
        const SizedBox(width: AppTheme.paddingSmall),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: AppTheme.fontSizeSmall,
                  color: theme.hintColor,
                ),
              ),
              Text(
                value,
                style: const TextStyle(
                  fontSize: AppTheme.fontSizeRegular,
                  fontWeight: FontWeight.w500,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ],
    );
  }
}

// Versi yang diperbaiki untuk PopularRouteCard
class PopularRouteCard extends StatelessWidget {
  final String departureName;
  final String arrivalName;
  final String? imageUrl;
  final VoidCallback? onTap;

  const PopularRouteCard({
    Key? key,
    required this.departureName,
    required this.arrivalName,
    this.imageUrl,
    this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 200,
      height: 174, // Menggunakan height yang tepat sama dengan constraint parent
      margin: const EdgeInsets.only(
        left: AppTheme.paddingMedium,
        right: AppTheme.paddingSmall,
        bottom: AppTheme.paddingMedium,
      ),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
        child: Column(
          children: [
            // Route image dengan ukuran yang lebih kecil
            ClipRRect(
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(AppTheme.borderRadiusRegular),
                topRight: Radius.circular(AppTheme.borderRadiusRegular),
              ),
              child: SizedBox(
                height: 100, // Mengurangi height gambar
                width: double.infinity,
                child: imageUrl != null
                    ? Image.network(
                        imageUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return Container(
                            color: AppTheme.primaryColor.withOpacity(0.2),
                            child: const Icon(
                              Icons.image_not_supported,
                              color: AppTheme.primaryColor,
                              size: 40,
                            ),
                          );
                        },
                      )
                    : Container(
                        color: AppTheme.primaryColor.withOpacity(0.2),
                        child: const Icon(
                          Icons.directions_boat,
                          color: AppTheme.primaryColor,
                          size: 40,
                        ),
                      ),
              ),
            ),
            
            // Route info - dengan padding minimal
            Expanded(
              child: Container(
                width: double.infinity,
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(AppTheme.borderRadiusRegular),
                    bottomRight: Radius.circular(AppTheme.borderRadiusRegular),
                  ),
                ),
                // Mengurangi padding
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      '$departureName → $arrivalName',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 12, // Ukuran font lebih kecil
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2), // Jarak minimum
                    Row(
                      children: const [
                        Icon(
                          Icons.navigation,
                          size: 12, // Icon lebih kecil
                          color: AppTheme.primaryColor,
                        ),
                        SizedBox(width: 2), // Jarak minimum
                        Expanded(
                          child: Text(
                            'Popular Route',
                            style: TextStyle(
                              fontSize: 10, // Font lebih kecil
                              color: AppTheme.primaryColor,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}