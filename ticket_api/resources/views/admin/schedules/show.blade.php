@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-xl overflow-hidden">
        <!-- Header -->
        <div class="p-6 bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 text-white relative">
            <div class="absolute inset-0 overflow-hidden">
                <svg class="absolute right-0 bottom-0 opacity-10 h-64 w-64" viewBox="0 0 200 200"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill="white"
                        d="M46.5,-75.3C58.9,-68.9,67.3,-53.9,74.4,-38.7C81.6,-23.5,87.6,-8.1,85.8,6.3C84,20.7,74.2,34,63,44.4C51.8,54.8,39.2,62.3,25.2,68.2C11.1,74,-4.4,78.2,-19.6,76.1C-34.8,74,-49.6,65.7,-59.5,53.6C-69.4,41.5,-74.3,25.5,-77.6,8.5C-80.9,-8.5,-82.5,-26.5,-75.8,-40C-69.1,-53.5,-54.1,-62.4,-39.3,-67.4C-24.6,-72.5,-10.1,-73.7,4.4,-80.8C18.9,-87.9,34.1,-81.8,46.5,-75.3Z"
                        transform="translate(100 100)" />
                </svg>
            </div>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-0 gap-4 relative z-10">
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-calendar mr-3 text-blue-200"></i> Detail Jadwal
                </h1>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.schedules.index') }}"
                        class="bg-white/20 hover:bg-white/30 text-white py-2 px-4 rounded-lg transition duration-200 backdrop-blur-sm flex items-center shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                    <a href="{{ route('admin.schedules.edit', $schedule) }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition duration-200 flex items-center shadow-sm">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                    <a href="{{ route('admin.schedules.dates', $schedule) }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition duration-200 flex items-center shadow-sm">
                        <i class="fas fa-calendar-alt mr-2"></i> Kelola Tanggal
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50">
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm"
                    role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0 text-green-500 text-xl">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Basic Information Card -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i> Informasi Dasar
                    </h2>
                    <table class="w-full">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600 w-1/3 font-medium">Rute:</td>
                            <td class="py-3">
                                @if (is_object($schedule->route))
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-2 shadow-sm">
                                            <i class="fas fa-route text-blue-600"></i>
                                        </div>
                                        <div>
                                            <span class="text-blue-700 font-medium">{{ $schedule->route->origin }}</span>
                                            <i class="fas fa-long-arrow-alt-right text-gray-400 mx-1"></i>
                                            <span
                                                class="text-blue-700 font-medium">{{ $schedule->route->destination }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Rute tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600 font-medium">Kapal:</td>
                            <td class="py-3">
                                @if (is_object($schedule->ferry))
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-2 shadow-sm">
                                            <i class="fas fa-ship text-blue-600"></i>
                                        </div>
                                        <span class="text-blue-700 font-medium">{{ $schedule->ferry->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Kapal tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600 font-medium">Waktu Keberangkatan:</td>
                            <td class="py-3">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center mr-2 shadow-sm">
                                        <i class="far fa-clock text-green-600"></i>
                                    </div>
                                    <span class="font-medium">{{ $schedule->departure_time->format('H:i') }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600 font-medium">Estimasi Tiba:</td>
                            <td class="py-3">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center mr-2 shadow-sm">
                                        <i class="far fa-clock text-red-600"></i>
                                    </div>
                                    <span class="font-medium">{{ $schedule->arrival_time->format('H:i') }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600 font-medium">Hari Operasi:</td>
                            <td class="py-3">
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
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($dayLabels as $dayLabel)
                                        <span
                                            class="px-2 py-1 rounded-md text-xs bg-indigo-100 text-indigo-800 font-medium shadow-sm">
                                            {{ $dayLabel }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600 font-medium">Status:</td>
                            <td class="py-3">
                                @if ($schedule->status == 'ACTIVE')
                                    <span
                                        class="px-3 py-1.5 rounded-full text-xs bg-green-100 text-green-800 font-medium shadow-sm flex w-fit items-center">
                                        <i class="fas fa-check-circle mr-1.5"></i> Aktif
                                    </span>
                                @elseif($schedule->status == 'DELAYED')
                                    <span
                                        class="px-3 py-1.5 rounded-full text-xs bg-orange-100 text-orange-800 font-medium shadow-sm flex w-fit items-center">
                                        <i class="fas fa-exclamation-circle mr-1.5"></i> Tertunda
                                    </span>
                                @elseif($schedule->status == 'FULL')
                                    <span
                                        class="px-3 py-1.5 rounded-full text-xs bg-blue-100 text-blue-800 font-medium shadow-sm flex w-fit items-center">
                                        <i class="fas fa-users mr-1.5"></i> Penuh
                                    </span>
                                @else
                                    <span
                                        class="px-3 py-1.5 rounded-full text-xs bg-red-100 text-red-800 font-medium shadow-sm flex w-fit items-center">
                                        <i class="fas fa-times-circle mr-1.5"></i> Dibatalkan
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600 font-medium">Dibuat pada:</td>
                            <td class="py-3">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-2 shadow-sm">
                                        <i class="fas fa-calendar-plus text-gray-600"></i>
                                    </div>
                                    <span
                                        class="font-medium text-gray-800">{{ $schedule->created_at->format('d M Y H:i') }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600 font-medium">Terakhir diperbarui:</td>
                            <td class="py-3">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-2 shadow-sm">
                                        <i class="fas fa-calendar-check text-gray-600"></i>
                                    </div>
                                    <span
                                        class="font-medium text-gray-800">{{ $schedule->updated_at->format('d M Y H:i') }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Capacity Information Card -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-3 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-blue-600"></i> Informasi Kapasitas
                    </h2>
                    @if (is_object($schedule->ferry))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                class="p-5 bg-gray-50 rounded-lg shadow-sm border border-gray-200 transition hover:bg-gray-100">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-3 shadow-sm">
                                        <i class="fas fa-users text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 text-xs uppercase tracking-wider font-medium">Penumpang</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $schedule->ferry->capacity_passenger }}</p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="p-5 bg-gray-50 rounded-lg shadow-sm border border-gray-200 transition hover:bg-gray-100">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-3 shadow-sm">
                                        <i class="fas fa-motorcycle text-green-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 text-xs uppercase tracking-wider font-medium">Motor</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $schedule->ferry->capacity_vehicle_motorcycle }}</p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="p-5 bg-gray-50 rounded-lg shadow-sm border border-gray-200 transition hover:bg-gray-100">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center mr-3 shadow-sm">
                                        <i class="fas fa-car text-yellow-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 text-xs uppercase tracking-wider font-medium">Mobil</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $schedule->ferry->capacity_vehicle_car }}</p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="p-5 bg-gray-50 rounded-lg shadow-sm border border-gray-200 transition hover:bg-gray-100">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center mr-3 shadow-sm">
                                        <i class="fas fa-bus text-purple-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 text-xs uppercase tracking-wider font-medium">Bus</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $schedule->ferry->capacity_vehicle_bus }}</p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="p-5 bg-gray-50 rounded-lg shadow-sm border border-gray-200 sm:col-span-2 transition hover:bg-gray-100">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-3 shadow-sm">
                                        <i class="fas fa-truck text-red-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 text-xs uppercase tracking-wider font-medium">Truk</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $schedule->ferry->capacity_vehicle_truck }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-6 bg-gray-50 rounded-lg text-center">
                            <div
                                class="w-16 h-16 mx-auto rounded-full bg-yellow-100 flex items-center justify-center mb-3 shadow-sm">
                                <i class="fas fa-exclamation-circle text-yellow-500 text-2xl"></i>
                            </div>
                            <p class="text-gray-700 font-medium">Informasi kapasitas tidak tersedia</p>
                            <p class="text-gray-500 text-sm mt-2">Data kapal tidak ditemukan atau belum dikonfigurasi.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Available Dates Section -->
            <div class="mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition">
                <h2 class="text-lg font-semibold mb-5 text-gray-800 pb-2 border-b flex items-center">
                    <i class="fas fa-calendar-day mr-2 text-blue-600"></i> Tanggal Tersedia
                </h2>

                <!-- Filter -->
                <div class="mb-6 bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h3 class="text-md font-medium mb-3 text-gray-700 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-500"></i>Filter
                    </h3>
                    <form class="flex flex-col md:flex-row md:items-end gap-4">
                        <div class="flex-grow">
                            <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Bulan:</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                                <select id="month" name="month"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                                    <option value="">Semua Bulan</option>
                                    @foreach (range(1, 12) as $month)
                                        <option value="{{ $month }}"
                                            {{ request('month') == $month ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun:</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                                <select id="year" name="year"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                                    <option value="">Semua Tahun</option>
                                    @foreach (range(date('Y'), date('Y') + 1) as $year)
                                        <option value="{{ $year }}"
                                            {{ request('year') == $year ? 'selected' : '' }}>
                                            {{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="md:flex-shrink-0">
                            <button id="filterBtn"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-4 rounded-lg transition shadow-sm flex items-center justify-center">
                                <i class="fas fa-filter mr-2"></i> Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th
                                    class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th
                                    class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th
                                    class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hari
                                </th>
                                <th
                                    class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kapasitas Terisi</th>
                                <th
                                    class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($scheduleDates) && $scheduleDates->count() > 0)
                                @foreach ($scheduleDates as $date)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $loop->iteration }}</td>
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm font-medium">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center mr-2 shadow-sm">
                                                    <i class="fas fa-calendar text-indigo-500"></i>
                                                </div>
                                                {{ \Carbon\Carbon::parse($date->date)->format('d/m/Y') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs">
                                                {{ \Carbon\Carbon::parse($date->date)->translatedFormat('l') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            @if (is_object($schedule->ferry))
                                                <div class="grid grid-cols-5 gap-2">
                                                    <div class="text-xs flex items-center">
                                                        <i class="fas fa-users text-blue-500 mr-1"></i>
                                                        <span class="font-medium">{{ $date->passenger_count ?? 0 }} /
                                                            {{ $schedule->ferry->capacity_passenger }}</span>
                                                    </div>
                                                    <div class="text-xs flex items-center">
                                                        <i class="fas fa-motorcycle text-green-500 mr-1"></i>
                                                        <span class="font-medium">{{ $date->motorcycle_count ?? 0 }} /
                                                            {{ $schedule->ferry->capacity_vehicle_motorcycle }}</span>
                                                    </div>
                                                    <div class="text-xs flex items-center">
                                                        <i class="fas fa-car text-yellow-500 mr-1"></i>
                                                        <span class="font-medium">{{ $date->car_count ?? 0 }} /
                                                            {{ $schedule->ferry->capacity_vehicle_car }}</span>
                                                    </div>
                                                    <div class="text-xs flex items-center">
                                                        <i class="fas fa-bus text-purple-500 mr-1"></i>
                                                        <span class="font-medium">{{ $date->bus_count ?? 0 }} /
                                                            {{ $schedule->ferry->capacity_vehicle_bus }}</span>
                                                    </div>
                                                    <div class="text-xs flex items-center">
                                                        <i class="fas fa-truck text-red-500 mr-1"></i>
                                                        <span class="font-medium">{{ $date->truck_count ?? 0 }} /
                                                            {{ $schedule->ferry->capacity_vehicle_truck }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-500 italic">Data tidak tersedia</span>
                                            @endif
                                        </td>
                                        <!-- Status badge column -->
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            @if ($date->status == 'AVAILABLE')
                                                <span
                                                    class="px-3 py-1.5 rounded-full text-xs bg-green-100 text-green-800 flex items-center w-fit shadow-sm">
                                                    <i class="fas fa-check-circle mr-1.5"></i> Tersedia
                                                </span>
                                            @elseif ($date->status == 'WEATHER_ISSUE')
                                                <span
                                                    class="px-3 py-1.5 rounded-full text-xs bg-yellow-100 text-yellow-800 flex items-center w-fit shadow-sm">
                                                    <i class="fas fa-cloud-rain mr-1.5"></i> Masalah Cuaca
                                                </span>
                                            @elseif ($date->status == 'FULL')
                                                <span
                                                    class="px-3 py-1.5 rounded-full text-xs bg-blue-100 text-blue-800 flex items-center w-fit shadow-sm">
                                                    <i class="fas fa-users mr-1.5"></i> Penuh
                                                </span>
                                            @elseif ($date->status == 'DEPARTED')
                                                <span
                                                    class="px-3 py-1.5 rounded-full text-xs bg-purple-100 text-purple-800 flex items-center w-fit shadow-sm">
                                                    <i class="fas fa-check-double mr-1.5"></i> Selesai
                                                </span>
                                            @else
                                                <span
                                                    class="px-3 py-1.5 rounded-full text-xs bg-red-100 text-red-800 flex items-center w-fit shadow-sm">
                                                    <i class="fas fa-ban mr-1.5"></i> Tidak Tersedia
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5"
                                        class="py-10 px-4 border-b border-gray-200 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-3 shadow-sm">
                                                <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                                            </div>
                                            <p class="text-lg font-medium text-gray-700">Tidak ada tanggal terjadwal</p>
                                            <p class="text-sm text-gray-500 mt-1">Silakan gunakan menu "Kelola Tanggal"
                                                untuk menambahkan jadwal.</p>
                                        </div>
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

            <!-- Recent Bookings Section -->
            @if (isset($bookings) && count($bookings) > 0)
                <div class="mt-6 bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition">
                    <h2 class="text-lg font-semibold mb-5 text-gray-800 pb-2 border-b flex items-center">
                        <i class="fas fa-ticket-alt mr-2 text-blue-600"></i> Pemesanan Terbaru
                    </h2>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th
                                        class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kode Booking</th>
                                    <th
                                        class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal</th>
                                    <th
                                        class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Penumpang</th>
                                    <th
                                        class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kendaraan</th>
                                    <th
                                        class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="py-3.5 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-2 shadow-sm">
                                                    <i class="fas fa-ticket-alt text-blue-600"></i>
                                                </div>
                                                <span
                                                    class="font-medium text-blue-700">{{ $booking->booking_code }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-day text-gray-400 mr-1.5"></i>
                                                {{ \Carbon\Carbon::parse($booking->travel_date)->format('d/m/Y') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center mr-1 shadow-sm">
                                                    <i class="fas fa-users text-indigo-600 text-xs"></i>
                                                </div>
                                                <span class="font-medium">{{ $booking->passenger_count }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            @if ($booking->vehicle_type)
                                                <div class="flex items-center">
                                                    <div
                                                        class="w-6 h-6 rounded-full flex items-center justify-center mr-1 shadow-sm
                                                        @if ($booking->vehicle_type == 'MOTORCYCLE') bg-green-100 @endif
                                                        @if ($booking->vehicle_type == 'CAR') bg-yellow-100 @endif
                                                        @if ($booking->vehicle_type == 'BUS') bg-purple-100 @endif
                                                        @if ($booking->vehicle_type == 'TRUCK') bg-red-100 @endif
                                                    ">
                                                        @if ($booking->vehicle_type == 'MOTORCYCLE')
                                                            <i class="fas fa-motorcycle text-green-600 text-xs"></i>
                                                        @elseif ($booking->vehicle_type == 'CAR')
                                                            <i class="fas fa-car text-yellow-600 text-xs"></i>
                                                        @elseif ($booking->vehicle_type == 'BUS')
                                                            <i class="fas fa-bus text-purple-600 text-xs"></i>
                                                        @elseif ($booking->vehicle_type == 'TRUCK')
                                                            <i class="fas fa-truck text-red-600 text-xs"></i>
                                                        @endif
                                                    </div>
                                                    <span class="font-medium">{{ $booking->vehicle_count }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            @if ($booking->status == 'CONFIRMED')
                                                <span
                                                    class="px-3 py-1.5 rounded-full text-xs bg-green-100 text-green-800 shadow-sm flex w-fit items-center">
                                                    <i class="fas fa-check-circle mr-1.5"></i> Terkonfirmasi
                                                </span>
                                            @elseif($booking->status == 'PENDING')
                                                <span
                                                    class="px-3 py-1.5 rounded-full text-xs bg-yellow-100 text-yellow-800 shadow-sm flex w-fit items-center">
                                                    <i class="fas fa-clock mr-1.5"></i> Pending
                                                </span>
                                            @elseif($booking->status == 'CANCELLED')
                                                <span
                                                    class="px-3 py-1.5 rounded-full text-xs bg-red-100 text-red-800 shadow-sm flex w-fit items-center">
                                                    <i class="fas fa-times-circle mr-1.5"></i> Dibatalkan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                            <a href="{{ route('admin.bookings.show', $booking) }}"
                                                class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition shadow-sm flex w-fit items-center">
                                                <i class="fas fa-eye mr-1.5"></i> Detail
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
