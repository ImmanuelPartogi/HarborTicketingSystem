@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Detail Jadwal</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.schedules.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded transition duration-200 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
                <a href="{{ route('admin.schedules.edit', $schedule) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded transition duration-200 flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('admin.schedules.dates', $schedule) }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition duration-200 flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i> Kelola Tanggal
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 p-5 rounded-lg shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Informasi Dasar</h2>
                <table class="w-full">
                    <tr>
                        <td class="py-2 text-gray-600 w-1/3">Rute:</td>
                        <td class="py-2 font-medium">
                            @if (is_object($schedule->route))
                                <span class="text-blue-600">{{ $schedule->route->origin }}</span>
                                <i class="fas fa-long-arrow-alt-right text-gray-400 mx-1"></i>
                                <span class="text-blue-600">{{ $schedule->route->destination }}</span>
                            @else
                                <span class="text-gray-500 italic">Rute tidak tersedia</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Kapal:</td>
                        <td class="py-2 font-medium">
                            @if (is_object($schedule->ferry))
                                <span class="text-blue-600">{{ $schedule->ferry->name }}</span>
                            @else
                                <span class="text-gray-500 italic">Kapal tidak tersedia</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Waktu Keberangkatan:</td>
                        <td class="py-2 font-medium">
                            <span class="inline-flex items-center">
                                <i class="far fa-clock text-gray-400 mr-2"></i>
                                {{ $schedule->departure_time->format('H:i') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Estimasi Tiba:</td>
                        <td class="py-2 font-medium">
                            <span class="inline-flex items-center">
                                <i class="far fa-clock text-gray-400 mr-2"></i>
                                {{ $schedule->arrival_time->format('H:i') }}
                            </span>
                        </td>
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
                            <div class="flex flex-wrap gap-1">
                                @foreach ($dayLabels as $dayLabel)
                                    <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                                        {{ $dayLabel }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Status:</td>
                        <td class="py-2">
                            @if ($schedule->status == 'ACTIVE')
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 font-medium">
                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                </span>
                            @elseif($schedule->status == 'DELAYED')
                                <span class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-800 font-medium">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Tertunda
                                </span>
                            @elseif($schedule->status == 'FULL')
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 font-medium">
                                    <i class="fas fa-users mr-1"></i> Penuh
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 font-medium">
                                    <i class="fas fa-times-circle mr-1"></i> Dibatalkan
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Dibuat pada:</td>
                        <td class="py-2 font-medium text-gray-700">{{ $schedule->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Terakhir diperbarui:</td>
                        <td class="py-2 font-medium text-gray-700">{{ $schedule->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>

            <div class="bg-gray-50 p-5 rounded-lg shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Informasi Kapasitas</h2>
                @if (is_object($schedule->ferry))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="text-center">
                                <i class="fas fa-users text-blue-500 text-2xl mb-2"></i>
                                <p class="text-gray-600 text-sm">Penumpang</p>
                                <p class="text-xl font-bold text-gray-800">{{ $schedule->ferry->capacity_passenger }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="text-center">
                                <i class="fas fa-motorcycle text-blue-500 text-2xl mb-2"></i>
                                <p class="text-gray-600 text-sm">Motor</p>
                                <p class="text-xl font-bold text-gray-800">
                                    {{ $schedule->ferry->capacity_vehicle_motorcycle }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="text-center">
                                <i class="fas fa-car text-blue-500 text-2xl mb-2"></i>
                                <p class="text-gray-600 text-sm">Mobil</p>
                                <p class="text-xl font-bold text-gray-800">{{ $schedule->ferry->capacity_vehicle_car }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="text-center">
                                <i class="fas fa-bus text-blue-500 text-2xl mb-2"></i>
                                <p class="text-gray-600 text-sm">Bus</p>
                                <p class="text-xl font-bold text-gray-800">{{ $schedule->ferry->capacity_vehicle_bus }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm border border-gray-100 sm:col-span-2">
                            <div class="text-center">
                                <i class="fas fa-truck text-blue-500 text-2xl mb-2"></i>
                                <p class="text-gray-600 text-sm">Truk</p>
                                <p class="text-xl font-bold text-gray-800">{{ $schedule->ferry->capacity_vehicle_truck }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-white rounded-lg text-center">
                        <i class="fas fa-exclamation-circle text-yellow-500 text-2xl mb-2"></i>
                        <p class="text-gray-600">Informasi kapasitas tidak tersedia</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-4 text-gray-800 pb-2 border-b">Tanggal Tersedia</h2>

            <!-- Filter -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h3 class="text-md font-medium mb-3 text-gray-700"><i class="fas fa-filter mr-2"></i>Filter</h3>
                <form class="flex flex-col md:flex-row md:items-end gap-4">
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Bulan:</label>
                        <select id="month" name="month"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 w-full">
                            <option value="">Semua Bulan</option>
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun:</label>
                        <select id="year" name="year"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 w-full">
                            <option value="">Semua Tahun</option>
                            @foreach (range(date('Y'), date('Y') + 1) as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                    {{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button id="filterBtn"
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition duration-200 w-full">
                            <i class="fas fa-filter mr-2"></i> Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">#
                            </th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Tanggal
                            </th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Hari
                            </th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Kapasitas Terisi</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($scheduleDates) && $scheduleDates->count() > 0)
                            @foreach ($scheduleDates as $date)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $loop->iteration }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm font-medium">
                                        {{ \Carbon\Carbon::parse($date->date)->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        {{ \Carbon\Carbon::parse($date->date)->translatedFormat('l') }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        @if (is_object($schedule->ferry))
                                            <div class="grid grid-cols-5 gap-2">
                                                <div class="text-xs">
                                                    <i class="fas fa-users text-blue-500"></i>
                                                    <span>{{ $date->passenger_count ?? 0 }} /
                                                        {{ $schedule->ferry->capacity_passenger }}</span>
                                                </div>
                                                <div class="text-xs">
                                                    <i class="fas fa-motorcycle text-green-500"></i>
                                                    <span>{{ $date->motorcycle_count ?? 0 }} /
                                                        {{ $schedule->ferry->capacity_vehicle_motorcycle }}</span>
                                                </div>
                                                <div class="text-xs">
                                                    <i class="fas fa-car text-yellow-500"></i>
                                                    <span>{{ $date->car_count ?? 0 }} /
                                                        {{ $schedule->ferry->capacity_vehicle_car }}</span>
                                                </div>
                                                <div class="text-xs">
                                                    <i class="fas fa-bus text-purple-500"></i>
                                                    <span>{{ $date->bus_count ?? 0 }} /
                                                        {{ $schedule->ferry->capacity_vehicle_bus }}</span>
                                                </div>
                                                <div class="text-xs">
                                                    <i class="fas fa-truck text-red-500"></i>
                                                    <span>{{ $date->truck_count ?? 0 }} /
                                                        {{ $schedule->ferry->capacity_vehicle_truck }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-500 italic">Data tidak tersedia</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        @if ($date->status == 'AVAILABLE')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 font-medium">
                                                <i class="fas fa-check-circle mr-1"></i> Tersedia
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 font-medium">
                                                <i class="fas fa-times-circle mr-1"></i> Tidak Tersedia
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5"
                                    class="py-8 px-4 border-b border-gray-200 text-sm text-center text-gray-500">
                                    <i class="fas fa-calendar-times text-gray-400 text-3xl mb-2"></i>
                                    <p>Tidak ada tanggal terjadwal</p>
                                </td>
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
        </div>

        @if (isset($bookings) && count($bookings) > 0)
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 pb-2 border-b">Pemesanan Terbaru</h2>
                <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
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
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm font-medium text-blue-600">
                                        {{ $booking->booking_code }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        {{ \Carbon\Carbon::parse($booking->travel_date)->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        <span class="inline-flex items-center">
                                            <i class="fas fa-users text-gray-400 mr-1"></i>
                                            {{ $booking->passenger_count }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        @if ($booking->vehicle_type)
                                            <span class="inline-flex items-center">
                                                @if ($booking->vehicle_type == 'MOTORCYCLE')
                                                    <i class="fas fa-motorcycle text-gray-400 mr-1"></i>
                                                @elseif ($booking->vehicle_type == 'CAR')
                                                    <i class="fas fa-car text-gray-400 mr-1"></i>
                                                @elseif ($booking->vehicle_type == 'BUS')
                                                    <i class="fas fa-bus text-gray-400 mr-1"></i>
                                                @elseif ($booking->vehicle_type == 'TRUCK')
                                                    <i class="fas fa-truck text-gray-400 mr-1"></i>
                                                @endif
                                                {{ $booking->vehicle_count }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        @if ($booking->status == 'CONFIRMED')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 font-medium">
                                                <i class="fas fa-check-circle mr-1"></i> Terkonfirmasi
                                            </span>
                                        @elseif($booking->status == 'PENDING')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800 font-medium">
                                                <i class="fas fa-clock mr-1"></i> Pending
                                            </span>
                                        @elseif($booking->status == 'CANCELLED')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 font-medium">
                                                <i class="fas fa-times-circle mr-1"></i> Dibatalkan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                        <a href="{{ route('admin.bookings.show', $booking) }}"
                                            class="text-blue-500 hover:text-blue-700 transition duration-200">
                                            <i class="fas fa-eye"></i> Detail
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter functionality
        const filterBtn = document.getElementById('filterBtn');
        filterBtn.addEventListener('click', function(e) {
            e.preventDefault();
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
