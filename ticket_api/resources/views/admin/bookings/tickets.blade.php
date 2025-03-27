@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow-lg rounded-xl p-5 sm:p-7 transition-all duration-300 hover:shadow-xl">
        <!-- Header Section with Gradient Background -->
        <div
            class="bg-gradient-to-r from-indigo-50 to-purple-50 -m-5 sm:-m-7 mb-6 p-5 sm:p-7 rounded-t-xl border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                <h1 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-0 text-gray-800 flex items-center">
                    <span class="bg-indigo-600 text-white p-2 rounded-lg shadow-md mr-3">
                        <i class="fas fa-ticket-alt"></i>
                    </span>
                    Tiket Pemesanan
                </h1>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.bookings.printTickets', $booking->id) }}" target="_blank"
                        class="text-white bg-yellow-500 hover:bg-yellow-600 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                        <i class="fas fa-print mr-1.5"></i> Print Tiket
                    </a>
                    <a href="{{ route('admin.bookings.show', $booking->id) }}"
                        class="text-gray-600 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                        <i class="fas fa-arrow-left mr-1.5"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Booking Information -->
        <div class="mb-6 bg-gray-50 rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">No. Booking</p>
                    <p class="font-medium text-indigo-600 flex items-center">
                        <i class="fas fa-ticket-alt text-indigo-500 mr-1.5"></i>
                        {{ $booking->booking_number }}
                    </p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Status</p>
                    <p class="font-medium">
                        @if ($booking->status == 'PENDING')
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 flex items-center w-fit shadow-sm">
                                <i class="fas fa-clock mr-1.5"></i> Menunggu
                            </span>
                        @elseif($booking->status == 'CONFIRMED')
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200 flex items-center w-fit shadow-sm">
                                <i class="fas fa-check mr-1.5"></i> Terkonfirmasi
                            </span>
                        @elseif($booking->status == 'COMPLETED')
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200 flex items-center w-fit shadow-sm">
                                <i class="fas fa-check-double mr-1.5"></i> Selesai
                            </span>
                        @elseif($booking->status == 'CANCELLED')
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200 flex items-center w-fit shadow-sm">
                                <i class="fas fa-times mr-1.5"></i> Dibatalkan
                            </span>
                        @elseif($booking->status == 'RESCHEDULED')
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800 border border-teal-200 flex items-center w-fit shadow-sm">
                                <i class="fas fa-calendar-alt mr-1.5"></i> Dijadwalkan Ulang
                            </span>
                        @endif
                    </p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Tanggal Perjalanan</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="far fa-calendar-alt text-green-500 mr-1.5"></i>
                        {{ $booking->travel_date ? $booking->travel_date->format('d/m/Y') : 'N/A' }}
                    </p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Jumlah Tiket</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-users text-blue-500 mr-1.5"></i>
                        {{ $booking->tickets->count() }} Tiket
                    </p>
                </div>
            </div>
        </div>

        <!-- Tickets -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
            @forelse($booking->tickets as $ticket)
                <div
                    class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                    <!-- Ticket Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold">Tiket {{ $loop->iteration }}</h3>
                                <p class="text-xs text-indigo-100">{{ $ticket->ticket_number }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-indigo-100">Status</div>
                                <div class="text-sm font-medium">
                                    @if ($ticket->status == 'ACTIVE')
                                        <span class="px-2 py-1 bg-green-500 text-white text-xs rounded-full">Aktif</span>
                                    @elseif($ticket->status == 'USED')
                                        <span class="px-2 py-1 bg-blue-500 text-white text-xs rounded-full">Terpakai</span>
                                    @elseif($ticket->status == 'CANCELLED')
                                        <span class="px-2 py-1 bg-red-500 text-white text-xs rounded-full">Dibatalkan</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Content -->
                    <div class="p-4">
                        <!-- Travel Info -->
                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-2">
                                        <i class="fas fa-route"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Rute</div>
                                        <div class="font-medium text-gray-800">
                                            {{ $booking->schedule->route->origin ?? 'N/A' }} →
                                            {{ $booking->schedule->route->destination ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Kapal</div>
                                    <div class="font-medium text-gray-800">{{ $booking->schedule->ferry->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between mt-3">
                                <div>
                                    <div class="text-xs text-gray-500">Tanggal</div>
                                    <div class="font-medium text-gray-800">
                                        {{ $booking->travel_date ? $booking->travel_date->format('d/m/Y') : 'N/A' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Waktu Keberangkatan</div>
                                    <div class="font-medium text-gray-800">
                                        {{ $booking->schedule->departure_time ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Passenger Info -->
                        @if ($ticket->passenger)
                            <div class="mb-4 pb-4 border-b border-gray-100">
                                <div class="text-xs text-gray-500 mb-2">Penumpang</div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-800">{{ $ticket->passenger->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $ticket->passenger->id_type }}: {{ $ticket->passenger->id_number }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Jenis</div>
                                        <div>
                                            @if ($ticket->passenger->type == 'ADULT')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Dewasa</span>
                                            @elseif($ticket->passenger->type == 'CHILD')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Anak</span>
                                            @elseif($ticket->passenger->type == 'INFANT')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Bayi</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Vehicle Info (if exists) -->
                        @if ($ticket->vehicle)
                            <div class="mb-4 pb-4 border-b border-gray-100">
                                <div class="text-xs text-gray-500 mb-2">Kendaraan</div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-800">{{ $ticket->vehicle->brand_model }}</div>
                                        <div class="text-xs text-gray-500">
                                            Plat Nomor: {{ $ticket->vehicle->license_plate }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Jenis</div>
                                        <div class="font-medium text-gray-800">
                                            {{ $ticket->vehicle->vehicle_type->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Ticket Footer -->
                        <div class="mt-4 flex justify-between items-center">
                            <div class="bg-indigo-50 px-3 py-1.5 rounded-lg text-xs text-indigo-600 font-medium">
                                <i class="fas fa-qrcode mr-1"></i> {{ substr($ticket->ticket_number, -8) }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Dipesan pada:
                                {{ $booking->created_at ? $booking->created_at->format('d/m/Y H:i') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-gray-50 p-8 rounded-xl text-center border border-gray-200">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-ticket-alt text-3xl text-gray-300"></i>
                        </div>
                        <p class="text-gray-600 font-medium">Tidak ada tiket yang tersedia</p>
                        <p class="text-gray-400 text-sm mt-1">Tiket akan muncul setelah pemesanan dikonfirmasi</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
