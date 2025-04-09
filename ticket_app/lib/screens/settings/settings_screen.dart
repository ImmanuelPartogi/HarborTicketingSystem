import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/theme.dart';
import '../../providers/theme_provider.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final themeProvider = Provider.of<ThemeProvider>(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pengaturan'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(AppTheme.paddingMedium),
        children: [
          // Bagian pengaturan tema
          Card(
            margin: const EdgeInsets.only(bottom: AppTheme.paddingMedium),
            child: Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Tampilan',
                    style: theme.textTheme.titleLarge,
                  ),
                  const SizedBox(height: AppTheme.paddingRegular),
                  
                  // Pemilihan mode tema
                  ListTile(
                    title: const Text('Tema Aplikasi'),
                    subtitle: Text(
                      _getThemeText(themeProvider.themeMode),
                    ),
                    leading: const Icon(Icons.color_lens),
                    trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                    onTap: () => _showThemeDialog(context, themeProvider),
                  ),
                ],
              ),
            ),
          ),
          
          // Bagian pengaturan lain bisa ditambahkan di sini
          Card(
            margin: const EdgeInsets.only(bottom: AppTheme.paddingMedium),
            child: Padding(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Tentang Aplikasi',
                    style: theme.textTheme.titleLarge,
                  ),
                  const SizedBox(height: AppTheme.paddingRegular),
                  
                  // Informasi versi aplikasi
                  ListTile(
                    title: const Text('Versi Aplikasi'),
                    subtitle: const Text('1.0.0'),
                    leading: const Icon(Icons.info_outline),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
  
  String _getThemeText(ThemeMode mode) {
    switch (mode) {
      case ThemeMode.light:
        return 'Terang';
      case ThemeMode.dark:
        return 'Gelap';
      case ThemeMode.system:
        return 'Mengikuti sistem';
    }
  }
  
  void _showThemeDialog(BuildContext context, ThemeProvider themeProvider) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Pilih tema'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildThemeOption(
                context,
                title: 'Terang',
                icon: Icons.wb_sunny,
                mode: ThemeMode.light,
                themeProvider: themeProvider,
              ),
              const Divider(),
              _buildThemeOption(
                context,
                title: 'Gelap',
                icon: Icons.nights_stay,
                mode: ThemeMode.dark,
                themeProvider: themeProvider,
              ),
              const Divider(),
              _buildThemeOption(
                context,
                title: 'Mengikuti sistem',
                icon: Icons.settings_brightness,
                mode: ThemeMode.system,
                themeProvider: themeProvider,
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
          ],
        );
      },
    );
  }
  
  Widget _buildThemeOption(
    BuildContext context, {
    required String title,
    required IconData icon,
    required ThemeMode mode,
    required ThemeProvider themeProvider,
  }) {
    final isSelected = themeProvider.themeMode == mode;
    
    return ListTile(
      leading: Icon(icon),
      title: Text(title),
      trailing: isSelected ? const Icon(Icons.check, color: AppTheme.primaryColor) : null,
      onTap: () {
        themeProvider.setThemeMode(mode);
        Navigator.pop(context);
      },
    );
  }
}