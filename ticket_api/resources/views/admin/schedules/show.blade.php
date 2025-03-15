@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Detail Jadwal</h1>
            <div>
                <a href="{{ route('admin.schedules.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
                <a href="{{ route('admin.schedules.edit', $schedule) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded mr-2">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('admin.schedules.dates', $schedule) }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                    <i class="fas fa-calendar-alt mr-2"></i> Kelola Tanggal
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4">Informasi Dasar</h2>
                <table class="w-full">
                    <tr>
                        <td class="py-2 text-gray-600">Rute:</td>
                        <td class="py-2 font-medium">
                            @if (is_object($schedule->route))
                                {{ $schedule->route->origin }} - {{ $schedule->route->destination }}
                            @else
                                Rute tidak tersedia
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Kapal:</td>
                        <td class="py-2 font-medium">
                            @if (is_object($schedule->ferry))
                                {{ $schedule->ferry->name }}
                            @else
                                Kapal tidak tersedia
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Waktu Keberangkatan:</td>
                        <td class="py-2 font-medium">{{ $schedule->departure_time->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Estimasi Tiba:</td>
                        <td class="py-2 font-medium">{{ $schedule->arrival_time->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Hari Operasi:</td>
                        <td class="py-2 font-medium">
                            @php
                                $days = explode(',', $schedule->days);
                                $dayNames = [
                                    '1' => 'Senin',
                                    '2' => 'Selasa',
                                    '3' => 'Rabu',
                                    '4' => 'Kamis',
                                    '5' => 'Jumat',
                                    '6' => 'Sabtu',
                                    '7' => 'Minggu',
                                    '0' => 'Minggu',
                                ];
                                $dayLabels = [];
                                foreach ($days as $day) {
                                    if (isset($dayNames[$day])) {
                                        $dayLabels[] = $dayNames[$day];
                                    }
                                }
                            @endphp
                            {{ implode(', ', $dayLabels) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Status:</td>
                        <td class="py-2">
                            @if ($schedule->status == 'ACTIVE')
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @elseif($schedule->status == 'DELAYED')
                                <span class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-800">
                                    Tertunda
                                </span>
                            @elseif($schedule->status == 'FULL')
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                    Penuh
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                    Dibatalkan
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Dibuat pada:</td>
                        <td class="py-2 font-medium">{{ $schedule->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Terakhir diperbarui:</td>
                        <td class="py-2 font-medium">{{ $schedule->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4">Informasi Harga & Kapasitas</h2>
                <table class="w-full">
                    <tr>
                        <td class="py-2 text-gray-600">Harga Ekonomi:</td>
                        <td class="py-2 font-medium">Rp {{ number_format($schedule->price_economy, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Harga Bisnis:</td>
                        <td class="py-2 font-medium">Rp {{ number_format($schedule->price_business, 0, ',', '.') }}</td>
                    </tr>
                    @if (is_object($schedule->ferry))
                        <tr>
                            <td class="py-2 text-gray-600">Kapasitas Penumpang:</td>
                            <td class="py-2 font-medium">{{ $schedule->ferry->capacity_passenger }} orang</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Kapasitas Motor:</td>
                            <td class="py-2 font-medium">{{ $schedule->ferry->capacity_vehicle_motorcycle }} unit</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Kapasitas Mobil:</td>
                            <td class="py-2 font-medium">{{ $schedule->ferry->capacity_vehicle_car }} unit</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Kapasitas Bus:</td>
                            <td class="py-2 font-medium">{{ $schedule->ferry->capacity_vehicle_bus }} unit</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Kapasitas Truk:</td>
                            <td class="py-2 font-medium">{{ $schedule->ferry->capacity_vehicle_truck }} unit</td>
                        </tr>
                    @else
                        <tr>
                            <td class="py-2 text-gray-600 colspan="2">Informasi kapasitas tidak tersedia</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <h2 class="text-lg font-semibold mb-4">Tanggal Tersedia</h2>

        <!-- Filter -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center gap-4">
            <div>
                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Filter Bulan:</label>
                <select id="month" name="month"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Bulan</option>
                    @foreach (range(1, 12) as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Filter Tahun:</label>
                <select id="year" name="year"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Tahun</option>
                    @foreach (range(date('Y'), date('Y') + 1) as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-4 md:mt-6">
                <button id="filterBtn" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">#</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Tanggal
                        </th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Hari
                        </th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                            Penumpang</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($scheduleDates) && $scheduleDates->count() > 0)
                        @foreach ($scheduleDates as $date)
                            <tr>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    {{ \Carbon\Carbon::parse($date->date)->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    {{ \Carbon\Carbon::parse($date->date)->translatedFormat('l') }}</td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    @if (is_object($schedule->ferry))
                                        {{ $date->passenger_count ?? 0 }} / {{ $schedule->ferry->capacity_passenger }}
                                    @else
                                        {{ $date->passenger_count ?? 0 }} / -
                                    @endif
                                </td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    @if ($date->status == 'AVAILABLE')
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Tersedia</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Tidak
                                            Tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada
                                tanggal terjadwal</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if (isset($scheduleDates) && $scheduleDates instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                {{ $scheduleDates->links() }}
            </div>
        @endif

        @if (isset($bookings) && count($bookings) > 0)
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-4">Pemesanan Terbaru</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th
                                    class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                    Kode Booking</th>
                                <th
                                    class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                    Tanggal</th>
                                <th
                                    class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                    Penumpang</th>
                                <th
                                    class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                    Kendaraan</th>
                                <th
                                    class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                    Status</th>
                                <th
                                    class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm font-medium">
                                        {{ $booking->booking_code }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        {{ \Carbon\Carbon::parse($booking->travel_date)->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->passenger_count }}
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        @if ($booking->vehicle_type)
                                            {{ $booking->vehicle_count }} {{ $booking->vehicle_type }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        @if ($booking->status == 'CONFIRMED')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Terkonfirmasi</span>
                                        @elseif($booking->status == 'PENDING')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($booking->status == 'CANCELLED')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        <a href="{{ route('admin.bookings.show', $booking) }}"
                                            class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter functionality
            const filterBtn = document.getElementById('filterBtn');
            filterBtn.addEventListener('click', function() {
                const month = document.getElementById('month').value;
                const year = document.getElementById('year').value;

                let url = new URL(window.location.href);
                if (month) url.searchParams.set('month', month);
                else url.searchParams.delete('month');

                if (year) url.searchParams.set('year', year);
                else url.searchParams.delete('year');

                window.location.href = url.toString();
            });
        });
    </script>
@endpush
