<?php
// Jika di root project:
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

// Jika di folder scripts:
// require __DIR__.'/../vendor/autoload.php';
// $app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ID pembayaran yang akan diupdate
$paymentId = 32; // Sesuaikan dengan ID payment yang bermasalah

// Ambil payment dan cek status
$paymentService = app(\App\Services\PaymentService::class);
$payment = \App\Models\Payment::find($paymentId);

if (!$payment) {
    echo "Payment dengan ID $paymentId tidak ditemukan\n";
    exit(1);
}

echo "Status payment sebelum update: " . $payment->status . "\n";

// Update status dari Midtrans
$newStatus = $paymentService->checkPaymentStatus($payment);

echo "Status payment setelah update: " . $newStatus . "\n";

// Update booking jika pembayaran sukses
if ($newStatus === 'SUCCESS' && $payment->booking->status === 'PENDING') {
    $payment->booking->status = 'CONFIRMED';
    $payment->booking->save();

    echo "Status booking diupdate menjadi: " . $payment->booking->status . "\n";

    // Generate tickets jika diperlukan
    $ticketService = app(\App\Services\TicketService::class);
    $result = $ticketService->generateTicketsForBooking($payment->booking);

    echo "Tickets generated: " . ($result['success'] ? 'Ya' : 'Tidak') . "\n";
}

echo "Proses update selesai!\n";
