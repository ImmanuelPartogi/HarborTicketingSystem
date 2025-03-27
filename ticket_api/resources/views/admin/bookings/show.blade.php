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
                    Detail Pemesanan
                </h1>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.bookings.tickets', $booking->id) }}"
                        class="text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                        <i class="fas fa-ticket-alt mr-1.5"></i> Lihat Tiket
                    </a>
                    <a href="{{ route('admin.bookings.index') }}"
                        class="text-gray-600 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                        <i class="fas fa-arrow-left mr-1.5"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm animate__animated animate__fadeIn"
                role="alert">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-check text-green-500"></i>
                    </div>
                    <div>
                        <p class="font-medium">Berhasil!</p>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm animate__animated animate__fadeIn"
                role="alert">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <div>
                        <p class="font-medium">Error!</p>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Booking Summary Card -->
        <div class="bg-indigo-50 rounded-xl p-5 mb-6 border border-indigo-100 shadow-sm">
            <div class="flex flex-col md:flex-row justify-between mb-4">
                <div class="mb-4 md:mb-0">
                    <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-info-circle text-indigo-600 mr-2"></i> Informasi Pemesanan
                    </h2>
                    <div class="flex items-center gap-4">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-lg font-medium text-sm">
                            No. Booking: {{ $booking->booking_number }}
                        </span>
                        @if ($booking->status == 'PENDING')
                            <span
                                class="px-3 py-1 rounded-lg text-sm font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 flex items-center shadow-sm">
                                <i class="fas fa-clock mr-1.5"></i> Menunggu
                            </span>
                        @elseif($booking->status == 'CONFIRMED')
                            <span
                                class="px-3 py-1 rounded-lg text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200 flex items-center shadow-sm">
                                <i class="fas fa-check mr-1.5"></i> Terkonfirmasi
                            </span>
                        @elseif($booking->status == 'COMPLETED')
                            <span
                                class="px-3 py-1 rounded-lg text-sm font-medium bg-green-100 text-green-800 border border-green-200 flex items-center shadow-sm">
                                <i class="fas fa-check-double mr-1.5"></i> Selesai
                            </span>
                        @elseif($booking->status == 'CANCELLED')
                            <span
                                class="px-3 py-1 rounded-lg text-sm font-medium bg-red-100 text-red-800 border border-red-200 flex items-center shadow-sm">
                                <i class="fas fa-times mr-1.5"></i> Dibatalkan
                            </span>
                        @elseif($booking->status == 'REFUNDED')
                            <span
                                class="px-3 py-1 rounded-lg text-sm font-medium bg-purple-100 text-purple-800 border border-purple-200 flex items-center shadow-sm">
                                <i class="fas fa-undo mr-1.5"></i> Dikembalikan
                            </span>
                        @elseif($booking->status == 'RESCHEDULED')
                            <span
                                class="px-3 py-1 rounded-lg text-sm font-medium bg-teal-100 text-teal-800 border border-teal-200 flex items-center shadow-sm">
                                <i class="fas fa-calendar-alt mr-1.5"></i> Dijadwalkan Ulang
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($booking->status == 'PENDING')
                        <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                                <i class="fas fa-check-circle mr-1.5"></i> Konfirmasi
                            </button>
                        </form>
                        <button type="button" onclick="document.getElementById('cancelModal').classList.remove('hidden')"
                            class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                            <i class="fas fa-times-circle mr-1.5"></i> Batalkan
                        </button>
                    @elseif($booking->status == 'CONFIRMED' || $booking->status == 'RESCHEDULED')
                        <form action="{{ route('admin.bookings.complete', $booking->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                                <i class="fas fa-check-double mr-1.5"></i> Selesaikan
                            </button>
                        </form>
                        <button type="button"
                            onclick="document.getElementById('rescheduleModal').classList.remove('hidden')"
                            class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                            <i class="fas fa-calendar-alt mr-1.5"></i> Reschedule
                        </button>
                        <button type="button" onclick="document.getElementById('cancelModal').classList.remove('hidden')"
                            class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                            <i class="fas fa-times-circle mr-1.5"></i> Batalkan
                        </button>
                    @elseif($booking->status == 'CANCELLED')
                        <button type="button" onclick="document.getElementById('refundModal').classList.remove('hidden')"
                            class="text-white bg-purple-600 hover:bg-purple-700 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                            <i class="fas fa-undo mr-1.5"></i> Proses Refund
                        </button>
                    @endif
                    <a href="{{ route('admin.bookings.printTickets', $booking->id) }}" target="_blank"
                        class="text-gray-800 bg-yellow-400 hover:bg-yellow-500 font-medium rounded-lg text-sm px-4 py-2 flex items-center transition-all duration-300 shadow-sm">
                        <i class="fas fa-print mr-1.5"></i> Print Tiket
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div class="bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Pengguna</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user text-blue-600 mr-1.5"></i>
                        {{ $booking->user->name ?? 'N/A' }}
                    </p>
                    <p class="text-gray-600 text-xs mt-1">
                        <i class="fas fa-envelope text-gray-400 mr-1.5"></i>
                        {{ $booking->user->email ?? 'N/A' }}
                    </p>
                    @if ($booking->user->phone)
                        <p class="text-gray-600 text-xs mt-1">
                            <i class="fas fa-phone text-gray-400 mr-1.5"></i>
                            {{ $booking->user->phone }}
                        </p>
                    @endif
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Rute</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-route text-purple-600 mr-1.5"></i>
                        {{ $booking->schedule && $booking->schedule->route ? $booking->schedule->route->origin : 'N/A' }}
                        <i class="fas fa-long-arrow-alt-right mx-1.5 text-gray-400"></i>
                        {{ $booking->schedule->route->destination ?? 'N/A' }}
                    </p>
                    <p class="text-gray-600 text-xs mt-1">
                        <i class="fas fa-ship text-gray-400 mr-1.5"></i>
                        {{ $booking->schedule && $booking->schedule->ferry ? $booking->schedule->ferry->name : 'N/A' }}
                    </p>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Jadwal</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="far fa-calendar-alt text-green-600 mr-1.5"></i>
                        {{ $booking->travel_date ? $booking->travel_date->format('d/m/Y') : 'N/A' }}
                    </p>
                    <p class="text-gray-600 text-xs mt-1">
                        <i class="far fa-clock text-gray-400 mr-1.5"></i>
                        {{ $booking->schedule->departure_time ?? 'N/A' }}
                    </p>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Pembayaran</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-money-bill-wave text-green-600 mr-1.5"></i>
                        Rp {{ number_format($booking->total_amount ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-gray-600 text-xs mt-1">
                        <i class="fas fa-users text-gray-400 mr-1.5"></i>
                        {{ $booking->passenger_count ?? 0 }} Penumpang
                    </p>
                    <p class="text-gray-600 text-xs mt-1">
                        <i class="fas fa-car text-gray-400 mr-1.5"></i>
                        {{ $booking->vehicles->count() ?? 0 }} Kendaraan
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px" id="tabMenu" role="tablist">
                    <li class="mr-2" role="presentation">
                        <button
                            class="inline-block p-4 border-b-2 border-indigo-600 text-indigo-600 font-medium text-sm rounded-t-lg"
                            id="passengers-tab" data-tabs-target="#passengers" type="button" role="tab"
                            aria-controls="passengers" aria-selected="true">
                            <i class="fas fa-users mr-1.5"></i> Penumpang
                        </button>
                    </li>
                    @if ($booking->vehicles->count() > 0)
                        <li class="mr-2" role="presentation">
                            <button
                                class="inline-block p-4 border-b-2 border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600 font-medium text-sm rounded-t-lg"
                                id="vehicles-tab" data-tabs-target="#vehicles" type="button" role="tab"
                                aria-controls="vehicles" aria-selected="false">
                                <i class="fas fa-car mr-1.5"></i> Kendaraan
                            </button>
                        </li>
                    @endif
                    <li class="mr-2" role="presentation">
                        <button
                            class="inline-block p-4 border-b-2 border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600 font-medium text-sm rounded-t-lg"
                            id="payments-tab" data-tabs-target="#payments" type="button" role="tab"
                            aria-controls="payments" aria-selected="false">
                            <i class="fas fa-money-bill-wave mr-1.5"></i> Pembayaran
                        </button>
                    </li>
                    <li class="mr-2" role="presentation">
                        <button
                            class="inline-block p-4 border-b-2 border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600 font-medium text-sm rounded-t-lg"
                            id="history-tab" data-tabs-target="#history" type="button" role="tab"
                            aria-controls="history" aria-selected="false">
                            <i class="fas fa-history mr-1.5"></i> Riwayat
                        </button>
                    </li>
                </ul>
            </div>

            <div id="tabContent">
                <!-- Passengers Tab -->
                <div class="block p-4 bg-white rounded-lg" id="passengers" role="tabpanel"
                    aria-labelledby="passengers-tab">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No.</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jenis Identitas</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No. Identitas</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jenis Penumpang</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($booking->passengers as $index => $passenger)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $passenger->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $passenger->id_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $passenger->id_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if ($passenger->type == 'ADULT')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Dewasa</span>
                                            @elseif($passenger->type == 'CHILD')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Anak</span>
                                            @elseif($passenger->type == 'INFANT')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Bayi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Tidak ada
                                            data penumpang</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Vehicles Tab -->
                @if ($booking->vehicles->count() > 0)
                    <div class="hidden p-4 bg-white rounded-lg" id="vehicles" role="tabpanel"
                        aria-labelledby="vehicles-tab">
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No.</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jenis Kendaraan</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No. Plat</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Merk/Model</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Harga</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($booking->vehicles as $index => $vehicle)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $vehicle->vehicle_type ? $vehicle->vehicle_type->name : 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $vehicle->license_plate }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $vehicle->brand_model }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                Rp {{ number_format($vehicle->price, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Payments Tab -->
                <div class="hidden p-4 bg-white rounded-lg" id="payments" role="tabpanel"
                    aria-labelledby="payments-tab">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No.</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID Pembayaran</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Metode</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jumlah</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($booking->payments as $index => $payment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $payment->payment_id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $payment->payment_method }}
                                            @if ($payment->payment_channel)
                                                ({{ $payment->payment_channel }})
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($payment->status == 'SUCCESS')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Berhasil</span>
                                            @elseif($payment->status == 'PENDING')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                            @elseif($payment->status == 'FAILED')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Gagal</span>
                                            @elseif($payment->status == 'REFUNDED')
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Refund</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Tidak ada
                                            data pembayaran</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- History Tab -->
                <div class="hidden p-4 bg-white rounded-lg" id="history" role="tabpanel"
                    aria-labelledby="history-tab">
                    <div class="space-y-4">
                        @forelse($booking->logs as $index => $log)
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-600">
                                        @if (strpos($log->notes, 'confirmed') !== false)
                                            <i class="fas fa-check"></i>
                                        @elseif(strpos($log->notes, 'completed') !== false)
                                            <i class="fas fa-check-double"></i>
                                        @elseif(strpos($log->notes, 'cancelled') !== false)
                                            <i class="fas fa-times"></i>
                                        @elseif(strpos($log->notes, 'rescheduled') !== false)
                                            <i class="fas fa-calendar-alt"></i>
                                        @elseif(strpos($log->notes, 'refunded') !== false)
                                            <i class="fas fa-undo"></i>
                                        @else
                                            <i class="fas fa-history"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="font-medium text-gray-900">
                                                Status Diubah: {{ $log->previous_status }} → {{ $log->new_status }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-600">{{ $log->notes }}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            @if ($log->changed_by_type == 'ADMIN')
                                                <span class="text-indigo-600">Diubah oleh Admin</span>
                                            @elseif($log->changed_by_type == 'USER')
                                                <span class="text-green-600">Diubah oleh Pengguna</span>
                                            @elseif($log->changed_by_type == 'SYSTEM')
                                                <span class="text-orange-600">Diubah oleh Sistem</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-4 italic">
                                Tidak ada riwayat perubahan status pemesanan
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Batalkan Pemesanan</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin membatalkan pemesanan ini?
                                        Tindakan ini tidak dapat dibatalkan.</p>
                                    <div class="mt-4">
                                        <label for="reason" class="block text-sm font-medium text-gray-700">Alasan
                                            Pembatalan <span class="text-red-500">*</span></label>
                                        <textarea id="reason" name="reason" rows="3"
                                            class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Batalkan Pemesanan
                        </button>
                        <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div id="rescheduleModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.bookings.reschedule', $booking->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-calendar-alt text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Jadwalkan Ulang Pemesanan</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Pilih jadwal dan tanggal baru untuk pemesanan ini.</p>
                                    <div class="mt-4">
                                        <label for="schedule_id" class="block text-sm font-medium text-gray-700">Jadwal
                                            Baru <span class="text-red-500">*</span></label>
                                        <select id="schedule_id" name="schedule_id"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                            required>
                                            <option value="">Pilih Jadwal</option>
                                            @foreach ($schedules as $schedule)
                                                <option value="{{ $schedule->id }}">
                                                    {{ $schedule->departure_time ?? 'N/A' }} -
                                                    {{ $schedule->route ? $schedule->route->origin : 'N/A' }} ke
                                                    {{ $schedule->route ? $schedule->route->destination : 'N/A' }}
                                                    ({{ $schedule->ferry ? $schedule->ferry->name : 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-4">
                                        <label for="booking_date" class="block text-sm font-medium text-gray-700">Tanggal
                                            Baru <span class="text-red-500">*</span></label>
                                        <input type="date" id="booking_date" name="booking_date"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            required min="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Jadwalkan Ulang
                        </button>
                        <button type="button"
                            onclick="document.getElementById('rescheduleModal').classList.add('hidden')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div id="refundModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.bookings.refund', $booking->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-undo text-purple-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Proses Refund</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Tentukan jumlah refund untuk pemesanan ini.</p>
                                    <div class="mt-4">
                                        <label for="amount" class="block text-sm font-medium text-gray-700">Jumlah
                                            Refund <span class="text-red-500">*</span></label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input type="number" id="amount" name="amount"
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md"
                                                placeholder="0" value="{{ $booking->total_amount }}" required
                                                min="0" max="{{ $booking->total_amount }}">
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label for="reason" class="block text-sm font-medium text-gray-700">Alasan
                                            Refund <span class="text-red-500">*</span></label>
                                        <textarea id="reason" name="reason" rows="3"
                                            class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Proses Refund
                        </button>
                        <button type="button" onclick="document.getElementById('refundModal').classList.add('hidden')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Tab functionality
                const tabButtons = document.querySelectorAll('[data-tabs-target]');
                const tabContents = document.querySelectorAll('[role="tabpanel"]');

                tabButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const target = document.querySelector(button.dataset.tabsTarget);

                        tabContents.forEach(tc => {
                            tc.classList.add('hidden');
                        });

                        tabButtons.forEach(tb => {
                            tb.classList.remove('border-indigo-600', 'text-indigo-600');
                            tb.classList.add('border-transparent', 'text-gray-500',
                                'hover:border-gray-300', 'hover:text-gray-600');
                        });

                        button.classList.add('border-indigo-600', 'text-indigo-600');
                        button.classList.remove('border-transparent', 'text-gray-500',
                            'hover:border-gray-300', 'hover:text-gray-600');

                        target.classList.remove('hidden');
                    });
                });
            });
        </script>
    @endpush
@endsection
