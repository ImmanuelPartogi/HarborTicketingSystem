import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_indicator.dart';
import 'edit_profile_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _isLoading = false;  // Start with false to avoid immediate loading indicator
  bool _isInitialized = false;

  @override
  void initState() {
    super.initState();
    // Defer loading to after first build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _loadUserData();
      }
    });
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    // Load data if not already initialized and not already loading
    if (!_isInitialized && !_isLoading && mounted) {
      _loadUserData();
    }
  }

  Future<void> _loadUserData() async {
    if (!mounted || _isLoading) return;

    setState(() {
      _isLoading = true;
    });

    try {
      // Use Provider.of with listen: false to avoid rebuild loops
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      
      // Get current user data
      await authProvider.getCurrentUser();
      
      // ALWAYS check mounted before setState
      if (mounted) {
        setState(() {
          _isLoading = false;
          _isInitialized = true;
        });
      }
    } catch (e) {
      // Safety check before setState
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading profile: $e')),
        );
      }
    }
  }

  void _navigateToEditProfile() {
    // Use Navigator.of to get the right context
    Navigator.of(context).push(
      MaterialPageRoute(builder: (context) => const EditProfileScreen()),
    ).then((_) {
      // IMPORTANT: Check mounted before calling setState indirectly via _loadUserData
      if (mounted) {
        _loadUserData();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    // Use Consumer pattern for the data-dependent part of UI
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit),
            onPressed: _navigateToEditProfile,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: LoadingIndicator())
          : Consumer<AuthProvider>(
              builder: (context, authProvider, _) {
                final user = authProvider.user;
                
                if (user == null) {
                  return const Center(child: Text('User data not available'));
                }
                
                return RefreshIndicator(
                  onRefresh: () async {
                    // Make sure we're still mounted before refreshing
                    if (mounted) {
                      await _loadUserData();
                    }
                  },
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(AppTheme.paddingMedium),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Profile Header with Avatar
                        Center(
                          child: Column(
                            children: [
                              CircleAvatar(
                                radius: 50,
                                backgroundColor: theme.primaryColor.withOpacity(0.2),
                                child: Text(
                                  user.name.isNotEmpty
                                      ? user.name[0].toUpperCase()
                                      : '?',
                                  style: TextStyle(
                                    fontSize: 36,
                                    fontWeight: FontWeight.bold,
                                    color: theme.primaryColor,
                                  ),
                                ),
                              ),
                              const SizedBox(height: AppTheme.paddingMedium),
                              Text(
                                user.name,
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeLarge,
                                  fontWeight: FontWeight.bold,
                                  color: theme.textTheme.displaySmall?.color,
                                ),
                              ),
                              const SizedBox(height: AppTheme.paddingSmall),
                              Text(
                                user.email,
                                style: TextStyle(
                                  fontSize: AppTheme.fontSizeRegular,
                                  color: theme.textTheme.bodySmall?.color,
                                ),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: AppTheme.paddingXLarge),

                        // Basic Information
                        _buildSectionHeader(theme, 'Basic Information'),
                        const SizedBox(height: AppTheme.paddingMedium),

                        _buildInfoRow(
                          theme,
                          'Phone Number',
                          user.phone.isNotEmpty ? user.phone : 'Not provided',
                          Icons.phone,
                        ),

                        const SizedBox(height: AppTheme.paddingLarge),

                        // Identity Information
                        _buildSectionHeader(theme, 'Identity Information'),
                        const SizedBox(height: AppTheme.paddingMedium),

                        _buildInfoRow(
                          theme,
                          'ID Type',
                          user.identityType != null
                              ? _formatIdType(user.identityType!)
                              : 'Not provided',
                          Icons.badge,
                        ),

                        const SizedBox(height: AppTheme.paddingMedium),

                        _buildInfoRow(
                          theme,
                          'ID Number',
                          user.identityNumber != null &&
                                  user.identityNumber!.isNotEmpty
                              ? user.identityNumber!
                              : 'Not provided',
                          Icons.credit_card,
                        ),

                        const SizedBox(height: AppTheme.paddingMedium),

                        _buildInfoRow(
                          theme,
                          'Gender',
                          user.gender != null
                              ? (user.gender!.toUpperCase() == 'MALE' ? 'Male' : 'Female')
                              : 'Not provided',
                          Icons.person,
                        ),

                        const SizedBox(height: AppTheme.paddingMedium),

                        _buildInfoRow(
                          theme,
                          'Date of Birth',
                          user.dateOfBirth != null &&
                                  user.dateOfBirth!.isNotEmpty
                              ? _formatDate(user.dateOfBirth!)
                              : 'Not provided',
                          Icons.calendar_today,
                        ),

                        const SizedBox(height: AppTheme.paddingLarge),

                        // Address Information
                        _buildSectionHeader(theme, 'Address Information'),
                        const SizedBox(height: AppTheme.paddingMedium),

                        _buildInfoRow(
                          theme,
                          'Address',
                          user.address != null && user.address!.isNotEmpty
                              ? user.address!
                              : 'Not provided',
                          Icons.home,
                          maxLines: 3,
                        ),

                        const SizedBox(height: AppTheme.paddingXLarge),

                        // Edit Profile Button
                        CustomButton(
                          text: 'Edit Profile',
                          onPressed: _navigateToEditProfile,
                          type: ButtonType.primary,
                          isFullWidth: true,
                        ),

                        const SizedBox(height: AppTheme.paddingLarge),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }

  // Helper to format dates from ISO to readable format
  String _formatDate(String isoDate) {
    try {
      final date = DateTime.parse(isoDate);
      return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
    } catch (e) {
      return isoDate; // Return original if parsing fails
    }
  }

  String _formatIdType(String idType) {
    switch (idType.toLowerCase()) {
      case 'ktp':
        return 'KTP';
      case 'sim':
        return 'SIM';
      case 'passport':
      case 'paspor':
        return 'Passport';
      default:
        return idType.toUpperCase();
    }
  }

  Widget _buildSectionHeader(ThemeData theme, String title) {
    return Text(
      title,
      style: TextStyle(
        fontSize: AppTheme.fontSizeMedium,
        fontWeight: FontWeight.bold,
        color: theme.textTheme.displaySmall?.color,
      ),
    );
  }

  Widget _buildInfoRow(
    ThemeData theme,
    String label,
    String value,
    IconData icon, {
    int maxLines = 1,
  }) {
    return Container(
      padding: const EdgeInsets.all(AppTheme.paddingRegular),
      decoration: BoxDecoration(
        color: theme.cardColor,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
      ),
      child: Row(
        crossAxisAlignment:
            maxLines > 1 ? CrossAxisAlignment.start : CrossAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: theme.primaryColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
            ),
            child: Icon(icon, color: theme.primaryColor, size: 20),
          ),
          const SizedBox(width: AppTheme.paddingRegular),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeSmall,
                    color: theme.textTheme.bodySmall?.color,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: AppTheme.fontSizeRegular,
                    fontWeight: FontWeight.w500,
                  ),
                  maxLines: maxLines,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}