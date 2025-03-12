<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan Tiket Ferry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .content {
            padding: 20px 0;
        }
        .booking-details {
            margin-bottom: 20px;
        }
        .booking-details h3 {
            margin-top: 0;
            color: #4a6fb3;
        }
        .route-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .passenger-details {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
        }
        .vehicle-details {
            margin-bottom: 20px;
        }
        .payment-details {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #777;
        }
        .button {
            display: inline-block;
            background-color: #4a6fb3;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .button:hover {
            background-color: #3a5a99;
        }
        .important-note {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 10px 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Ferry Ticket Logo" class="logo">
            <h2>Konfirmasi Pemesanan Tiket Ferry</h2>
        </div>

        <div class="content">
            <p>Halo {{ $booking->user->name }},</p>
            <p>Terima kasih telah melakukan pemesanan tiket ferry. Berikut adalah detail pemesanan Anda:</p>

            <div class="booking-details">
                <h3>Informasi Pemesanan</h3>
                <p><strong>Kode Booking:</strong> {{ $booking->booking_code }}</p>
                <p><strong>Tanggal Pemesanan:</strong> {{ $booking->created_at->format('d M Y H:i') }}</p>
                <p><strong>Status:</strong> {{ $booking->status }}</p>
            </div>

            <div class="route-details">
                <h3>Detail Perjalanan</h3>
                <p><strong>Rute:</strong> {{ $booking->schedule->route->origin }} - {{ $booking->schedule->route->destination }}</p>
                <p><strong>Tanggal Keberangkatan:</strong> {{ $booking->booking_date->format('d M Y') }}</p>
                <p><strong>Waktu Keberangkatan:</strong> {{ $booking->schedule->departure_time->format('H:i') }}</p>
                <p><strong>Estimasi Waktu Kedatangan:</strong> {{ $booking->schedule->arrival_time->format('H:i') }}</p>
                <p><strong>Kapal:</strong> {{ $booking->schedule->ferry->name }}</p>
            </div>

            <div class="passenger-details">
                <h3>Detail Penumpang</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe ID</th>
                            <th>Nomor ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->passengers as $passenger)
                        <tr>
                            <td>{{ $passenger->name }}</td>
                            <td>{{ $passenger->id_type }}</td>
                            <td>{{ $passenger->id_number }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($booking->vehicles->count() > 0)
            <div class="vehicle-details">
                <h3>Detail Kendaraan</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Plat Nomor</th>
                            <th>Berat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->type_name }}</td>
                            <td>{{ $vehicle->license_plate }}</td>
                            <td>{{ $vehicle->weight ? $vehicle->weight . ' kg' : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div class="payment-details">
                <h3>Detail Pembayaran</h3>
                <p><strong>Total Pembayaran:</strong> Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                <p><strong>Metode Pembayaran:</strong> {{ $booking->latestPayment() ? $booking->latestPayment()->payment_method_name : 'Belum dibayar' }}</p>
                <p><strong>Status Pembayaran:</strong> {{ $booking->latestPayment() ? $booking->latestPayment()->status : 'PENDING' }}</p>
            </div>

            <div class="important-note">
                <p><strong>Catatan Penting:</strong></p>
                <p>Harap tiba di pelabuhan minimal 30 menit sebelum waktu keberangkatan. Jangan lupa untuk membawa bukti identitas yang sesuai untuk verifikasi.</p>
            </div>

            <p>Tiket elektronik Anda dapat dilihat dan diunduh melalui aplikasi atau dengan menekan tombol di bawah ini:</p>
            <p style="text-align: center;">
                <a href="{{ url('/tickets/' . $booking->booking_code) }}" class="button">Lihat Tiket</a>
            </p>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Ferry Ticket System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
