import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_indicator.dart';
import '../../models/payment_model.dart';

class PaymentDetailScreen extends StatefulWidget {
  final int bookingId;

  const PaymentDetailScreen({Key? key, required this.bookingId})
    : super(key: key);

  @override
  State<PaymentDetailScreen> createState() => _PaymentDetailScreenState();
}

class _PaymentDetailScreenState extends State<PaymentDetailScreen> {
  bool _isLoading = false;
  bool _isCheckingStatus = false;
  Timer? _paymentCheckTimer;
  Timer? _countdownTimer;
  int _remainingSeconds = 0;

  @override
  void initState() {
    super.initState();

    // PERBAIKAN: Gunakan addPostFrameCallback untuk menunda operasi sampai setelah build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadPaymentDetails();
      _startPaymentTimer();
    });
  }

  void _startPaymentTimer() {
    // Memulai countdown timer
    _startCountdownTimer();

    // Memulai timer pengecekan status secara periodik
    _startStatusCheckTimer();
  }

  @override
  void dispose() {
    _paymentCheckTimer?.cancel();
    _countdownTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadPaymentDetails() async {
    if (!mounted) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );

      // PERBAIKAN: Tambahkan delay untuk memastikan data tersedia di server
      await Future.delayed(Duration(milliseconds: 500));

      // Membersihkan cache booking saat ini untuk memaksa refresh
      bookingProvider.clearCurrentBooking();

      // PERBAIKAN: Tambahkan retry mechanism dengan delay bertingkat
      bool success = false;
      int retryCount = 0;

      while (!success && retryCount < 3) {
        try {
          await bookingProvider.fetchBookingDetail(
            widget.bookingId,
            forceRefresh: true,
          );
          success = bookingProvider.currentBooking?.payment != null;

          if (success) break;

          retryCount++;
          await Future.delayed(Duration(seconds: 1 * retryCount));
        } catch (e) {
          retryCount++;
          print('Error on attempt $retryCount: $e');
          await Future.delayed(Duration(seconds: 1 * retryCount));
        }
      }

      // Cek apakah data payment berhasil dimuat
      if (!success && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Gagal memuat data pembayaran. Silakan coba lagi.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
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

  void _startCountdownTimer() {
    final bookingProvider = Provider.of<BookingProvider>(
      context,
      listen: false,
    );
    final payment = bookingProvider.currentBooking?.payment;

    if (payment == null) return;

    // Hitung selisih waktu antara sekarang dan waktu expire
    final now = DateTime.now();
    final expiry = payment.expiredAt;
    final difference = expiry.difference(now);

    if (difference.isNegative) {
      _remainingSeconds = 0;
      _showPaymentExpiredDialog();
      return;
    }

    _remainingSeconds = difference.inSeconds;

    // Batalkan countdown timer sebelumnya jika ada
    _countdownTimer?.cancel();

    // Mulai countdown timer baru
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_remainingSeconds <= 0) {
        timer.cancel();
        _showPaymentExpiredDialog();
      } else {
        setState(() {
          _remainingSeconds--;
        });
      }
    });
  }

  void _startStatusCheckTimer() {
    // Batalkan timer sebelumnya jika ada
    _paymentCheckTimer?.cancel();

    // Cek status setiap 30 detik
    _paymentCheckTimer = Timer.periodic(const Duration(seconds: 30), (timer) {
      _checkPaymentStatus();
    });
  }

  String get _formattedRemainingTime {
    final hours = _remainingSeconds ~/ 3600;
    final minutes = (_remainingSeconds % 3600) ~/ 60;
    final seconds = _remainingSeconds % 60;

    if (hours > 0) {
      return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
    } else {
      return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
    }
  }

  Future<void> _checkPaymentStatus() async {
    if (_isCheckingStatus) return;

    setState(() {
      _isCheckingStatus = true;
    });

    try {
      final bookingProvider = Provider.of<BookingProvider>(
        context,
        listen: false,
      );

      final isCompleted = await bookingProvider.checkPaymentStatus();

      if (isCompleted && mounted) {
        _paymentCheckTimer?.cancel();
        _countdownTimer?.cancel();

        // Tampilkan dialog sukses lalu navigasi ke halaman konfirmasi booking
        _showPaymentSuccessDialog();
      }
    } catch (e) {
      print('Error checking payment status: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isCheckingStatus = false;
        });
      }
    }
  }

  void _showPaymentExpiredDialog() {
    if (!mounted) return;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder:
          (context) => AlertDialog(
            title: const Text('Waktu Pembayaran Habis'),
            content: const Text(
              'Waktu pembayaran Anda telah habis. Booking akan dibatalkan.',
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pushNamedAndRemoveUntil(
                    context,
                    AppRoutes.home,
                    (route) => false,
                  );
                },
                child: const Text('Kembali ke Beranda'),
              ),
            ],
          ),
    );
  }

  void _showPaymentSuccessDialog() {
    if (!mounted) return;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder:
          (context) => AlertDialog(
            title: const Text('Pembayaran Berhasil'),
            content: const Text(
              'Pembayaran Anda telah berhasil diproses. Silakan lihat detail booking Anda.',
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pushNamedAndRemoveUntil(
                    context,
                    AppRoutes.bookingConfirmation,
                    (route) => false,
                    arguments: {'bookingId': widget.bookingId},
                  );
                },
                child: const Text('Lihat Booking'),
              ),
            ],
          ),
    );
  }

  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$label telah disalin ke clipboard'),
        backgroundColor: Colors.green,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  Future<void> _openPaymentApp(String? url) async {
    if (url == null || url.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Link aplikasi tidak tersedia'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    try {
      if (await canLaunch(url)) {
        await launch(url);
      } else {
        throw 'Tidak dapat membuka aplikasi';
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final currencyFormat = NumberFormat.currency(
      locale: 'id',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Pembayaran'),
        automaticallyImplyLeading: false,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            showDialog(
              context: context,
              builder:
                  (context) => AlertDialog(
                    title: const Text('Batalkan Pembayaran?'),
                    content: const Text(
                      'Apakah Anda yakin ingin kembali? Proses pembayaran belum selesai.',
                    ),
                    actions: [
                      TextButton(
                        onPressed: () {
                          Navigator.pop(context);
                        },
                        child: const Text('Tidak'),
                      ),
                      TextButton(
                        onPressed: () {
                          Navigator.pop(context);
                          Navigator.pop(context);
                        },
                        child: const Text('Ya, Kembali'),
                      ),
                    ],
                  ),
            );
          },
        ),
      ),
      body: LoadingOverlay(
        isLoading: _isLoading,
        loadingMessage: 'Memuat detail pembayaran...',
        child: Column(
          children: [
            // Timer pembayaran
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingRegular),
              color: Colors.amber,
              child: Row(
                children: [
                  const Icon(Icons.timer, color: Colors.black87, size: 20),
                  const SizedBox(width: AppTheme.paddingSmall),
                  Text(
                    'Pembayaran berakhir dalam: $_formattedRemainingTime',
                    style: const TextStyle(
                      color: Colors.black87,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),

            // Konten utama
            Expanded(
              child: Consumer<BookingProvider>(
                builder: (context, bookingProvider, _) {
                  final booking = bookingProvider.currentBooking;
                  final payment = booking?.payment;

                  if (booking == null || payment == null) {
                    return const Center(
                      child: Text('Data pembayaran tidak tersedia'),
                    );
                  }

                  return SingleChildScrollView(
                    padding: const EdgeInsets.all(AppTheme.paddingMedium),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Panel status pembayaran
                        Container(
                          padding: const EdgeInsets.all(AppTheme.paddingMedium),
                          decoration: BoxDecoration(
                            color:
                                payment.isPending
                                    ? Colors.amber.shade50
                                    : Colors.green.shade50,
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusMedium,
                            ),
                            border: Border.all(
                              color:
                                  payment.isPending
                                      ? Colors.amber
                                      : Colors.green,
                              width: 1,
                            ),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                payment.isPending
                                    ? Icons.pending
                                    : Icons.check_circle,
                                color:
                                    payment.isPending
                                        ? Colors.amber
                                        : Colors.green,
                                size: 28,
                              ),
                              const SizedBox(width: AppTheme.paddingRegular),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      payment.isPending
                                          ? 'Menunggu Pembayaran'
                                          : payment.statusText,
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: AppTheme.fontSizeMedium,
                                        color:
                                            payment.isPending
                                                ? Colors.amber.shade800
                                                : Colors.green.shade800,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      payment.isPending
                                          ? 'Selesaikan pembayaran sebelum ${DateFormat('HH:mm').format(payment.expiredAt)}'
                                          : 'Pembayaran telah berhasil',
                                      style: TextStyle(
                                        fontSize: AppTheme.fontSizeSmall,
                                        color:
                                            payment.isPending
                                                ? Colors.amber.shade800
                                                : Colors.green.shade800,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              if (payment.isPending)
                                ElevatedButton(
                                  onPressed:
                                      _isCheckingStatus
                                          ? null
                                          : _checkPaymentStatus,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: theme.primaryColor,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: AppTheme.paddingRegular,
                                      vertical: AppTheme.paddingSmall,
                                    ),
                                  ),
                                  child:
                                      _isCheckingStatus
                                          ? const SizedBox(
                                            width: 16,
                                            height: 16,
                                            child: CircularProgressIndicator(
                                              strokeWidth: 2,
                                              valueColor:
                                                  AlwaysStoppedAnimation<Color>(
                                                    Colors.white,
                                                  ),
                                            ),
                                          )
                                          : const Text('Cek Status'),
                                ),
                            ],
                          ),
                        ),

                        const SizedBox(height: AppTheme.paddingLarge),

                        // Ringkasan booking
                        Container(
                          padding: const EdgeInsets.all(AppTheme.paddingMedium),
                          decoration: BoxDecoration(
                            color: theme.cardColor,
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusMedium,
                            ),
                            border: Border.all(color: Colors.grey.shade200),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text(
                                    'Ringkasan Booking',
                                    style: TextStyle(
                                      fontSize: AppTheme.fontSizeMedium,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  Text(
                                    'Booking ID: ${booking.id} (${booking.bookingNumber})',
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: AppTheme.paddingRegular),
                              const Divider(),
                              const SizedBox(height: AppTheme.paddingSmall),

                              // Rute
                              if (booking.schedule?.route != null)
                                _buildInfoRow(
                                  label: 'Rute',
                                  value: booking.schedule!.route!.routeName,
                                ),

                              // Waktu keberangkatan
                              if (booking.schedule != null)
                                _buildInfoRow(
                                  label: 'Waktu Keberangkatan',
                                  value:
                                      '${DateFormat('EEE, dd MMM yyyy').format(booking.schedule!.departureTime)} ${booking.schedule!.formattedDepartureTime}',
                                ),

                              // Jumlah penumpang
                              _buildInfoRow(
                                label: 'Penumpang',
                                value:
                                    '${booking.passengerCount} ${booking.passengerCount > 1 ? 'orang' : 'orang'}',
                              ),

                              // Kendaraan (jika ada)
                              if (booking.hasVehicles &&
                                  booking.vehicles != null &&
                                  booking.vehicles!.isNotEmpty)
                                _buildInfoRow(
                                  label: 'Kendaraan',
                                  value:
                                      '${booking.vehicles!.first.typeText} (${booking.vehicles!.first.licensePlate})',
                                ),

                              const SizedBox(height: AppTheme.paddingSmall),
                              const Divider(),
                              const SizedBox(height: AppTheme.paddingSmall),

                              // Total pembayaran
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text(
                                    'Total Pembayaran',
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: AppTheme.fontSizeMedium,
                                    ),
                                  ),
                                  Text(
                                    currencyFormat.format(booking.totalAmount),
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: AppTheme.fontSizeMedium,
                                      color: theme.primaryColor,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: AppTheme.paddingLarge),

                        // Detail metode pembayaran
                        Text(
                          'Detail Metode Pembayaran',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeMedium,
                            fontWeight: FontWeight.bold,
                            color: theme.textTheme.displaySmall?.color,
                          ),
                        ),
                        const SizedBox(height: AppTheme.paddingMedium),

                        Container(
                          padding: const EdgeInsets.all(AppTheme.paddingMedium),
                          decoration: BoxDecoration(
                            color: theme.cardColor,
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusMedium,
                            ),
                            border: Border.all(color: Colors.grey.shade200),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Logo dan nama metode pembayaran
                              Row(
                                children: [
                                  Container(
                                    width: 48,
                                    height: 48,
                                    decoration: BoxDecoration(
                                      color: Colors.grey.shade100,
                                      borderRadius: BorderRadius.circular(
                                        AppTheme.borderRadiusSmall,
                                      ),
                                      image: DecorationImage(
                                        image: AssetImage(
                                          'assets/images/payment_methods/${payment.paymentChannel.toLowerCase()}.png',
                                        ),
                                        fit: BoxFit.contain,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: AppTheme.paddingMedium),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          '${payment.paymentMethodText} - ${payment.paymentChannelText}',
                                          style: const TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: AppTheme.fontSizeMedium,
                                          ),
                                        ),
                                        Text(
                                          'ID Transaksi: ${payment.transactionId ?? "Menunggu pembayaran"}',
                                          style: TextStyle(
                                            fontSize: AppTheme.fontSizeSmall,
                                            color:
                                                theme
                                                    .textTheme
                                                    .bodySmall
                                                    ?.color,
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

                              // Virtual Account Number (untuk pembayaran VA)
                              if (payment.paymentMethod.toUpperCase() ==
                                      'VIRTUAL_ACCOUNT' &&
                                  payment.vaNumber != null) ...[
                                const Text(
                                  'Nomor Virtual Account',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: AppTheme.fontSizeRegular,
                                  ),
                                ),
                                const SizedBox(height: AppTheme.paddingSmall),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: AppTheme.paddingMedium,
                                    vertical: AppTheme.paddingRegular,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.grey.shade50,
                                    borderRadius: BorderRadius.circular(
                                      AppTheme.borderRadiusRegular,
                                    ),
                                    border: Border.all(
                                      color: Colors.grey.shade200,
                                    ),
                                  ),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.center,
                                    children: [
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.center,
                                        children: [
                                          Text(
                                            _formatVaNumber(payment.vaNumber!),
                                            style: const TextStyle(
                                              fontSize: 24,
                                              fontWeight: FontWeight.bold,
                                              letterSpacing: 1.5,
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(
                                        height: AppTheme.paddingRegular,
                                      ),
                                      SizedBox(
                                        width: double.infinity,
                                        child: ElevatedButton.icon(
                                          onPressed:
                                              () => _copyToClipboard(
                                                payment.vaNumber!,
                                                'Nomor Virtual Account',
                                              ),
                                          icon: const Icon(Icons.copy),
                                          label: const Text('Salin Nomor VA'),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: theme.primaryColor,
                                            foregroundColor: Colors.white,
                                            padding: const EdgeInsets.symmetric(
                                              vertical: AppTheme.paddingRegular,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],

                              // QR Code (untuk pembayaran e-wallet)
                              if (payment.paymentMethod.toUpperCase() ==
                                      'E_WALLET' &&
                                  payment.qrCodeUrl != null) ...[
                                const Text(
                                  'Scan QR Code untuk Membayar',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: AppTheme.fontSizeRegular,
                                  ),
                                ),
                                const SizedBox(height: AppTheme.paddingMedium),
                                Center(
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Container(
                                        width: 250,
                                        height: 250,
                                        padding: const EdgeInsets.all(
                                          AppTheme.paddingMedium,
                                        ),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(
                                            AppTheme.borderRadiusSmall,
                                          ),
                                          border: Border.all(
                                            color: Colors.grey.shade300,
                                          ),
                                        ),
                                        child: Image.network(
                                          payment.qrCodeUrl!,
                                          fit: BoxFit.contain,
                                          errorBuilder: (
                                            context,
                                            error,
                                            stackTrace,
                                          ) {
                                            return const Center(
                                              child: Text(
                                                'QR Code tidak dapat dimuat',
                                                textAlign: TextAlign.center,
                                                style: TextStyle(
                                                  color: Colors.red,
                                                ),
                                              ),
                                            );
                                          },
                                        ),
                                      ),
                                      const SizedBox(
                                        height: AppTheme.paddingRegular,
                                      ),
                                      Text(
                                        'Scan dengan aplikasi ${payment.paymentChannelText}',
                                        style: TextStyle(
                                          fontSize: AppTheme.fontSizeRegular,
                                          color:
                                              theme.textTheme.bodyMedium?.color,
                                        ),
                                      ),
                                      if (payment.deepLinkUrl != null) ...[
                                        const SizedBox(
                                          height: AppTheme.paddingMedium,
                                        ),
                                        SizedBox(
                                          width: 250,
                                          child: ElevatedButton.icon(
                                            onPressed:
                                                () => _openPaymentApp(
                                                  payment.deepLinkUrl,
                                                ),
                                            icon: const Icon(Icons.open_in_new),
                                            label: Text(
                                              'Buka Aplikasi ${payment.paymentChannelText}',
                                            ),
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor:
                                                  theme.primaryColor,
                                              foregroundColor: Colors.white,
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    vertical:
                                                        AppTheme.paddingRegular,
                                                  ),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),

                        const SizedBox(height: AppTheme.paddingLarge),

                        // Instruksi pembayaran
                        Text(
                          'Cara Pembayaran',
                          style: TextStyle(
                            fontSize: AppTheme.fontSizeMedium,
                            fontWeight: FontWeight.bold,
                            color: theme.textTheme.displaySmall?.color,
                          ),
                        ),
                        const SizedBox(height: AppTheme.paddingMedium),

                        Container(
                          padding: const EdgeInsets.all(AppTheme.paddingMedium),
                          decoration: BoxDecoration(
                            color: theme.cardColor,
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusMedium,
                            ),
                            border: Border.all(color: Colors.grey.shade200),
                          ),
                          child: _buildPaymentInstructions(payment),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),

            // Tombol bawah
            Container(
              padding: const EdgeInsets.all(AppTheme.paddingMedium),
              decoration: BoxDecoration(
                color: Theme.of(context).cardColor,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 4,
                    offset: const Offset(0, -2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Consumer<BookingProvider>(
                      builder: (context, bookingProvider, _) {
                        final payment = bookingProvider.currentBooking?.payment;

                        if (payment != null && payment.isCompleted) {
                          return CustomButton(
                            text: 'Lihat Detail Booking',
                            onPressed: () {
                              Navigator.pushNamedAndRemoveUntil(
                                context,
                                AppRoutes.bookingConfirmation,
                                (route) => false,
                                arguments: {'bookingId': widget.bookingId},
                              );
                            },
                            type: ButtonType.primary,
                          );
                        } else {
                          return CustomButton(
                            text: 'Cek Status Pembayaran',
                            onPressed:
                                _isCheckingStatus ? null : _checkPaymentStatus,
                            type: ButtonType.primary,
                            isLoading: _isCheckingStatus,
                          );
                        }
                      },
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow({required String label, required String value}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppTheme.paddingSmall),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              color: Theme.of(context).textTheme.bodyMedium?.color,
            ),
          ),
          const SizedBox(width: AppTheme.paddingRegular),
          Flexible(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w500),
              textAlign: TextAlign.right,
            ),
          ),
        ],
      ),
    );
  }

  String _formatVaNumber(String vaNumber) {
    // Format nomor VA untuk keterbacaan yang lebih baik
    // Contoh: 1234567890 -> 1234 5678 90
    String cleaned = vaNumber.replaceAll(RegExp(r'\s+'), '');

    if (cleaned.length <= 4) {
      return cleaned;
    } else if (cleaned.length <= 8) {
      return '${cleaned.substring(0, 4)} ${cleaned.substring(4)}';
    } else {
      String formatted = '';
      int i = 0;

      while (i < cleaned.length) {
        if (i > 0 && i % 4 == 0) {
          formatted += ' ';
        }
        formatted += cleaned[i];
        i++;
      }

      return formatted;
    }
  }

  Widget _buildPaymentInstructions(Payment payment) {
    final String instructions;

    // Instruksi berdasarkan metode pembayaran
    if (payment.paymentMethod.toUpperCase() == 'VIRTUAL_ACCOUNT') {
      switch (payment.paymentChannel.toUpperCase()) {
        case 'BCA':
          instructions = '''
1. Login ke aplikasi BCA Mobile atau Internet Banking.
2. Pilih menu "Transfer" > "Virtual Account".
3. Masukkan nomor Virtual Account: ${payment.vaNumber ?? 'Tidak tersedia'}.
4. Konfirmasi detail pembayaran dan jumlah: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
5. Masukkan PIN atau password untuk mengotorisasi pembayaran.
6. Simpan bukti pembayaran Anda.

Anda juga dapat membayar melalui ATM BCA:
1. Masukkan kartu ATM dan PIN Anda.
2. Pilih "Transaksi Lainnya".
3. Pilih "Transfer".
4. Pilih "Ke Rekening BCA Virtual Account".
5. Masukkan nomor Virtual Account.
6. Konfirmasi detail pembayaran dan selesaikan transaksi.
''';
          break;
        case 'BNI':
          instructions = '''
1. Login ke aplikasi BNI Mobile Banking atau Internet Banking.
2. Pilih menu "Transfer" > "Virtual Account" atau "Transfer ke BNI Virtual Account".
3. Masukkan nomor Virtual Account: ${payment.vaNumber ?? 'Tidak tersedia'}.
4. Konfirmasi detail pembayaran dan jumlah: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
5. Masukkan PIN atau password untuk mengotorisasi pembayaran.
6. Simpan bukti pembayaran Anda.

Anda juga dapat membayar melalui ATM BNI:
1. Masukkan kartu ATM dan PIN Anda.
2. Pilih "Menu Lainnya".
3. Pilih "Transfer".
4. Pilih "Rekening Tabungan".
5. Pilih "Ke Rekening BNI".
6. Masukkan nomor Virtual Account.
7. Konfirmasi detail pembayaran dan selesaikan transaksi.
''';
          break;
        case 'BRI':
          instructions = '''
1. Login ke aplikasi BRI Mobile Banking atau Internet Banking.
2. Pilih menu "Transfer" > "BRIVA".
3. Masukkan nomor Virtual Account: ${payment.vaNumber ?? 'Tidak tersedia'}.
4. Konfirmasi detail pembayaran dan jumlah: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
5. Masukkan PIN atau password untuk mengotorisasi pembayaran.
6. Simpan bukti pembayaran Anda.

Anda juga dapat membayar melalui ATM BRI:
1. Masukkan kartu ATM dan PIN Anda.
2. Pilih "Transaksi Lainnya".
3. Pilih "Pembayaran".
4. Pilih "BRIVA".
5. Masukkan nomor BRIVA.
6. Konfirmasi detail pembayaran dan selesaikan transaksi.
''';
          break;
        case 'MANDIRI':
          instructions = '''
1. Login ke aplikasi Mandiri Mobile Banking atau Internet Banking.
2. Pilih menu "Pembayaran" > "Multi Payment".
3. Pilih "Ferry Ticketing" sebagai biller.
4. Masukkan kode pembayaran Anda: ${payment.vaNumber ?? 'Tidak tersedia'}.
5. Konfirmasi detail pembayaran dan jumlah: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
6. Masukkan PIN atau password untuk mengotorisasi pembayaran.
7. Simpan bukti pembayaran Anda.

Anda juga dapat membayar melalui ATM Mandiri:
1. Masukkan kartu ATM dan PIN Anda.
2. Pilih "Bayar/Beli".
3. Pilih "Multi Payment".
4. Masukkan kode perusahaan "70012" (Ferry Ticketing).
5. Masukkan kode pembayaran Anda.
6. Konfirmasi detail pembayaran dan selesaikan transaksi.
''';
          break;
        case 'PERMATA':
          instructions = '''
1. Login ke aplikasi Permata Mobile Banking atau Internet Banking.
2. Pilih menu "Pembayaran" > "Pembayaran Tagihan".
3. Pilih "Virtual Account".
4. Masukkan nomor Virtual Account: ${payment.vaNumber ?? 'Tidak tersedia'}.
5. Konfirmasi detail pembayaran dan jumlah: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
6. Masukkan PIN atau password untuk mengotorisasi pembayaran.
7. Simpan bukti pembayaran Anda.

Anda juga dapat membayar melalui ATM Permata:
1. Masukkan kartu ATM dan PIN Anda.
2. Pilih "Transaksi Lainnya".
3. Pilih "Pembayaran".
4. Pilih "Pembayaran Lainnya".
5. Pilih "Virtual Account".
6. Masukkan nomor Virtual Account.
7. Konfirmasi detail pembayaran dan selesaikan transaksi.
''';
          break;
        default:
          instructions = '''
1. Login ke aplikasi mobile banking atau internet banking Anda.
2. Pilih menu "Transfer" > "Virtual Account" atau opsi serupa.
3. Masukkan nomor Virtual Account: ${payment.vaNumber ?? 'Tidak tersedia'}.
4. Konfirmasi detail pembayaran dan jumlah: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
5. Masukkan PIN atau password untuk mengotorisasi pembayaran.
6. Simpan bukti pembayaran Anda.

Anda juga dapat membayar melalui ATM:
1. Masukkan kartu ATM dan PIN Anda.
2. Pilih menu "Transfer" atau "Pembayaran".
3. Pilih "Virtual Account" atau opsi serupa.
4. Masukkan nomor Virtual Account.
5. Konfirmasi detail pembayaran dan selesaikan transaksi.
''';
      }
    } else if (payment.paymentMethod.toUpperCase() == 'E_WALLET') {
      if (payment.qrCodeUrl != null) {
        instructions = '''
1. Buka aplikasi ${payment.paymentChannelText} di ponsel Anda.
2. Tap "Scan" atau "Bayar" di aplikasi Anda.
3. Scan QR code yang ditampilkan di atas.
4. Konfirmasi detail pembayaran dan jumlah: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
5. Masukkan PIN Anda untuk mengotorisasi pembayaran.
6. Tunggu pesan konfirmasi pembayaran berhasil.
''';
      } else if (payment.deepLinkUrl != null) {
        instructions = '''
1. Tap tombol "Buka Aplikasi ${payment.paymentChannelText}" di atas.
2. Anda akan diarahkan ke aplikasi ${payment.paymentChannelText}.
3. Konfirmasi detail pembayaran dan jumlah: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
4. Masukkan PIN Anda untuk mengotorisasi pembayaran.
5. Tunggu pesan konfirmasi pembayaran berhasil.
''';
      } else {
        instructions = '''
1. Buka aplikasi ${payment.paymentChannelText} di ponsel Anda.
2. Pilih menu "Bayar" atau "Scan".
3. Masukkan jumlah pembayaran: ${NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0).format(payment.amount)}.
4. Konfirmasi detail pembayaran.
5. Masukkan PIN Anda untuk mengotorisasi pembayaran.
6. Tunggu pesan konfirmasi pembayaran berhasil.
''';
      }
    } else {
      instructions = '''
1. Ikuti petunjuk pembayaran untuk metode pembayaran yang Anda pilih.
2. Gunakan detail pembayaran yang disediakan di atas.
3. Pastikan Anda menyelesaikan pembayaran sebelum waktu habis.
4. Setelah pembayaran, tunggu pesan konfirmasi.
''';
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Langkah-langkah Pembayaran',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: AppTheme.fontSizeMedium,
          ),
        ),
        const SizedBox(height: AppTheme.paddingMedium),
        Text(
          instructions,
          style: const TextStyle(fontSize: AppTheme.fontSizeRegular),
        ),
        const SizedBox(height: AppTheme.paddingMedium),
        Container(
          padding: const EdgeInsets.all(AppTheme.paddingRegular),
          decoration: BoxDecoration(
            color: Colors.blue.shade50,
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusRegular),
            border: Border.all(color: Colors.blue.shade200),
          ),
          child: Row(
            children: [
              Icon(Icons.info_outline, color: Colors.blue.shade700, size: 24),
              const SizedBox(width: AppTheme.paddingRegular),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Catatan Penting',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: AppTheme.fontSizeRegular,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Pembayaran akan diproses otomatis oleh sistem. Harap jangan tutup halaman ini sampai Anda menyelesaikan pembayaran. Pastikan nominal yang dibayarkan sesuai hingga digit terakhir.',
                      style: TextStyle(
                        fontSize: AppTheme.fontSizeSmall,
                        color: Colors.blue.shade800,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}


class LoadingOverlay extends StatelessWidget {
  final bool isLoading;
  final Widget child;
  final String loadingMessage;

  const LoadingOverlay({
    Key? key,
    required this.isLoading,
    required this.child,
    this.loadingMessage = 'Loading...',
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        child,
        if (isLoading)
          Container(
            color: Colors.black.withOpacity(0.3),
            child: Center(
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const CircularProgressIndicator(),
                    const SizedBox(height: 20),
                    Text(
                      loadingMessage,
                      style: const TextStyle(
                        color: Colors.black87,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
      ],
    );
  }
}
