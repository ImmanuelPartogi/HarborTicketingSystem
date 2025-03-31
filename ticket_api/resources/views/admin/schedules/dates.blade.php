@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-xl overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 p-6 text-white relative">
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
                    <i class="fas fa-calendar-alt mr-3 text-blue-200"></i> Kelola Jadwal Operasi
                </h1>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('admin.schedules.show', $schedule) }}"
                        class="bg-white/20 hover:bg-white/30 text-white py-2 px-4 rounded-lg transition backdrop-blur-sm flex items-center justify-center shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                    <button type="button" id="addDateBtn"
                        class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition flex items-center justify-center shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Tambah Jadwal
                    </button>
                </div>
            </div>
        </div>

        <!-- Alerts Section -->
        <div class="p-6">
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm fade-out-alert"
                    role="alert">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 text-green-500 text-xl">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm fade-out-alert"
                    role="alert">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 text-red-500 text-xl">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Schedule Info Card -->
            <div class="mb-6">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 bg-gray-50 border-b border-gray-200 rounded-t-xl">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Informasi Jadwal
                        </h2>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm hover:bg-gray-100 transition">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Rute:</p>
                                <p class="font-medium text-gray-800 flex items-center">
                                    @if (is_object($schedule->route))
                                        <i class="fas fa-route text-blue-600 mr-2"></i>
                                        {{ $schedule->route->origin }} - {{ $schedule->route->destination }}
                                    @else
                                        <span class="text-red-500">Rute tidak tersedia</span>
                                    @endif
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm hover:bg-gray-100 transition">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Waktu
                                    Keberangkatan:</p>
                                <p class="font-medium text-gray-800 flex items-center">
                                    <i class="fas fa-clock text-green-600 mr-2"></i>
                                    {{ $schedule->departure_time->format('H:i') }}
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm hover:bg-gray-100 transition">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Estimasi Tiba:
                                </p>
                                <p class="font-medium text-gray-800 flex items-center">
                                    <i class="fas fa-hourglass-end text-red-600 mr-2"></i>
                                    {{ $schedule->arrival_time->format('H:i') }}
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm hover:bg-gray-100 transition">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Kapal:</p>
                                <p class="font-medium text-gray-800 flex items-center">
                                    @if (is_object($schedule->ferry))
                                        <i class="fas fa-ship text-blue-600 mr-2"></i>
                                        {{ $schedule->ferry->name }}
                                    @else
                                        <span class="text-red-500">Kapal tidak tersedia</span>
                                    @endif
                                </p>
                            </div>
                            <div
                                class="bg-gray-50 p-4 rounded-lg shadow-sm hover:bg-gray-100 transition md:col-span-2 lg:col-span-1">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Hari Operasi:</p>
                                <p class="font-medium text-gray-800 flex items-center">
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
                                        ];
                                        $dayLabels = [];
                                        foreach ($days as $day) {
                                            if (isset($dayNames[$day])) {
                                                $dayLabels[] = $dayNames[$day];
                                            }
                                        }
                                    @endphp
                                    <i class="fas fa-calendar-day text-indigo-600 mr-2"></i>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($dayLabels as $day)
                                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs rounded-md shadow-sm">
                                            {{ $day }}
                                        </span>
                                    @endforeach
                                </div>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="mb-6">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 bg-gray-50 border-b border-gray-200 rounded-t-xl">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-filter text-indigo-600 mr-2"></i>Filter Tanggal
                        </h2>
                    </div>
                    <div class="p-5">
                        <form action="{{ url()->current() }}" method="GET" id="filterForm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label for="month"
                                        class="block text-sm font-medium text-gray-700 mb-1">Bulan:</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        </div>
                                        <select id="month" name="month"
                                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors">
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
                                <div>
                                    <label for="year"
                                        class="block text-sm font-medium text-gray-700 mb-1">Tahun:</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        </div>
                                        <select id="year" name="year"
                                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors">
                                            <option value="">Semua Tahun</option>
                                            @foreach (range(date('Y'), date('Y') + 1) as $year)
                                                <option value="{{ $year }}"
                                                    {{ request('year') == $year ? 'selected' : '' }}>
                                                    {{ $year }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="status"
                                        class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-tag text-gray-400"></i>
                                        </div>
                                        <select id="status" name="status"
                                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors">
                                            <option value="">Semua Status</option>
                                            <option value="AVAILABLE"
                                                {{ request('status') == 'AVAILABLE' ? 'selected' : '' }}>
                                                Tersedia</option>
                                            <option value="WEATHER_ISSUE"
                                                {{ request('status') == 'WEATHER_ISSUE' ? 'selected' : '' }}>
                                                Masalah Cuaca</option>
                                            <option value="FULL" {{ request('status') == 'FULL' ? 'selected' : '' }}>
                                                Penuh</option>
                                            <option value="DEPARTED"
                                                {{ request('status') == 'DEPARTED' ? 'selected' : '' }}>
                                                Selesai</option>
                                            <option value="UNAVAILABLE"
                                                {{ request('status') == 'UNAVAILABLE' ? 'selected' : '' }}>
                                                Tidak Tersedia</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit"
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition shadow-sm flex items-center justify-center">
                                        <i class="fas fa-filter mr-2"></i> Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Dates Table Card -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="px-5 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center rounded-t-xl">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-alt text-green-600 mr-2"></i>Daftar Tanggal Jadwal
                    </h2>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1.5 rounded-full shadow-sm">
                        Total: {{ $scheduleDates->total() ?? 0 }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-left text-xs font-medium uppercase tracking-wider">
                                <th class="py-3.5 px-4 font-medium border-b">#</th>
                                <th class="py-3.5 px-4 font-medium border-b">Tanggal</th>
                                <th class="py-3.5 px-4 font-medium border-b">Hari</th>
                                <th class="py-3.5 px-4 font-medium border-b">Penumpang</th>
                                <th class="py-3.5 px-4 font-medium border-b">Kendaraan</th>
                                <th class="py-3.5 px-4 font-medium border-b">Status</th>
                                <th class="py-3.5 px-4 font-medium border-b">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($scheduleDates ?? [] as $date)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 border-b text-sm text-gray-500">{{ $loop->iteration }}</td>
                                    <td class="py-3 px-4 border-b text-sm">
                                        <div class="font-medium flex items-center">
                                            <div
                                                class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mr-2 shadow-sm">
                                                <i class="fas fa-calendar-day"></i>
                                            </div>
                                            {{ is_object($date) && $date->date ? \Carbon\Carbon::parse($date->date)->format('d/m/Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 border-b text-sm">
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs">
                                            {{ is_object($date) && $date->date ? \Carbon\Carbon::parse($date->date)->locale('id')->translatedFormat('l') : '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 border-b text-sm">
                                        @if (is_object($schedule->ferry))
                                            <div class="flex items-center">
                                                <div class="flex items-center bg-blue-50 px-2 py-1 rounded-md">
                                                    <i class="fas fa-users text-blue-600 mr-2"></i>
                                                    <span
                                                        class="font-medium">{{ is_object($date) ? $date->passenger_count ?? 0 : 0 }}</span>
                                                    <span class="text-gray-400 mx-1">/</span>
                                                    <span>{{ $schedule->ferry->capacity_passenger }}</span>
                                                </div>
                                                @php
                                                    $passengerPercentage =
                                                        is_object($date) && $schedule->ferry->capacity_passenger > 0
                                                            ? min(
                                                                100,
                                                                round(
                                                                    (($date->passenger_count ?? 0) /
                                                                        $schedule->ferry->capacity_passenger) *
                                                                        100,
                                                                ),
                                                            )
                                                            : 0;
                                                @endphp
                                                <div
                                                    class="w-20 h-2 ml-2 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                                                    <div class="h-full rounded-full {{ $passengerPercentage > 80 ? 'bg-red-500' : 'bg-blue-500' }}"
                                                        style="width: {{ $passengerPercentage }}%"></div>
                                                </div>
                                                <span
                                                    class="ml-1 text-xs text-gray-500">{{ $passengerPercentage }}%</span>
                                            </div>
                                        @else
                                            <span
                                                class="text-gray-400">{{ is_object($date) ? $date->passenger_count ?? 0 : 0 }}
                                                / -</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border-b text-sm">
                                        @if (is_object($schedule->ferry))
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="flex items-center text-xs bg-green-50 px-2 py-1 rounded-md">
                                                    <i class="fas fa-motorcycle text-green-600 mr-1"></i>
                                                    <span
                                                        class="font-medium">{{ is_object($date) ? $date->motorcycle_count ?? 0 : 0 }}</span>
                                                    <span class="text-gray-400 mx-0.5">/</span>
                                                    <span>{{ $schedule->ferry->capacity_vehicle_motorcycle }}</span>
                                                </div>
                                                <div class="flex items-center text-xs bg-yellow-50 px-2 py-1 rounded-md">
                                                    <i class="fas fa-car text-yellow-600 mr-1"></i>
                                                    <span class="font-medium">{{ $date->car_count ?? 0 }}</span>
                                                    <span class="text-gray-400 mx-0.5">/</span>
                                                    <span>{{ $schedule->ferry->capacity_vehicle_car }}</span>
                                                </div>
                                                <div class="flex items-center text-xs bg-purple-50 px-2 py-1 rounded-md">
                                                    <i class="fas fa-bus text-purple-600 mr-1"></i>
                                                    <span class="font-medium">{{ $date->bus_count ?? 0 }}</span>
                                                    <span class="text-gray-400 mx-0.5">/</span>
                                                    <span>{{ $schedule->ferry->capacity_vehicle_bus }}</span>
                                                </div>
                                                <div class="flex items-center text-xs bg-red-50 px-2 py-1 rounded-md">
                                                    <i class="fas fa-truck text-red-600 mr-1"></i>
                                                    <span class="font-medium">{{ $date->truck_count ?? 0 }}</span>
                                                    <span class="text-gray-400 mx-0.5">/</span>
                                                    <span>{{ $schedule->ferry->capacity_vehicle_truck }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <!-- Status display in table -->
                                    <td class="py-3 px-4 border-b text-sm">
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
                                    <td class="py-3 px-4 border-b text-sm">
                                        <div class="flex space-x-1">
                                            <button
                                                class="edit-date-btn bg-yellow-100 text-yellow-600 hover:bg-yellow-200 p-2 rounded-lg shadow-sm"
                                                data-id="{{ $date->id }}" data-date="{{ $date->date }}"
                                                data-status="{{ $date->status }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                class="delete-date-btn bg-red-100 text-red-600 hover:bg-red-200 p-2 rounded-lg shadow-sm"
                                                data-id="{{ $date->id }}"
                                                data-date="{{ \Carbon\Carbon::parse($date->date)->format('d/m/Y') }}"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 px-4 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div
                                                class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <i class="fas fa-calendar-times text-gray-300 text-3xl"></i>
                                            </div>
                                            <p class="text-lg font-medium text-gray-700 mb-2">Tidak ada tanggal terjadwal
                                            </p>
                                            <p class="text-sm text-gray-500 mb-6">Tambahkan jadwal baru untuk kapal ini</p>
                                            <button id="emptyAddDateBtn"
                                                class="bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-5 rounded-lg transition shadow-sm">
                                                <i class="fas fa-plus mr-2"></i> Tambah Jadwal Baru
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if (isset($scheduleDates) &&
                    $scheduleDates instanceof \Illuminate\Pagination\LengthAwarePaginator &&
                    $scheduleDates->hasPages())
                <div class="mt-4 flex justify-center">
                    {{ $scheduleDates->appends(request()->except('page'))->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add Date Modal - Fixed Structure -->
    <div id="addDateModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-xl relative animate-modalFadeIn">
                <div class="flex justify-between items-center mb-4 pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-plus text-blue-600 mr-2"></i>Tambah Jadwal
                    </h3>
                    <button type="button" id="closeAddModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Operation Days Info -->
                <div class="bg-blue-50 p-4 rounded-lg mb-5 border border-blue-200 shadow-sm">
                    <div class="flex">
                        <div class="text-blue-600 mr-3 text-xl">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-800">
                                Hari operasi: <strong>{{ implode(', ', $dayLabels) }}</strong>
                            </p>
                            <p class="text-xs text-blue-700 mt-1">Hanya tanggal yang jatuh pada hari operasi yang dapat
                                dipilih.</p>
                        </div>
                    </div>
                </div>

                <form id="addDateForm" action="{{ route('admin.schedules.dates.store', $schedule) }}" method="POST"
                    class="space-y-4">
                    @csrf
                    <div>
                        <label for="date_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe
                            Penambahan</label>
                        <div class="relative">
                            <select id="date_type" name="date_type"
                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="single">Tanggal Tunggal</option>
                                <option value="range">Rentang Tanggal</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Single date fields section -->
                    <div id="singleDateFields" class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                        <div>
                            <label for="single_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <div class="flex items-center">
                                <div class="relative flex-grow">
                                    <input type="date" id="single_date" name="single_date"
                                        class="date-input w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <span id="single_date_day" class="ml-2 px-3 py-1 rounded-md font-medium shadow-sm"></span>
                            </div>
                            <div id="single_date_warning"
                                class="hidden mt-2 text-xs text-red-600 bg-red-50 p-2 rounded-lg border border-red-200 shadow-sm">
                            </div>
                            <div class="date-type-info text-xs text-blue-600 mt-1"></div>
                        </div>
                    </div>

                    <!-- Range date fields section -->
                    <div id="rangeDateFields"
                        class="hidden bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm space-y-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Mulai</label>
                            <div class="flex items-center">
                                <div class="relative flex-grow">
                                    <input type="date" id="start_date" name="start_date"
                                        class="date-input w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <span id="start_date_day" class="ml-2 px-3 py-1 rounded-md font-medium shadow-sm"></span>
                            </div>
                            <div id="start_date_warning"
                                class="hidden mt-2 text-xs text-red-600 bg-red-50 p-2 rounded-lg border border-red-200 shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Akhir</label>
                            <div class="flex items-center">
                                <div class="relative flex-grow">
                                    <input type="date" id="end_date" name="end_date"
                                        class="date-input w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <span id="end_date_day" class="ml-2 px-3 py-1 rounded-md font-medium shadow-sm"></span>
                            </div>
                            <div id="end_date_warning"
                                class="hidden mt-2 text-xs text-red-600 bg-red-50 p-2 rounded-lg border border-red-200 shadow-sm">
                            </div>
                        </div>

                        <!-- Preview of valid operation days in the range -->
                        <div id="range_date_preview" class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                        </div>
                    </div>

                    <!-- Status options in add date modal -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <div class="relative">
                            <select id="status" name="status"
                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="AVAILABLE">Tersedia</option>
                                <option value="UNAVAILABLE">Tidak Tersedia</option>
                                <option value="WEATHER_ISSUE">Masalah Cuaca</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-toggle-on text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Status dapat diubah sesuai kondisi operasional.</p>
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                        <button type="button" id="cancelAddBtn"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 px-5 rounded-lg transition shadow-sm">
                            Batal
                        </button>
                        <button type="submit" id="submitBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-5 rounded-lg transition shadow-sm">
                            <i class="fas fa-save mr-2"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Date Modal with 5 Status Options -->
    <div id="editDateModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md relative animate-modalFadeIn">
                <div class="flex justify-between items-center mb-4 pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-edit text-yellow-600 mr-2"></i>Edit Status Jadwal
                    </h3>
                    <button type="button" id="closeEditModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editDateForm" action="#" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_date_id" name="date_id">
                    <input type="hidden" id="original_date" name="date">

                    <div class="bg-yellow-50 p-4 rounded-lg mb-5 border border-yellow-200 shadow-sm">
                        <div class="flex">
                            <div class="text-yellow-600 mr-3 text-xl">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-yellow-800" id="currentDateDisplay">
                                    Jadwal:
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="date_status_info"
                        class="hidden bg-blue-50 p-4 rounded-lg mb-5 border border-blue-200 shadow-sm">
                        <div class="flex">
                            <div class="text-blue-600 mr-3 text-xl">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-800">
                                    Status jadwal ini tidak dapat diubah karena merupakan status final.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Status
                            Jadwal</label>
                        <div class="relative">
                            <select id="edit_status" name="status"
                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="AVAILABLE">Tersedia</option>
                                <option value="FULL">Penuh</option>
                                <option value="DEPARTED">Selesai</option>
                                <option value="UNAVAILABLE">Tidak Tersedia</option>
                                <option value="WEATHER_ISSUE">Masalah Cuaca</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-toggle-on text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Status "Penuh" dan "Selesai" merupakan status final yang
                            tidak dapat diubah kembali.</p>
                    </div>

                    <div class="mb-4">
                        <label for="edit_status_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Alasan Perubahan Status <span class="text-sm text-gray-500">(opsional)</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="edit_status_reason" name="status_reason"
                                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                placeholder="Contoh: Maintenance, Libur, Cuaca buruk, dll">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-comment-alt text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                        <button type="button" id="cancelEditBtn"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 px-5 rounded-lg transition shadow-sm">
                            Batal
                        </button>
                        <button type="submit" id="edit_submit_btn"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white py-2.5 px-5 rounded-lg transition shadow-sm">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Date Modal -->
    <div id="deleteDateModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md relative animate-modalFadeIn">
                <div class="flex justify-between items-center mb-4 pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-trash text-red-600 mr-2"></i>Konfirmasi Hapus
                    </h3>
                    <button type="button" id="closeDeleteModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="bg-red-50 p-5 rounded-lg mb-6 flex items-start border border-red-200 shadow-sm">
                    <div class="text-red-600 mr-3 text-xl">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <p class="font-medium text-red-800 mb-2">Perhatian!</p>
                        <p id="deleteConfirmText" class="text-gray-700 text-sm">Apakah Anda yakin ingin menghapus tanggal
                            ini?</p>
                        <p class="text-gray-500 text-xs mt-2">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <form id="deleteDateForm" action="#" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2">
                        <button type="button" id="cancelDeleteBtn"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 px-5 rounded-lg transition shadow-sm">
                            Batal
                        </button>
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white py-2.5 px-5 rounded-lg transition shadow-sm">
                            <i class="fas fa-trash mr-2"></i> Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<style>
    .animate-modalFadeIn {
        animation: modalFadeIn 0.3s ease-out forwards;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-out-alert {
        animation: fadeOut 5s forwards;
        animation-delay: 3s;
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
            visibility: hidden;
            margin-bottom: 0;
            padding: 0;
            height: 0;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded - Schedule dates management initialized');

        // ====== Schedule Operation Days ======
        // Get operation days array from schedule.days
        let scheduleDays = '{{ $schedule->days }}'.split(',');

        // Debug log - check raw data coming from template
        console.log('Raw scheduleDays from template:', '{{ $schedule->days }}');
        console.log('Parsed scheduleDays:', scheduleDays);

        // Clean up the scheduleDays array in case there are empty strings or non-numeric values
        scheduleDays = scheduleDays.filter(day => day.trim() !== '' && !isNaN(parseInt(day.trim())));
        console.log('Cleaned scheduleDays:', scheduleDays);

        // Convert to integers - VERY IMPORTANT for proper comparisons
        scheduleDays = scheduleDays.map(day => parseInt(day.trim(), 10));
        console.log('Final integer scheduleDays:', scheduleDays);

        // ====== Utility Functions ======
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                // Add fade-in effect
                setTimeout(() => {
                    modal.style.opacity = '1';
                }, 10);
                // Prevent body scrolling when modal is open
                document.body.style.overflow = 'hidden';
                console.log(`Modal ${modalId} opened`);
            } else {
                console.error(`Modal with ID ${modalId} not found`);
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                // Add fade-out effect
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.classList.add('hidden');
                    // Restore body scrolling
                    document.body.style.overflow = 'auto';
                }, 300);
                console.log(`Modal ${modalId} closed`);
            } else {
                console.error(`Modal with ID ${modalId} not found`);
            }
        }

        // Format date for display (e.g., from YYYY-MM-DD to DD/MM/YYYY)
        function formatDisplayDate(dateString) {
            if (!dateString) return '';

            try {
                const date = new Date(dateString);
                if (date instanceof Date && !isNaN(date)) {
                    return date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                }

                // Fall back to manual parsing if Date constructor doesn't work well
                const dateParts = dateString.split('-');
                if (dateParts.length === 3) {
                    // If in YYYY-MM-DD format
                    return `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                } else {
                    // If already in other format, return as is
                    return dateString;
                }
            } catch (e) {
                // If parsing fails, return original
                console.error('Error formatting date:', e);
                return dateString;
            }
        }

        // ====== Date Operation Day Validation ======

        // No conversion needed - we'll use the backend days directly (1-7)
        // and convert JavaScript days when needed
        console.log('Backend operation days:', scheduleDays);

        // Add day name labels for visual feedback
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const operationDayNames = scheduleDays.map(day => {
            // Day 7 in our system is Sunday (Minggu)
            if (day === 7) return 'Minggu';
            // Otherwise, it matches the array index (1=Monday/Senin, etc.)
            return dayNames[day];
        });

        console.log('Operation day names:', operationDayNames);

        // Show available operation days for calendar reference
        const calendarInfoElement = document.getElementById('calendar-info');
        if (calendarInfoElement) {
            calendarInfoElement.textContent = `Hari operasi: ${operationDayNames.join(', ')}`;
        }

        // Display operation day names next to the date fields for clarity
        const dateTypeInfoElements = document.querySelectorAll('.date-type-info');
        dateTypeInfoElements.forEach(element => {
            element.textContent = `Hari operasi: ${operationDayNames.join(', ')}`;
        });

        // Check if date is a valid operation day
        function isValidOperationDay(dateString) {
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return false;

                // JavaScript menggunakan 0-6 (di mana 0=Minggu sampai 6=Sabtu)
                // Konversi ke format backend 1-7 (di mana 1=Senin sampai 7=Minggu)
                const jsDay = date.getDay();
                const backendDay = jsDay === 0 ? 7 : jsDay;

                // Periksa apakah hari tersebut ada dalam hari operasi jadwal
                return scheduleDays.includes(backendDay);
            } catch (e) {
                console.error('Error validating date:', e);
                return false;
            }
        }

        // Konversi hari JavaScript (0-6) ke format backend (1-7)
        function jsToBackendDay(jsDay) {
            // JavaScript: 0=Minggu, 1=Senin, ..., 6=Sabtu
            // Backend: 1=Senin, 2=Selasa, ..., 7=Minggu
            return jsDay === 0 ? 7 : jsDay;
        }

        // Memastikan nilai scheduleDays selalu berupa integer
        function normalizeScheduleDays() {
            // Ubah semua string menjadi integer
            scheduleDays = scheduleDays.map(day => parseInt(day, 10));
            console.log('Normalized scheduleDays:', scheduleDays);
            return scheduleDays;
        }

        // Fungsi untuk memastikan nilai scheduleDays terisi dengan benar
        function validateScheduleDays() {
            // Periksa apakah ada nilai yang tidak valid
            let valid = true;

            if (!scheduleDays || !Array.isArray(scheduleDays)) {
                console.error('scheduleDays is not an array:', scheduleDays);
                valid = false;
            } else if (scheduleDays.length === 0) {
                console.error('scheduleDays is empty');
                valid = false;
            } else {
                // Cek apakah semua nilai valid (1-7)
                for (const day of scheduleDays) {
                    const dayNum = parseInt(day, 10);
                    if (isNaN(dayNum) || dayNum < 1 || dayNum > 7) {
                        console.error('Invalid day value in scheduleDays:', day);
                        valid = false;
                    }
                }
            }

            // Jika tidak valid, set default (e.g., Senin & Jumat)
            if (!valid) {
                console.warn('Setting default scheduleDays to [1, 5]');
                scheduleDays = [1, 5];
            }

            return normalizeScheduleDays();
        }

        // Get day name for a date
        function getDayName(dateString) {
            try {
                const date = typeof dateString === 'string' ? new Date(dateString) : dateString;
                if (isNaN(date.getTime())) return '';
                return dayNames[date.getDay()];
            } catch (e) {
                console.error('Error getting day name:', e);
                return '';
            }
        }

        // Enhanced validate a date input with day name feedback
        function validateDateInput(inputElement, warningElement, dayDisplayElement) {
            const dateValue = inputElement.value;
            if (!dateValue) {
                if (warningElement) warningElement.classList.add('hidden');
                if (dayDisplayElement) dayDisplayElement.textContent = '';
                inputElement.classList.remove('border-red-500', 'border-green-500');
                return true;
            }

            // Always show the day name for the selected date
            const dayName = getDayName(dateValue);
            if (dayDisplayElement) {
                dayDisplayElement.textContent = `${dayName}`;
            }

            if (!isValidOperationDay(dateValue)) {
                // Show warning
                if (warningElement) {
                    warningElement.textContent =
                        `Tanggal ini (${dayName}) tidak sesuai dengan hari operasi kapal.`;
                    warningElement.classList.remove('hidden');
                }
                if (dayDisplayElement) {
                    dayDisplayElement.classList.remove('bg-green-100', 'text-green-800');
                    dayDisplayElement.classList.add('bg-red-100', 'text-red-800');
                }
                inputElement.classList.add('border-red-500');
                inputElement.classList.remove('border-green-500');
                return false;
            } else {
                // Clear warning
                if (warningElement) {
                    warningElement.classList.add('hidden');
                }
                if (dayDisplayElement) {
                    dayDisplayElement.classList.remove('bg-red-100', 'text-red-800');
                    dayDisplayElement.classList.add('bg-green-100', 'text-green-800');
                }
                inputElement.classList.remove('border-red-500');
                inputElement.classList.add('border-green-500');
                return true;
            }
        }

        // Setup function for all date inputs
        function setupDateInputs() {
            const dateInputs = document.querySelectorAll('.date-input');
            dateInputs.forEach(input => {
                const inputId = input.id;
                const warningId = inputId + '_warning';
                const warningElement = document.getElementById(warningId);

                // Create or find day display element
                let dayDisplayId = inputId + '_day';
                let dayDisplayElement = document.getElementById(dayDisplayId);

                if (!dayDisplayElement) {
                    // Create new day display element if it doesn't exist
                    dayDisplayElement = document.createElement('span');
                    dayDisplayElement.id = dayDisplayId;
                    dayDisplayElement.className = 'ml-2 px-2 py-1 rounded font-medium';
                    input.parentNode.insertBefore(dayDisplayElement, warningElement || input
                        .nextSibling);
                }

                // Add change event listener for validation
                input.addEventListener('change', function() {
                    validateDateInput(this, warningElement, dayDisplayElement);
                });

                // Validate on page load if date already has a value
                if (input.value) {
                    validateDateInput(input, warningElement, dayDisplayElement);
                }

                // Set minimum date to today
                input.min = new Date().toISOString().split('T')[0];
            });
        }

        // Initialize date inputs with validation
        setupDateInputs();

        // ====== Visualize valid dates in range ======
        function updateRangeDatePreview() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const previewElement = document.getElementById('range_date_preview');

            if (!startDate || !endDate || !previewElement) return;

            if (!startDate.value || !endDate.value) {
                previewElement.innerHTML = '';
                return;
            }

            // Convert to date objects
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);

            if (isNaN(start.getTime()) || isNaN(end.getTime()) || start > end) {
                previewElement.innerHTML =
                    `<div class="flex items-center text-red-600 text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>Rentang tanggal tidak valid</span>
            </div>`;
                return;
            }

            // Find valid operation days in the range
            const validDates = [];
            let currentDate = new Date(start);

            while (currentDate <= end) {
                const dateStr = currentDate.toISOString().split('T')[0];
                if (isValidOperationDay(dateStr)) {
                    validDates.push(new Date(currentDate));
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }

            // Create preview
            if (validDates.length === 0) {
                previewElement.innerHTML =
                    `<div class="flex items-center text-yellow-600 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>Tidak ada tanggal operasi dalam rentang ini</span>
            </div>`;
            } else {
                previewElement.innerHTML = `
        <div class="text-sm">
            <div class="flex items-center text-blue-600 font-medium mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                <span>Tanggal operasi yang akan dibuat (${validDates.length}):</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-inner">
                ${validDates.map(date => `
                    <div class="text-xs bg-white p-2 rounded-lg border border-gray-200 flex items-center shadow-sm hover:bg-blue-50 transition-colors">
                        <i class="fas fa-calendar-day text-green-600 mr-1.5"></i>
                        ${formatDisplayDate(date.toISOString().split('T')[0])}
                        <span class="ml-1 text-blue-600">(${getDayName(date)})</span>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
            }
        }

        // Setup range date preview
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        if (startDateInput && endDateInput) {
            startDateInput.addEventListener('change', updateRangeDatePreview);
            endDateInput.addEventListener('change', updateRangeDatePreview);
        }

        // ====== Toast Notification System ======
        function showToast(message, type = 'info') {
            // Check if toast container exists, create if not
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col space-y-2';
                document.body.appendChild(toastContainer);
            }

            // Create toast element
            const toast = document.createElement('div');

            // Set classes based on type
            let bgColor, textColor, icon;
            switch (type) {
                case 'success':
                    bgColor = 'bg-green-100';
                    textColor = 'text-green-800';
                    icon = '<i class="fas fa-check-circle text-green-600 mr-2"></i>';
                    break;
                case 'error':
                    bgColor = 'bg-red-100';
                    textColor = 'text-red-800';
                    icon = '<i class="fas fa-exclamation-circle text-red-600 mr-2"></i>';
                    break;
                case 'warning':
                    bgColor = 'bg-yellow-100';
                    textColor = 'text-yellow-800';
                    icon = '<i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>';
                    break;
                default: // info
                    bgColor = 'bg-blue-100';
                    textColor = 'text-blue-800';
                    icon = '<i class="fas fa-info-circle text-blue-600 mr-2"></i>';
            }

            toast.className =
                `${bgColor} ${textColor} py-3 px-4 rounded-lg shadow-md flex items-start max-w-xs transform transition-all duration-300 opacity-0 translate-y-2`;
            toast.innerHTML = `
        <div class="flex-shrink-0">
            ${icon}
        </div>
        <div>
            ${message}
        </div>
        <button class="ml-auto text-gray-500 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
    `;

            // Add to container
            toastContainer.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
            }, 10);

            // Setup close button
            const closeBtn = toast.querySelector('button');
            closeBtn.addEventListener('click', () => {
                closeToast(toast);
            });

            // Auto close after 5 seconds
            setTimeout(() => {
                closeToast(toast);
            }, 5000);
        }

        function closeToast(toast) {
            // Animate out
            toast.classList.add('opacity-0', 'translate-y-2');

            // Remove from DOM after animation
            setTimeout(() => {
                toast.remove();

                // Remove container if empty
                const toastContainer = document.getElementById('toast-container');
                if (toastContainer && toastContainer.children.length === 0) {
                    toastContainer.remove();
                }
            }, 300);
        }

        // ====== Date type switcher for Add Modal ======
        const dateTypeSelect = document.getElementById('date_type');
        const singleDateFields = document.getElementById('singleDateFields');
        const rangeDateFields = document.getElementById('rangeDateFields');

        if (dateTypeSelect) {
            dateTypeSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                console.log(`Date type changed to: ${selectedValue}`);

                // Hide all fields first
                if (singleDateFields) singleDateFields.classList.add('hidden');
                if (rangeDateFields) rangeDateFields.classList.add('hidden');

                // Show only relevant fields with smooth transition
                if (selectedValue === 'single' && singleDateFields) {
                    singleDateFields.classList.remove('hidden');
                } else if (selectedValue === 'range' && rangeDateFields) {
                    rangeDateFields.classList.remove('hidden');
                    // Update preview when showing range fields
                    updateRangeDatePreview();
                }

                // Re-setup date inputs with enhanced validation after showing fields
                setTimeout(setupDateInputs, 100);
            });
        }

        // ====== Modal buttons and initialization ======
        const addDateBtn = document.getElementById('addDateBtn');
        const emptyAddDateBtn = document.getElementById('emptyAddDateBtn');
        const addDateForm = document.getElementById('addDateForm');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddBtn = document.getElementById('cancelAddBtn');

        // Function to prepare Add Modal
        function prepareAddModal() {
            // Make absolutely sure scheduleDays is properly set as integers
            scheduleDays = scheduleDays.map(day => parseInt(day.trim(), 10));
            console.log('PrepareAddModal - scheduleDays:', scheduleDays);

            // Setup date inputs with enhanced validation
            setupDateInputs();

            // If range fields are visible, update preview
            if (rangeDateFields && !rangeDateFields.classList.contains('hidden')) {
                updateRangeDatePreview();
            }
        }

        if (addDateBtn) {
            addDateBtn.addEventListener('click', function() {
                console.log('Add Date button clicked');
                openModal('addDateModal');
                prepareAddModal();
            });
        }

        if (emptyAddDateBtn) {
            emptyAddDateBtn.addEventListener('click', function() {
                console.log('Empty Add Date button clicked');
                openModal('addDateModal');
                prepareAddModal();
            });
        }

        if (closeAddModal) {
            closeAddModal.addEventListener('click', function() {
                closeModal('addDateModal');
            });
        }

        if (cancelAddBtn) {
            cancelAddBtn.addEventListener('click', function() {
                closeModal('addDateModal');
            });
        }

        // Form validation before submission
        function validateAddDateForm(e) {
            console.log('Validating form submission...');

            const dateType = document.getElementById('date_type').value;
            console.log('Date type:', dateType);

            if (dateType === 'single') {
                const singleDate = document.getElementById('single_date');
                if (!singleDate || !singleDate.value) {
                    e.preventDefault();
                    showToast('Silakan pilih tanggal', 'error');
                    return false;
                }

                // Validate operation day
                if (!isValidOperationDay(singleDate.value)) {
                    e.preventDefault();
                    showToast('Tanggal yang dipilih tidak sesuai dengan hari operasi kapal', 'error');
                    return false;
                }
            } else if (dateType === 'range') {
                const startDate = document.getElementById('start_date');
                const endDate = document.getElementById('end_date');

                if (!startDate || !startDate.value || !endDate || !endDate.value) {
                    e.preventDefault();
                    showToast('Silakan pilih tanggal mulai dan tanggal akhir', 'error');
                    return false;
                }

                // Check if there are any valid operation days in the range
                let hasValidDays = false;
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);

                if (!isNaN(start.getTime()) && !isNaN(end.getTime()) && start <= end) {
                    let currentDate = new Date(start);
                    while (currentDate <= end) {
                        if (isValidOperationDay(currentDate.toISOString().split('T')[0])) {
                            hasValidDays = true;
                            break;
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                }

                if (!hasValidDays) {
                    e.preventDefault();
                    showToast('Tidak ada hari operasi dalam rentang tanggal yang dipilih', 'error');
                    return false;
                }
            }

            console.log('Form validation passed');
            return true;
        }

        if (addDateForm) {
            addDateForm.addEventListener('submit', function(e) {
                return validateAddDateForm(e);
            });
        }

        // ====== Edit Date Modal ======
        const originalDateField = document.getElementById('original_date');
        const currentDateDisplay = document.getElementById('currentDateDisplay');

        // Get schedule ID from URL
        const urlParts = window.location.pathname.split('/');
        const scheduleIdIndex = urlParts.indexOf('schedules');
        const scheduleId = scheduleIdIndex !== -1 ? urlParts[scheduleIdIndex + 1] : null;

        // Edit buttons event handlers
        const editButtons = document.querySelectorAll('.edit-date-btn');
        const editDateForm = document.getElementById('editDateForm');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const dateStatusInfo = document.getElementById('date_status_info');
        const editSubmitBtn = document.getElementById('edit_submit_btn');

        editButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const dateId = this.getAttribute('data-id');
                const dateValue = this.getAttribute('data-date');
                const status = this.getAttribute('data-status');

                console.log('Edit date clicked - ID:', dateId, 'Date:', dateValue, 'Status:',
                    status);

                // Set form values
                document.getElementById('edit_date_id').value = dateId;
                if (originalDateField) {
                    originalDateField.value = dateValue;
                }

                // Display original date
                if (currentDateDisplay) {
                    const formattedDate = formatDisplayDate(dateValue);
                    const dayName = getDayName(dateValue);
                    currentDateDisplay.innerHTML =
                        `<i class="fas fa-calendar-day mr-1.5"></i> Jadwal tanggal <strong>${formattedDate}</strong> (${dayName})`;
                }

                // Check if status is final
                const isFinalStatus = ['FULL', 'DEPARTED'].includes(status);

                // Handle final status
                if (isFinalStatus) {
                    // Show info message
                    if (dateStatusInfo) {
                        dateStatusInfo.classList.remove('hidden');
                    }

                    // Disable the status dropdown and submit button
                    const statusDropdown = document.getElementById('edit_status');
                    if (statusDropdown) {
                        statusDropdown.disabled = true;
                        statusDropdown.value = status;
                    }

                    // Disable reason field
                    const reasonField = document.getElementById('edit_status_reason');
                    if (reasonField) {
                        reasonField.disabled = true;
                        reasonField.placeholder = 'Status final tidak dapat diubah';
                    }

                    // Disable submit button
                    if (editSubmitBtn) {
                        editSubmitBtn.disabled = true;
                        editSubmitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                } else {
                    // Hide info message
                    if (dateStatusInfo) {
                        dateStatusInfo.classList.add('hidden');
                    }

                    // Enable the form controls
                    const statusDropdown = document.getElementById('edit_status');
                    if (statusDropdown) {
                        statusDropdown.disabled = false;
                        statusDropdown.value = status;

                        // REMOVE THIS CODE BLOCK - it's removing FULL and DEPARTED options
                        // for (let i = statusDropdown.options.length - 1; i >= 0; i--) {
                        //     if (['FULL', 'DEPARTED'].includes(statusDropdown.options[i].value)) {
                        //         statusDropdown.remove(i);
                        //     }
                        // }
                    }

                    // Enable reason field
                    const reasonField = document.getElementById('edit_status_reason');
                    if (reasonField) {
                        reasonField.disabled = false;
                        reasonField.placeholder =
                            'Contoh: Maintenance, Libur, Cuaca buruk, dll';
                    }

                    // Enable submit button
                    if (editSubmitBtn) {
                        editSubmitBtn.disabled = false;
                        editSubmitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }

                // Set form action URL
                if (editDateForm && scheduleId) {
                    editDateForm.action = `/admin/schedules/${scheduleId}/dates/${dateId}`;
                }

                // Show modal
                openModal('editDateModal');
            });
        });

        // Modal close buttons
        if (closeEditModal) {
            closeEditModal.addEventListener('click', function() {
                closeModal('editDateModal');
            });
        }

        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', function() {
                closeModal('editDateModal');
            });
        }

        // ====== Delete Date Modal ======
        const deleteButtons = document.querySelectorAll('.delete-date-btn');
        const deleteDateForm = document.getElementById('deleteDateForm');
        const closeDeleteModal = document.getElementById('closeDeleteModal');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const deleteConfirmText = document.getElementById('deleteConfirmText');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const dateId = this.getAttribute('data-id');
                const dateText = this.getAttribute('data-date');
                console.log(`Delete button clicked for date: ${dateText}, id: ${dateId}`);

                if (deleteConfirmText) {
                    deleteConfirmText.innerHTML =
                        `Apakah Anda yakin ingin menghapus jadwal untuk tanggal <strong>${dateText}</strong>?`;
                }

                if (deleteDateForm) {
                    deleteDateForm.action = `/admin/schedules/dates/${dateId}`;
                }

                openModal('deleteDateModal');
            });
        });

        if (closeDeleteModal) {
            closeDeleteModal.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal('deleteDateModal');
            });
        }

        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal('deleteDateModal');
            });
        }

        // Pre-fill today's date for convenience
        const today = new Date().toISOString().split('T')[0];
        const allDateInputs = document.querySelectorAll('input[type="date"]');
        allDateInputs.forEach(input => {
            if (!input.value) {
                input.value = today;

                // Trigger change event to validate pre-filled date
                const changeEvent = new Event('change', {
                    bubbles: true
                });
                input.dispatchEvent(changeEvent);
            }
        });

        // Function to update date status badge
        function updateStatusBadge(statusElement, status) {
            if (!statusElement) return;

            // Remove all existing classes
            statusElement.classList.remove(
                'bg-green-100', 'text-green-800',
                'bg-red-100', 'text-red-800',
                'bg-yellow-100', 'text-yellow-800',
                'bg-blue-100', 'text-blue-800',
                'bg-purple-100', 'text-purple-800'
            );

            // Set new classes and icon based on status
            let iconHTML = '';
            switch (status) {
                case 'AVAILABLE':
                    statusElement.classList.add('bg-green-100', 'text-green-800');
                    iconHTML = '<i class="fas fa-check-circle mr-1"></i> Tersedia';
                    break;
                case 'UNAVAILABLE':
                    statusElement.classList.add('bg-red-100', 'text-red-800');
                    iconHTML = '<i class="fas fa-ban mr-1"></i> Tidak Tersedia';
                    break;
                case 'WEATHER_ISSUE':
                    statusElement.classList.add('bg-yellow-100', 'text-yellow-800');
                    iconHTML = '<i class="fas fa-cloud-rain mr-1"></i> Masalah Cuaca';
                    break;
                case 'FULL':
                    statusElement.classList.add('bg-blue-100', 'text-blue-800');
                    iconHTML = '<i class="fas fa-users mr-1"></i> Penuh';
                    break;
                case 'DEPARTED':
                    statusElement.classList.add('bg-purple-100', 'text-purple-800');
                    iconHTML = '<i class="fas fa-check-double mr-1"></i> Selesai';
                    break;
                default:
                    statusElement.classList.add('bg-gray-100', 'text-gray-800');
                    iconHTML = '<i class="fas fa-question-circle mr-1"></i> ' + status;
            }

            statusElement.innerHTML = iconHTML;
        }

        // Update status display when status changes in edit modal
        const editStatusDropdown = document.getElementById('edit_status');
        if (editStatusDropdown) {
            editStatusDropdown.addEventListener('change', function() {
                // Change reason field placeholder based on selected status
                const reasonField = document.getElementById('edit_status_reason');
                if (reasonField) {
                    switch (this.value) {
                        case 'WEATHER_ISSUE':
                            reasonField.placeholder = 'Contoh: Gelombang tinggi, angin kencang, dll';
                            break;
                        case 'UNAVAILABLE':
                            reasonField.placeholder = 'Contoh: Maintenance, libur, dll';
                            break;
                        default:
                            reasonField.placeholder = 'Alasan perubahan status (opsional)';
                    }
                }
            });
        }

        console.log('Schedule dates management initialization complete');
    });
</script>
