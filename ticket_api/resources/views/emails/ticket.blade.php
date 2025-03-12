<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket Ferry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 0;
            margin: 0;
        }
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            position: relative;
        }
        .header {
            background-color: #4a6fb3;
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .ticket-id {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 14px;
        }
        .watermark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            z-index: -1;
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }
        .ticket-body {
            padding: 20px;
            position: relative;
        }
        .qr-code {
            float: right;
            margin-left: 20px;
            text-align: center;
        }
        .qr-code img {
            max-width: 150px;
            height: auto;
        }
        .qr-text {
            font-size: 12px;
            margin-top: 5px;
        }
        .route-details {
            margin-bottom: 20px;
        }
        .route-name {
            font-size: 24px;
            font-weight: bold;
            color: #4a6fb3;
            margin-bottom: 10px;
        }
        .departure-info {
            display: flex;
            margin-bottom: 15px;
        }
        .departure-date, .departure-time {
            flex: 1;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .section-value {
            font-size: 16px;
            margin-bottom: 15px;
        }
        .passenger-details {
            margin-bottom: 20px;
        }
        .vehicle-details {
            margin-bottom: 20px;
        }
        .divider {
            height: 1px;
            background-color: #ddd;
            margin: 20px 0;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 15px 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
        }
        .boarding-info {
            margin-top: 15px;
            padding: 15px;
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
        }
        .boarding-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #b38c00;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th, table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: normal;
        }
        @media print {
            body {
                background-color: white;
            }
            .ticket-container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Watermark -->
        <div class="watermark" style="background-image: url('{{ asset('storage/tickets/watermarks/' . $ticket->ticket_code . '.png') }}');"></div>

        <!-- Header -->
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Ferry Ticket Logo" class="logo">
            <h2>E-TICKET FERRY</h2>
            <div class="ticket-id">Ticket: {{ $ticket->ticket_code }}</div>
        </div>

        <div class="ticket-body">
            <!-- QR Code -->
            <div class="qr-code">
                <img src="{{ asset('storage/' . $ticket->qr_code) }}" alt="QR Code">
                <div class="qr-text">Scan untuk boarding</div>
            </div>

            <!-- Route Details -->
            <div class="route-details">
                <div class="route-name">{{ $route->origin }} → {{ $route->destination }}</div>
                <div class="departure-info">
                    <div class="departure-date">
                        <div class="section-title">Tanggal Keberangkatan</div>
                        <div class="section-value">{{ $departureDate }}</div>
                    </div>
                    <div class="departure-time">
                        <div class="section-title">Waktu Keberangkatan</div>
                        <div class="section-value">{{ $departureTime }}</div>
                    </div>
                </div>
                <div class="departure-info">
                    <div class="departure-date">
                        <div class="section-title">Kapal</div>
                        <div class="section-value">{{ $ferry->name }}</div>
                    </div>
                    <div class="departure-time">
                        <div class="section-title">Estimasi Durasi</div>
                        <div class="section-value">{{ $route->formatted_duration }}</div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Passenger Details -->
            <div class="passenger-details">
                <div class="section-title">DETAIL PENUMPANG</div>
                <table>
                    <tr>
                        <th>Nama</th>
                        <td>{{ $passenger->name }}</td>
                    </tr>
                    <tr>
                        <th>Tipe Identitas</th>
                        <td>{{ $passenger->id_type }}</td>
                    </tr>
                    <tr>
                        <th>Nomor Identitas</th>
                        <td>{{ $passenger->id_number }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Lahir</th>
                        <td>{{ $passenger->dob->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td>{{ $passenger->gender === 'MALE' ? 'Laki-laki' : 'Perempuan' }}</td>
                    </tr>
                    @if($ticket->seat_number)
                    <tr>
                        <th>Nomor Kursi</th>
                        <td>{{ $ticket->seat_number }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            @if($vehicle)
            <div class="divider"></div>

            <!-- Vehicle Details -->
            <div class="vehicle-details">
                <div class="section-title">DETAIL KENDARAAN</div>
                <table>
                    <tr>
                        <th>Tipe Kendaraan</th>
                        <td>{{ $vehicle->type_name }}</td>
                    </tr>
                    <tr>
                        <th>Plat Nomor</th>
                        <td>{{ $vehicle->license_plate }}</td>
                    </tr>
                    @if($vehicle->weight)
                    <tr>
                        <th>Berat</th>
                        <td>{{ $vehicle->weight }} kg</td>
                    </tr>
                    @endif
                </table>
            </div>
            @endif

            <div class="divider"></div>

            <!-- Booking Details -->
            <div class="booking-details">
                <div class="section-title">DETAIL PEMESANAN</div>
                <table>
                    <tr>
                        <th>Kode Booking</th>
                        <td>{{ $booking->booking_code }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ $booking->status }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Pemesanan</th>
                        <td>{{ $booking->created_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Boarding Info -->
            <div class="boarding-info">
                <div class="boarding-title">INFORMASI BOARDING</div>
                <p>Silakan tiba di pelabuhan minimal 30 menit sebelum waktu keberangkatan. Harap membawa identitas yang sesuai untuk verifikasi.</p>
                <p>E-ticket ini hanya berlaku untuk tanggal keberangkatan yang tertera dan akan kedaluwarsa setelah keberangkatan.</p>
            </div>
        </div>

        <div class="footer">
            <p>Ini adalah e-ticket yang sah. Tidak diperlukan stempel atau tanda tangan tambahan.</p>
            <p>&copy; {{ date('Y') }} Ferry Ticket System. Dokumen ini dihasilkan pada {{ now()->format('d M Y H:i') }}.</p>
        </div>
    </div>
</body>
</html>
