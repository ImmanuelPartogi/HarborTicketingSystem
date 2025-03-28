<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Tickets - {{ $booking->booking_number }}</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-color: #0369A1;
            --primary-light: #E0F2FE;
            --primary-dark: #075985;
            --secondary-color: #0EA5E9;
            --accent-color: #0284C7;
            --dark-text: #0F172A;
            --light-text: #64748B;
            --border-color: #E2E8F0;
        }

        body {
            font-family: 'Nunito', sans-serif;
            color: var(--dark-text);
            background-color: #F8FAFC;
            line-height: 1.5;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Montserrat', sans-serif;
        }

        /* Ticket styling */
        .watermark {
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M50 25C59.665 25 68.3301 29.2232 74.1421 35.8579C79.9542 42.4925 82.5 50.8 82.5 59.5C82.5 61.6 78.3542 76.2425 71.2421 82.6421C64.1301 89.0418 53.665 89 50 89C46.335 89 35.8699 89.0418 28.7579 82.6421C21.6458 76.2425 17.5 61.6 17.5 59.5C17.5 55.8 18.5458 47.7575 24.3579 39.3579C30.1699 30.9582 40.335 25 50 25Z' fill='%230EA5E9' fill-opacity='0.03'/%3E%3C/svg%3E");
            background-repeat: repeat;
        }

        .ticket-shape {
            clip-path: polygon(0 0, 98% 0, 100% 4%, 100% 100%, 2% 100%, 0 96%);
        }

        .ticket-container {
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .ticket-container:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            z-index: 1;
        }

        .ticket-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(3, 105, 161, 0.1), 0 8px 10px -6px rgba(3, 105, 161, 0.1);
        }

        .ticket-serial {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 0.7rem;
            color: var(--light-text);
            letter-spacing: 1px;
        }

        .ticket-divider {
            position: relative;
            height: 2px;
            background-image: repeating-linear-gradient(to right, var(--border-color) 0, var(--border-color) 6px, transparent 6px, transparent 12px);
        }

        .ticket-divider::before,
        .ticket-divider::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
            background-color: #F8FAFC;
            border-radius: 50%;
            top: -15px;
            border: 1px dashed var(--border-color);
        }

        .ticket-divider::before {
            left: -15px;
        }

        .ticket-divider::after {
            right: -15px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35em 0.75em;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            text-transform: uppercase;
        }

        .route-path {
            position: relative;
            padding-left: 1.5rem;
        }

        .route-path::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0.75rem;
            bottom: 0.75rem;
            width: 2px;
            background: repeating-linear-gradient(to bottom, var(--secondary-color) 0, var(--secondary-color) 5px, transparent 5px, transparent 10px);
        }

        .route-path .start:before,
        .route-path .end:before {
            content: '';
            position: absolute;
            left: 0.35rem;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .route-path .start:before {
            top: 0.85rem;
            background-color: var(--primary-color);
            border: 2px solid white;
        }

        .route-path .end:before {
            bottom: 0.85rem;
            background-color: var(--accent-color);
            border: 2px solid white;
        }

        .boarding-pass {
            position: relative;
            background-color: white;
            border-top: 1px dashed var(--border-color);
            overflow: hidden;
        }

        .boarding-pass:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(var(--primary-light) 15%, transparent 16%),
                radial-gradient(var(--primary-light) 15%, transparent 16%);
            background-size: 12px 12px;
            background-position: 0 0, 6px 6px;
            opacity: 0.2;
        }

        .barcode-container {
            position: relative;
            overflow: hidden;
            height: 60px;
        }

        .barcode {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            background-image: linear-gradient(90deg,
                    black 0%, black 3%,
                    white 3%, white 5%,
                    black 5%, black 8%,
                    white 8%, white 10%,
                    black 10%, black 15%,
                    white 15%, white 17%,
                    black 17%, black 19%,
                    white 19%, white 21%,
                    black 21%, black 24%,
                    white 24%, white 25%,
                    black 25%, black 28%,
                    white 28%, white 32%,
                    black 32%, black 34%,
                    white 34%, white 36%,
                    black 36%, black 40%,
                    white 40%, white 41%,
                    black 41%, black 45%,
                    white 45%, white 47%,
                    black 47%, black 49%,
                    white 49%, white 55%,
                    black 55%, black 58%,
                    white 58%, white 61%,
                    black 61%, black 64%,
                    white 64%, white 67%,
                    black 67%, black 70%,
                    white 70%, white 73%,
                    black 73%, black 76%,
                    white 76%, white 79%,
                    black 79%, black 83%,
                    white 83%, white 86%,
                    black 86%, black 91%,
                    white 91%, white 93%,
                    black 93%, black 96%,
                    white 96%, white 98%,
                    black 98%, black 100%);
        }

        .security-strip {
            height: 10px;
            background: repeating-linear-gradient(45deg, var(--primary-color), var(--primary-color) 10px, var(--primary-dark) 10px, var(--primary-dark) 20px);
            opacity: 0.7;
        }

        .qr-code {
            background: white;
            border: 1px solid var(--border-color);
            padding: 5px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .ticket-detail-box {
            background-color: white;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .ticket-detail-box:hover {
            border-color: var(--primary-light);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 100;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .floating-action:hover {
            transform: scale(1.05);
        }

        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }

            body {
                background-color: white;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            .ticket-container:hover {
                transform: none;
                box-shadow: none;
            }

            .print-shadow-none {
                box-shadow: none !important;
            }

            .ticket-detail-box:hover {
                box-shadow: none;
            }

            .floating-action {
                display: none;
            }
        }

        /* Animations */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Admin Control Panel - Only visible on screen -->
    <div
        class="sticky top-0 z-50 bg-white bg-opacity-90 backdrop-filter backdrop-blur-lg mb-8 py-3 px-6 shadow-md flex justify-between items-center no-print">
        <div class="flex items-center space-x-3">
            <div class="bg-blue-100 rounded-lg p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-lg md:text-xl font-bold text-gray-800">Cetak Tiket Perjalanan</h1>
                <p class="text-sm text-gray-500">No. Booking: {{ $booking->booking_number }}</p>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.bookings.tickets', $booking->id) }}"
                class="flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors duration-200 border border-gray-200">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="hidden sm:inline">Kembali</span>
            </a>
            <button onclick="window.print()"
                class="flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200 shadow-sm hover:shadow">
                <i class="fas fa-print mr-2"></i>
                <span class="hidden sm:inline">Cetak Tiket</span>
            </button>
        </div>
    </div>

    <!-- Floating Action Button for Print (Mobile) -->
    <div class="floating-action no-print md:hidden">
        <button onclick="window.print()"
            class="flex items-center justify-center h-14 w-14 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-print text-xl"></i>
        </button>
    </div>

    <div class="max-w-5xl mx-auto p-4 md:p-6 lg:p-8 relative">
        <!-- Company Header (will appear on print) -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-8 border-b border-gray-200 pb-6">
            <div class="flex items-center mb-4 md:mb-0">
                <div
                    class="bg-blue-600 text-white h-14 w-14 rounded-lg flex items-center justify-center shadow-md mr-4">
                    <i class="fas fa-ship text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-blue-900">Ferry Ticket System</h1>
                    <p class="text-blue-600">E-Ticket untuk Perjalanan Ferry</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Tanggal Booking:</p>
                <p class="font-semibold">{{ $booking->created_at ? $booking->created_at->format('d M Y, H:i') : 'N/A' }}
                </p>
                @if ($booking->status == 'CONFIRMED' || $booking->status == 'COMPLETED')
                    <div
                        class="inline-block mt-2 bg-green-100 text-green-800 px-3 py-1 rounded-full font-medium text-xs">
                        <i class="fas fa-check-circle mr-1"></i> CONFIRMED
                    </div>
                @endif
            </div>
        </div>

        <!-- Booking Overview Card -->
        <div class="bg-white rounded-lg shadow-md mb-8 overflow-hidden print-shadow-none">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i> Informasi Perjalanan
                </h2>

                <div class="bg-blue-50 rounded-lg p-4 mb-4">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                        <div class="flex items-center mb-3 md:mb-0">
                            <div
                                class="bg-white shadow-sm rounded-full w-10 h-10 flex items-center justify-center mr-3 text-blue-600">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div>
                                <p class="text-sm text-blue-700 font-semibold">Rute Perjalanan</p>
                                <p class="text-lg font-bold">{{ $booking->schedule->route->origin ?? 'N/A' }} <i
                                        class="fas fa-long-arrow-alt-right text-blue-400 mx-2"></i>
                                    {{ $booking->schedule->route->destination ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div
                                class="bg-white shadow-sm rounded-full w-10 h-10 flex items-center justify-center mr-3 text-blue-600">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <p class="text-sm text-blue-700 font-semibold">Tanggal Keberangkatan</p>
                                <p class="text-lg font-bold">
                                    {{ $booking->travel_date ? $booking->travel_date->format('d F Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="ticket-detail-box rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-1">Kapal</p>
                        <p class="font-semibold flex items-center text-gray-800">
                            <i class="fas fa-ship text-blue-500 mr-2"></i>
                            {{ $booking->schedule->ferry->name ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="ticket-detail-box rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-1">Jam Keberangkatan</p>
                        <p class="font-semibold flex items-center text-gray-800">
                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                            {{ $booking->schedule->departure_time ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="ticket-detail-box rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-1">Status Booking</p>
                        <div>
                            @if ($booking->status == 'PENDING')
                                <span class="status-badge bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    <i class="fas fa-clock mr-1"></i> pending
                                </span>
                            @elseif($booking->status == 'CONFIRMED')
                                <span class="status-badge bg-blue-100 text-blue-800 border border-blue-200">
                                    <i class="fas fa-check-circle mr-1"></i> confirmed
                                </span>
                            @elseif($booking->status == 'COMPLETED')
                                <span class="status-badge bg-green-100 text-green-800 border border-green-200">
                                    <i class="fas fa-check-double mr-1"></i> completed
                                </span>
                            @elseif($booking->status == 'CANCELLED')
                                <span class="status-badge bg-red-100 text-red-800 border border-red-200">
                                    <i class="fas fa-times-circle mr-1"></i> cancelled
                                </span>
                            @else
                                <span class="status-badge bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $booking->status }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Penumpang & Kontak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex">
                        <div
                            class="bg-blue-100 rounded-full w-10 h-10 flex items-center justify-center mr-3 text-blue-600">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pemesan</p>
                            <p class="font-bold text-lg">{{ $booking->user->name ?? 'N/A' }}</p>
                            <div class="text-gray-600 text-sm">
                                <p class="mb-1"><i class="fas fa-envelope text-blue-500 mr-1"></i>
                                    {{ $booking->user->email ?? 'N/A' }}</p>
                                <p><i class="fas fa-phone text-blue-500 mr-1"></i>
                                    {{ $booking->user->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex">
                        <div
                            class="bg-blue-100 rounded-full w-10 h-10 flex items-center justify-center mr-3 text-blue-600">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Detail Tiket</p>
                            <p class="font-bold text-lg">{{ count($booking->tickets) }} Tiket</p>
                            <div class="text-gray-600 text-sm">
                                <p><i class="fas fa-users text-blue-500 mr-1"></i>
                                    {{ $booking->passenger_count ?? '0' }} Penumpang</p>
                                @if ($booking->vehicle_count)
                                    <p><i class="fas fa-car text-blue-500 mr-1"></i> {{ $booking->vehicle_count }}
                                        Kendaraan</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets -->
        @foreach ($booking->tickets as $ticket)
            <div
                class="ticket-container bg-white rounded-lg shadow-md overflow-hidden mb-8 print-shadow-none ticket-shape {{ !$loop->last ? 'page-break' : '' }}">
                <div class="relative watermark">
                    <!-- Serial Number -->
                    <div class="ticket-serial">SERIAL:
                        {{ substr($ticket->ticket_number, 0, 4) }}-{{ substr($ticket->ticket_number, 4, 4) }}-{{ substr($ticket->ticket_number, 8, 4) }}
                    </div>

                    <!-- Security Strip -->
                    <div class="security-strip"></div>

                    <!-- Ticket Header -->
                    <div class="p-6">
                        <div class="flex justify-between">
                            <div>
                                <div class="flex items-center mb-2">
                                    <div
                                        class="bg-blue-600 text-white rounded-lg w-10 h-10 flex items-center justify-center mr-3">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-blue-900">Tiket Ferry</h2>
                                        <p class="text-sm text-blue-600">#{{ $loop->iteration }} dari
                                            {{ count($booking->tickets) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                @if ($ticket->status == 'ACTIVE')
                                    <span class="status-badge bg-green-100 text-green-800 border border-green-200">
                                        <i class="fas fa-check-circle mr-1"></i> aktif
                                    </span>
                                @elseif($ticket->status == 'USED')
                                    <span class="status-badge bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-check-double mr-1"></i> terpakai
                                    </span>
                                @elseif($ticket->status == 'CANCELLED')
                                    <span class="status-badge bg-red-100 text-red-800 border border-red-200">
                                        <i class="fas fa-times-circle mr-1"></i> dibatalkan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Passenger & Journey Info -->
                    <div class="px-6 pb-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                            <!-- Col 1-3: Passenger Details -->
                            <div class="md:col-span-3">
                                @if ($ticket->passenger)
                                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                                        <h3
                                            class="font-semibold text-blue-900 mb-3 flex items-center text-sm uppercase tracking-wide">
                                            <i class="fas fa-user-circle mr-2"></i> Detail Penumpang
                                        </h3>

                                        <div class="flex items-start mb-4">
                                            <div
                                                class="bg-white shadow-sm rounded-full w-10 h-10 flex items-center justify-center mr-3 text-blue-500 mt-1">
                                                @if ($ticket->passenger->type == 'ADULT')
                                                    <i class="fas fa-user"></i>
                                                @elseif($ticket->passenger->type == 'CHILD')
                                                    <i class="fas fa-child"></i>
                                                @elseif($ticket->passenger->type == 'INFANT')
                                                    <i class="fas fa-baby"></i>
                                                @else
                                                    <i class="fas fa-user"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 text-lg">
                                                    {{ $ticket->passenger->name }}</p>
                                                <div class="flex flex-wrap text-sm text-gray-600 mt-1">
                                                    <span class="mr-3">
                                                        <i class="fas fa-id-card text-blue-500 mr-1"></i>
                                                        {{ $ticket->passenger->id_type }}:
                                                        {{ $ticket->passenger->id_number }}
                                                    </span>
                                                    <span>
                                                        <i class="fas fa-user-tag text-blue-500 mr-1"></i>
                                                        @if ($ticket->passenger->type == 'ADULT')
                                                            Dewasa
                                                        @elseif($ticket->passenger->type == 'CHILD')
                                                            Anak
                                                        @elseif($ticket->passenger->type == 'INFANT')
                                                            Bayi
                                                        @else
                                                            {{ $ticket->passenger->type }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Route Information -->
                                <div class="route-path bg-white border border-gray-200 rounded-lg p-4">
                                    <h3
                                        class="font-semibold text-blue-900 mb-3 flex items-center text-sm uppercase tracking-wide">
                                        <i class="fas fa-route mr-2"></i> Informasi Rute
                                    </h3>

                                    <div class="flex flex-col space-y-10 ml-2">
                                        <div class="start relative">
                                            <div class="ml-4">
                                                <p class="font-bold text-gray-800">
                                                    {{ $booking->schedule->route->origin ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $booking->travel_date ? $booking->travel_date->format('d M Y') : 'N/A' }}
                                                    | {{ $booking->schedule->departure_time ?? 'N/A' }}</p>
                                            </div>
                                        </div>

                                        <div class="end relative">
                                            <div class="ml-4">
                                                <p class="font-bold text-gray-800">
                                                    {{ $booking->schedule->route->destination ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-600">Tujuan</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Col 4-5: QR Code and Boarding Info -->
                            <div class="md:col-span-2 flex flex-col">
                                <div class="flex justify-center mb-4">
                                    <div class="qr-code w-32 h-32 rounded p-1">
                                        <div class="bg-white w-full h-full flex items-center justify-center">
                                            <div class="text-center">
                                                <i class="fas fa-qrcode text-5xl text-gray-800"></i>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ substr($ticket->ticket_number, -8) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-blue-50 rounded-lg p-4 mt-auto">
                                    <h3
                                        class="font-semibold text-blue-900 mb-2 flex items-center text-sm uppercase tracking-wide">
                                        <i class="fas fa-ship mr-2"></i> Detail Kapal
                                    </h3>

                                    <div class="mb-3">
                                        <p class="text-sm text-gray-600">Nama Kapal</p>
                                        <p class="font-bold text-gray-800">
                                            {{ $booking->schedule->ferry->name ?? 'N/A' }}</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-sm text-gray-600">Tanggal</p>
                                            <p class="font-medium text-gray-800">
                                                {{ $booking->travel_date ? $booking->travel_date->format('d M Y') : 'N/A' }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Keberangkatan</p>
                                            <p class="font-medium text-gray-800">
                                                {{ $booking->schedule->departure_time ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Information (if exists) -->
                    @if ($ticket->vehicle)
                        <div class="px-6 pb-6">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h3
                                    class="font-semibold text-gray-800 mb-3 flex items-center text-sm uppercase tracking-wide">
                                    <i class="fas fa-car mr-2"></i> Detail Kendaraan
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <div class="md:col-span-2">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 rounded-lg p-3 mr-3 text-blue-600">
                                                <i class="fas fa-car"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Tipe Kendaraan</p>
                                                <p class="font-semibold text-gray-800">
                                                    {{ $ticket->vehicle->vehicle_type->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">Plat Nomor</p>
                                        <p class="font-semibold text-gray-800 flex items-center">
                                            <i class="fas fa-digital-tachograph text-blue-500 mr-2"></i>
                                            {{ $ticket->vehicle->license_plate }}
                                        </p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-sm text-gray-600 mb-1">Merk/Model Kendaraan</p>
                                        <p class="font-semibold text-gray-800 flex items-center">
                                            <i class="fas fa-car-side text-blue-500 mr-2"></i>
                                            {{ $ticket->vehicle->brand_model }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Barcode -->
                    <div class="px-6 pb-6">
                        <div class="barcode-container rounded overflow-hidden">
                            <div class="barcode"></div>
                        </div>
                        <div class="text-center text-xs text-gray-500 mt-1">{{ $ticket->ticket_number }}</div>
                    </div>
                </div>

                <!-- Ticket Tear Line -->
                <div class="ticket-divider"></div>

                <!-- Boarding Pass Section -->
                <div class="boarding-pass p-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <div class="flex items-center mb-1">
                                <div
                                    class="bg-blue-600 text-white rounded-lg w-8 h-8 flex items-center justify-center mr-2 text-xs">
                                    BP</div>
                                <h3 class="font-bold text-blue-900 tracking-wide">BOARDING PASS</h3>
                            </div>
                            <p class="text-sm flex items-center font-medium text-gray-800 mb-1">
                                <i class="fas fa-map-marker-alt text-blue-500 mr-1"></i>
                                {{ $booking->schedule->route->origin ?? 'N/A' }} →
                                {{ $booking->schedule->route->destination ?? 'N/A' }}
                            </p>
                            <p class="text-sm flex items-center text-gray-600">
                                <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
                                {{ $booking->travel_date ? $booking->travel_date->format('d/m/Y') : 'N/A' }} |
                                <i class="fas fa-clock text-blue-500 mx-1"></i>
                                {{ $booking->schedule->departure_time ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:text-right flex flex-col items-end">
                            <div class="font-semibold text-gray-800">{{ $ticket->passenger->name ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-600">
                                <span>Tiket: {{ substr($ticket->ticket_number, -8) }}</span>
                            </div>
                            @if ($ticket->passenger)
                                <div
                                    class="text-xs mt-1 inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                    @if ($ticket->passenger->type == 'ADULT')
                                        <i class="fas fa-user mr-1"></i> Dewasa
                                    @elseif($ticket->passenger->type == 'CHILD')
                                        <i class="fas fa-child mr-1"></i> Anak
                                    @elseif($ticket->passenger->type == 'INFANT')
                                        <i class="fas fa-baby mr-1"></i> Bayi
                                    @else
                                        <i class="fas fa-user mr-1"></i> {{ $ticket->passenger->type }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Terms & Conditions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8 print-shadow-none">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-exclamation-circle text-yellow-500 mr-2"></i> Syarat & Ketentuan
            </h3>
            <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                <li>Harap tiba setidaknya <strong>60 menit</strong> sebelum waktu keberangkatan untuk proses check-in.
                </li>
                <li>E-tiket ini <strong>wajib ditunjukkan</strong> bersama dengan ID yang valid saat check-in.</li>
                <li>Untuk tiket kendaraan, harap siapkan dokumen registrasi kendaraan dan SIM yang masih berlaku.</li>
                <li>Kebijakan pembatalan: Pembatalan 24 jam sebelum keberangkatan akan dikenakan biaya 25% dari total
                    tiket.</li>
                <li>Check-in akan ditutup 30 menit sebelum waktu keberangkatan.</li>
                <li>Perusahaan berhak mengubah jadwal keberangkatan karena kondisi cuaca atau alasan operasional
                    lainnya.</li>
                <li>Penumpang bertanggung jawab atas barang bawaan pribadi masing-masing.</li>
                <li>Untuk bantuan atau informasi lebih lanjut, silakan hubungi pusat layanan kami di 0800-1234-5678.
                </li>
            </ol>
        </div>

        <!-- Print Footer - Only visible on print -->
        <div class="text-center text-gray-500 text-sm mt-8 print:mt-4 border-t border-gray-200 pt-4">
            <p>Ini adalah e-tiket resmi. Dicetak pada {{ now()->format('d/m/Y H:i:s') }}</p>
            <p class="mt-1"><strong>Ferry Ticket System</strong> | support@ferryticketsystem.com | 0800-1234-5678</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to status badges
            document.querySelectorAll('.status-badge').forEach(badge => {
                badge.classList.add('pulse-animation');
            });
        });
    </script>
</body>

</html>
