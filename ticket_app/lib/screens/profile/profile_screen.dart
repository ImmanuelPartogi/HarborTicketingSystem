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
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      // Refresh user data from server if needed
      await authProvider.getCurrentUser();
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _navigateToEditProfile() {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => const EditProfileScreen(),
      ),
    ).then((_) {
      // Refresh data when coming back from edit screen
      _loadUserData();
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;

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
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: 'Loading profile...',
        child: user == null
            ? const Center(
                child: Text('User data not available'),
              )
            : RefreshIndicator(
                onRefresh: _loadUserData,
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
                        user.identityType != null ? _formatIdType(user.identityType!) : 'Not provided',
                        Icons.badge,
                      ),
                      
                      const SizedBox(height: AppTheme.paddingMedium),
                      
                      _buildInfoRow(
                        theme,
                        'ID Number', 
                        user.identityNumber != null && user.identityNumber!.isNotEmpty 
                            ? user.identityNumber! 
                            : 'Not provided',
                        Icons.credit_card,
                      ),
                      
                      const SizedBox(height: AppTheme.paddingMedium),
                      
                      _buildInfoRow(
                        theme,
                        'Gender', 
                        user.gender != null ? (user.gender == 'm' ? 'Male' : 'Female') : 'Not provided',
                        Icons.person,
                      ),
                      
                      const SizedBox(height: AppTheme.paddingMedium),
                      
                      _buildInfoRow(
                        theme,
                        'Date of Birth', 
                        user.dateOfBirth != null && user.dateOfBirth!.isNotEmpty 
                            ? user.dateOfBirth! 
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
              ),
      ),
    );
  }

  String _formatIdType(String idType) {
    switch (idType) {
      case 'ktp':
        return 'KTP';
      case 'sim':
        return 'SIM';
      case 'passport':
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

  Widget _buildInfoRow(ThemeData theme, String label, String value, IconData icon, {int maxLines = 1}) {
    return Container(
      padding: const EdgeInsets.all(AppTheme.paddingRegular),
      decoration: BoxDecoration(
        color: theme.cardColor,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
      ),
      child: Row(
        crossAxisAlignment: maxLines > 1 ? CrossAxisAlignment.start : CrossAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: theme.primaryColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
            ),
            child: Icon(
              icon,
              color: theme.primaryColor,
              size: 20,
            ),
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