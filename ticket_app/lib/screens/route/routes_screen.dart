import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/ferry_provider.dart';
import '../../widgets/common/loading_indicator.dart';
import '../../widgets/home/route_card.dart';

class RoutesScreen extends StatefulWidget {
  const RoutesScreen({Key? key}) : super(key: key);

  @override
  State<RoutesScreen> createState() => _RoutesScreenState();
}

class _RoutesScreenState extends State<RoutesScreen> {
  bool _isLoading = false;
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadRoutes();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadRoutes() async {
    if (_isLoading) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final ferryProvider = Provider.of<FerryProvider>(context, listen: false);
      await ferryProvider.fetchRoutes();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading routes: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('All Routes'),
        elevation: 0,
      ),
      body: Column(
        children: [
          // Search bar with gradient background
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  theme.primaryColor,
                  theme.primaryColor.withOpacity(0.8),
                ],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
            padding: const EdgeInsets.all(AppTheme.paddingMedium),
            child: TextField(
              controller: _searchController,
              style: const TextStyle(color: Colors.white),
              onChanged: (value) {
                setState(() {
                  _searchQuery = value.toLowerCase();
                });
              },
              decoration: InputDecoration(
                hintText: 'Search routes...',
                hintStyle: const TextStyle(color: Colors.white70),
                prefixIcon: const Icon(Icons.search, color: Colors.white),
                filled: true,
                fillColor: Colors.white.withOpacity(0.2),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(AppTheme.borderRadiusRound),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(
                  vertical: AppTheme.paddingRegular,
                ),
              ),
            ),
          ),
          
          // Routes list
          Expanded(
            child: Consumer<FerryProvider>(
              builder: (context, ferryProvider, _) {
                if (_isLoading || ferryProvider.isLoadingRoutes) {
                  return const Center(child: LoadingIndicator());
                }

                // Filter routes based on search
                final filteredRoutes = ferryProvider.routes.where((route) {
                  if (_searchQuery.isEmpty) return true;
                  
                  // Search in route name, departure and arrival ports
                  return route.routeName.toLowerCase().contains(_searchQuery) ||
                      route.departurePort.toLowerCase().contains(_searchQuery) ||
                      route.arrivalPort.toLowerCase().contains(_searchQuery);
                }).toList();

                if (filteredRoutes.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.route,
                          size: 64,
                          color: theme.hintColor,
                        ),
                        const SizedBox(height: AppTheme.paddingMedium),
                        Text(
                          _searchQuery.isEmpty
                              ? 'No routes available'
                              : 'No routes match your search',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeMedium,
                            color: theme.textTheme.bodyLarge?.color,
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: _loadRoutes,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppTheme.paddingMedium),
                    itemCount: filteredRoutes.length,
                    itemBuilder: (context, index) {
                      final route = filteredRoutes[index];
                      return RouteCard(
                        route: route,
                        onTap: () {
                          Navigator.pushNamed(
                            context,
                            AppRoutes.search,
                            arguments: {
                              'departurePort': route.departurePort,
                              'arrivalPort': route.arrivalPort,
                              'departureDate': DateTime.now(),
                            },
                          );
                        },
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}