@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-3 md:mb-0">Kelola Jadwal Operasi</h1>
            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('admin.schedules.show', $schedule) }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
                <button type="button" id="addDateBtn"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Jadwal
                </button>
            </div>
        </div>

        <!-- Alerts Section -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md fade-out-alert"
                role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md fade-out-alert"
                role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Schedule Info Card -->
        <div class="mb-6">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>Informasi Jadwal
                    </h2>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Rute:</p>
                            <p class="font-medium text-gray-800">
                                @if (is_object($schedule->route))
                                    <i class="fas fa-route text-blue-500 mr-1"></i>
                                    {{ $schedule->route->origin }} - {{ $schedule->route->destination }}
                                @else
                                    <span class="text-red-500">Rute tidak tersedia</span>
                                @endif
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Kapal:</p>
                            <p class="font-medium text-gray-800">
                                @if (is_object($schedule->ferry))
                                    <i class="fas fa-ship text-blue-500 mr-1"></i>
                                    {{ $schedule->ferry->name }}
                                @else
                                    <span class="text-red-500">Kapal tidak tersedia</span>
                                @endif
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Waktu Keberangkatan:</p>
                            <p class="font-medium text-gray-800">
                                <i class="fas fa-clock text-green-500 mr-1"></i>
                                {{ $schedule->departure_time->format('H:i') }}
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Estimasi Tiba:</p>
                            <p class="font-medium text-gray-800">
                                <i class="fas fa-hourglass-end text-green-500 mr-1"></i>
                                {{ $schedule->arrival_time->format('H:i') }}
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Hari Operasi:</p>
                            <p class="font-medium text-gray-800">
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
                                <i class="fas fa-calendar-day text-indigo-500 mr-1"></i>
                                {{ implode(', ', $dayLabels) }}
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Status:</p>
                            <p class="font-medium">
                                @if ($schedule->status == 'ACTIVE')
                                    <span
                                        class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 flex items-center w-fit">
                                        <i class="fas fa-check-circle mr-1"></i> Aktif
                                    </span>
                                @elseif($schedule->status == 'DELAYED')
                                    <span
                                        class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-800 flex items-center w-fit">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Tertunda
                                    </span>
                                @elseif($schedule->status == 'FULL')
                                    <span
                                        class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 flex items-center w-fit">
                                        <i class="fas fa-users mr-1"></i> Penuh
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 flex items-center w-fit">
                                        <i class="fas fa-ban mr-1"></i> Dibatalkan
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="mb-6">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-filter text-indigo-500 mr-2"></i>Filter Tanggal
                    </h2>
                </div>
                <div class="p-4">
                    <form action="{{ url()->current() }}" method="GET" id="filterForm">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Bulan:</label>
                                <select id="month" name="month"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Semua Bulan</option>
                                    @foreach (range(1, 12) as $month)
                                        <option value="{{ $month }}"
                                            {{ request('month') == $month ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun:</label>
                                <select id="year" name="year"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Semua Tahun</option>
                                    @foreach (range(date('Y'), date('Y') + 1) as $year)
                                        <option value="{{ $year }}"
                                            {{ request('year') == $year ? 'selected' : '' }}>
                                            {{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
                                <select id="status" name="status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Semua Status</option>
                                    <option value="AVAILABLE" {{ request('status') == 'AVAILABLE' ? 'selected' : '' }}>
                                        Tersedia</option>
                                    <option value="UNAVAILABLE" {{ request('status') == 'UNAVAILABLE' ? 'selected' : '' }}>
                                        Tidak Tersedia</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                    class="w-full bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded transition-colors duration-200">
                                    <i class="fas fa-filter mr-2"></i> Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dates Table Card -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-calendar-alt text-green-500 mr-2"></i>Daftar Tanggal Jadwal
                </h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
                    Total: {{ $scheduleDates->total() ?? 0 }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-left text-sm">
                            <th class="py-3 px-4 font-medium border-b">#</th>
                            <th class="py-3 px-4 font-medium border-b">Tanggal</th>
                            <th class="py-3 px-4 font-medium border-b">Hari</th>
                            <th class="py-3 px-4 font-medium border-b">Penumpang</th>
                            <th class="py-3 px-4 font-medium border-b">Kendaraan</th>
                            <th class="py-3 px-4 font-medium border-b">Status</th>
                            <th class="py-3 px-4 font-medium border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scheduleDates ?? [] as $date)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4 border-b text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4 border-b text-sm font-medium">
                                    {{ is_object($date) && $date->date ? \Carbon\Carbon::parse($date->date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-4 border-b text-sm">
                                    {{ is_object($date) && $date->date ? \Carbon\Carbon::parse($date->date)->translatedFormat('l') : '-' }}
                                </td>
                                <td class="py-3 px-4 border-b text-sm">
                                    @if (is_object($schedule->ferry))
                                        <div class="flex items-center">
                                            <i class="fas fa-users text-blue-500 mr-2"></i>
                                            <span
                                                class="font-medium">{{ is_object($date) ? $date->passenger_count ?? 0 : 0 }}</span>
                                            /
                                            <span>{{ $schedule->ferry->capacity_passenger }}</span>
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
                                            <div class="w-16 h-2 ml-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $passengerPercentage > 80 ? 'bg-red-500' : 'bg-blue-500' }}"
                                                    style="width: {{ $passengerPercentage }}%"></div>
                                            </div>
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
                                            <div class="flex items-center text-xs">
                                                <i class="fas fa-motorcycle text-gray-500 mr-1"></i>
                                                <span
                                                    class="font-medium">{{ is_object($date) ? $date->motorcycle_count ?? 0 : 0 }}</span>
                                                /
                                                <span>{{ $schedule->ferry->capacity_vehicle_motorcycle }}</span>
                                            </div>
                                            <div class="flex items-center text-xs">
                                                <i class="fas fa-car text-gray-500 mr-1"></i>
                                                <span class="font-medium">{{ $date->car_count ?? 0 }}</span> /
                                                <span>{{ $schedule->ferry->capacity_vehicle_car }}</span>
                                            </div>
                                            <div class="flex items-center text-xs">
                                                <i class="fas fa-bus text-gray-500 mr-1"></i>
                                                <span class="font-medium">{{ $date->bus_count ?? 0 }}</span> /
                                                <span>{{ $schedule->ferry->capacity_vehicle_bus }}</span>
                                            </div>
                                            <div class="flex items-center text-xs">
                                                <i class="fas fa-truck text-gray-500 mr-1"></i>
                                                <span class="font-medium">{{ $date->truck_count ?? 0 }}</span> /
                                                <span>{{ $schedule->ferry->capacity_vehicle_truck }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 border-b text-sm">
                                    @if ($date->status == 'AVAILABLE')
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 flex items-center w-fit">
                                            <i class="fas fa-check-circle mr-1"></i> Tersedia
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 flex items-center w-fit">
                                            <i class="fas fa-ban mr-1"></i> Tidak Tersedia
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 border-b text-sm">
                                    <div class="flex space-x-1">
                                        <button
                                            class="edit-date-btn bg-yellow-100 text-yellow-600 hover:bg-yellow-200 p-1.5 rounded"
                                            data-id="{{ $date->id }}" data-date="{{ $date->date }}"
                                            data-status="{{ $date->status }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            class="delete-date-btn bg-red-100 text-red-600 hover:bg-red-200 p-1.5 rounded"
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
                                <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-calendar-times text-5xl text-gray-300 mb-3"></i>
                                        <p class="text-lg font-medium mb-2">Tidak ada tanggal terjadwal</p>
                                        <p class="text-sm mb-4">Tambahkan jadwal baru untuk kapal ini</p>
                                        <button id="emptyAddDateBtn"
                                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors duration-200">
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

    <!-- Add Date Modal -->
    <div id="addDateModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-xl relative animate-modalFadeIn">
                <div class="flex justify-between items-center mb-4 pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>Tambah Jadwal
                    </h3>
                    <button type="button" id="closeAddModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Operation Days Info -->
                <div class="bg-blue-50 p-3 rounded-lg mb-4 border border-blue-200">
                    <div class="flex">
                        <div class="text-blue-500 mr-2">
                            <i class="fas fa-info-circle text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-800">
                                Hari operasi: <strong>{{ implode(', ', $dayLabels) }}</strong>
                            </p>
                            <p class="text-xs text-blue-600 mt-1">Hanya tanggal yang jatuh pada hari operasi yang dapat
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
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="single">Tanggal Tunggal</option>
                                <option value="range">Rentang Tanggal</option>
                                <option value="multiple">Pilih Beberapa Tanggal</option>
                                <option value="days">Pilih Hari Tertentu</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Single date fields section -->
                    <div id="singleDateFields" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div>
                            <label for="single_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <div class="flex items-center">
                                <div class="relative flex-grow">
                                    <input type="date" id="single_date" name="single_date"
                                        class="date-input w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <span id="single_date_day" class="ml-2 px-2 py-1 rounded font-medium"></span>
                            </div>
                            <div id="single_date_warning"
                                class="hidden mt-2 text-xs text-red-500 bg-red-50 p-2 rounded border border-red-200"></div>
                            <div class="date-type-info text-xs text-blue-600 mt-1"></div>
                        </div>
                    </div>

                    <!-- Range date fields section -->
                    <!-- Perbaikan 1: Tambahkan fields untuk mode "days" yang hilang -->
                    <!-- Tambahkan section ini setelah multipleDateFields div di form addDateModal -->

                    <div id="daysFields" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Hari dalam Seminggu</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <div class="flex items-center">
                                    <input type="checkbox" id="day_1" name="days[]" value="1" class="mr-2">
                                    <label for="day_1">Senin</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="day_2" name="days[]" value="2" class="mr-2">
                                    <label for="day_2">Selasa</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="day_3" name="days[]" value="3" class="mr-2">
                                    <label for="day_3">Rabu</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="day_4" name="days[]" value="4" class="mr-2">
                                    <label for="day_4">Kamis</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="day_5" name="days[]" value="5" class="mr-2">
                                    <label for="day_5">Jumat</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="day_6" name="days[]" value="6" class="mr-2">
                                    <label for="day_6">Sabtu</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="day_7" name="days[]" value="7" class="mr-2">
                                    <label for="day_7">Minggu</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="days_start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Mulai</label>
                            <div class="relative">
                                <input type="date" id="days_start_date" name="days_start_date"
                                    class="date-input w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="days_end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Akhir</label>
                            <div class="relative">
                                <input type="date" id="days_end_date" name="days_end_date"
                                    class="date-input w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Jadwal akan dibuat untuk hari-hari yang dipilih dalam
                                rentang tanggal ini.</p>
                        </div>
                    </div>

                    <!-- Preview of valid operation days in the range -->
                    <div id="range_date_preview" class="bg-white rounded p-3 border border-gray-200"></div>
            </div>

            <!-- Multiple date fields section -->
            <div id="multipleDateFields" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Tanggal (3 Bulan Ke Depan)</label>

                <!-- Calendar Legend -->
                <div class="flex flex-wrap items-center text-xs mb-3 bg-white p-2 rounded-lg border border-gray-200">
                    <div class="flex items-center mr-4 mb-1">
                        <div class="w-3 h-3 bg-gray-300 rounded mr-1"></div>
                        <span>Bukan hari operasi</span>
                    </div>
                    <div class="flex items-center mr-4 mb-1">
                        <div class="w-3 h-3 border border-green-200 rounded mr-1"></div>
                        <span>Hari operasi</span>
                    </div>
                    <div class="flex items-center mb-1">
                        <div class="w-3 h-3 bg-blue-500 rounded mr-1"></div>
                        <span>Tanggal dipilih</span>
                    </div>
                </div>

                <div id="date_calendar" class="bg-white p-3 rounded-lg border border-gray-200 max-h-64 overflow-y-auto">
                    <div id="calendar_dates" class="grid grid-cols-7 gap-1">
                        <!-- Calendar dates will be generated by JS -->
                    </div>
                    <input type="hidden" id="selected_dates" name="selected_dates" value="">
                </div>

                <!-- Preview of selected dates -->
                <div id="selected_dates_preview" class="mt-3 p-2 bg-white rounded-lg border border-gray-200 min-h-[50px]">
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="relative">
                    <select id="status" name="status"
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="AVAILABLE">Tersedia</option>
                        <option value="UNAVAILABLE">Tidak Tersedia</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-toggle-on text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                <button type="button" id="cancelAddBtn"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded transition-colors duration-200">
                    Batal
                </button>
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
            </form>
        </div>
    </div>
    </div>

    <!-- Edit Date Modal - Simplified Version (No Date Editing) -->
    <div id="editDateModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md relative animate-modalFadeIn">
                <div class="flex justify-between items-center mb-4 pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-edit text-yellow-500 mr-2"></i>Edit Status Jadwal
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

                    <div class="bg-yellow-50 p-3 rounded-lg mb-4 border border-yellow-200">
                        <div class="flex">
                            <div class="text-yellow-500 mr-2">
                                <i class="fas fa-info-circle text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-yellow-800" id="currentDateDisplay">
                                    Jadwal:
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Status
                            Jadwal</label>
                        <div class="relative">
                            <select id="edit_status" name="status"
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="AVAILABLE">Tersedia</option>
                                <option value="UNAVAILABLE">Tidak Tersedia</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-toggle-on text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_status_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Alasan Perubahan Status <span class="text-sm text-gray-500">(opsional)</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="edit_status_reason" name="status_reason"
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Contoh: Maintenance, Libur, dll">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-comment-alt text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                        <button type="button" id="cancelEditBtn"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded transition-colors duration-200">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
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
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md relative animate-modalFadeIn">
                <div class="flex justify-between items-center mb-4 pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-trash text-red-500 mr-2"></i>Konfirmasi Hapus
                    </h3>
                    <button type="button" id="closeDeleteModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="bg-red-50 p-4 rounded-lg mb-6 flex items-start border border-red-200">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3 mt-0.5"></i>
                    <div>
                        <p class="font-medium text-red-800 mb-1">Perhatian!</p>
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
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded transition-colors duration-200">
                            <i class="fas fa-trash mr-1"></i> Hapus
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

        // Convert to integers
        scheduleDays = scheduleDays.map(day => parseInt(day.trim()));
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

        // Convert schedule days to JS days (0=Sunday to 6=Saturday)
        // The system uses 1=Monday through 7=Sunday, JS uses 0=Sunday through 6=Saturday
        const jsOperationDays = scheduleDays.map(day => {
            // Convert day 7 (Sunday in our system) to 0 (Sunday in JS)
            return day === 7 ? 0 : day;
        });

        console.log('JS operation days:', jsOperationDays);

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

                // Get day of week as 1 (Monday) to 7 (Sunday) to match backend (ISO format)
                let dayOfWeek = date.getDay(); // 0=Sunday, 1=Monday, etc.

                // Convert from JS day to ISO day format (0→7, 1→1, 2→2, etc.)
                dayOfWeek = dayOfWeek === 0 ? 7 : dayOfWeek;

                // Convert scheduleDays to strings for comparison because backend compares as strings
                const operationDaysAsStrings = scheduleDays.map(day => day.toString());

                return operationDaysAsStrings.includes(dayOfWeek.toString());
            } catch (e) {
                console.error('Error validating date:', e);
                return false;
            }
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

        // Add day display elements next to date inputs
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

        // Setup date inputs with enhanced validation
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
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-36 overflow-y-auto bg-gray-50 p-2 rounded border border-gray-200">
                        ${validDates.map(date => `
                            <div class="text-xs bg-white p-1.5 rounded border border-gray-200 flex items-center">
                                <i class="fas fa-calendar-day text-green-500 mr-1.5"></i>
                                ${formatDisplayDate(date.toISOString().split('T')[0])}
                                <span class="ml-1 text-green-600">(${getDayName(date)})</span>
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

        // ====== Calendar for Multiple Date Selection ======
        function generateCalendar() {
            console.log('Generating calendar...');
            const calendarDates = document.getElementById('calendar_dates');
            const selectedDatesInput = document.getElementById('selected_dates');
            const selectedDatesPreview = document.getElementById('selected_dates_preview');

            if (!calendarDates || !selectedDatesInput) {
                console.error('Calendar elements not found');
                return;
            }

            // Clear existing calendar
            calendarDates.innerHTML = '';

            // Log operation days for debugging
            console.log('Operation days (JS format):', jsOperationDays);

            // Selected dates collection
            let selectedDates = [];

            // Helper function to update selected dates preview
            function updateSelectedDatesPreview() {
                if (!selectedDatesPreview) return;

                if (selectedDates.length === 0) {
                    selectedDatesPreview.innerHTML =
                        '<p class="text-gray-500 text-xs text-center py-2">Belum ada tanggal yang dipilih</p>';
                    return;
                }

                selectedDatesPreview.innerHTML = `
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-medium text-gray-700">Tanggal yang dipilih (${selectedDates.length}):</p>
                        <button type="button" id="clearAllDates" class="text-red-600 hover:text-red-800 text-xs">
                            <i class="fas fa-trash-alt mr-1"></i>Hapus Semua
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto p-2 bg-gray-50 rounded border border-gray-200">
                        ${selectedDates.map(dateStr => {
                            const date = new Date(dateStr);
                            return `
                                <div class="text-xs bg-blue-100 text-blue-800 rounded px-2 py-1 flex items-center">
                                    <i class="fas fa-calendar-check text-blue-500 mr-1"></i>
                                    ${formatDisplayDate(dateStr)}
                                    <span class="mx-1">(${getDayName(date)})</span>
                                    <button type="button" class="remove-date text-red-500 hover:text-red-700" data-date="${dateStr}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                        }).join('')}
                    </div>
                `;

                // Add event listener to clear all button
                const clearAllBtn = document.getElementById('clearAllDates');
                if (clearAllBtn) {
                    clearAllBtn.addEventListener('click', function() {
                        // Clear all selected dates
                        selectedDates = [];
                        selectedDatesInput.value = '';

                        // Clear all selected cells in calendar
                        document.querySelectorAll('.date-cell.bg-blue-500').forEach(cell => {
                            cell.classList.remove('bg-blue-500', 'text-white');
                        });

                        // Update preview
                        updateSelectedDatesPreview();
                    });
                }

                // Add event listeners to remove buttons
                document.querySelectorAll('.remove-date').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const dateToRemove = this.getAttribute('data-date');
                        selectedDates = selectedDates.filter(d => d !== dateToRemove);

                        // Update hidden input with selected dates
                        selectedDatesInput.value = selectedDates.join(',');

                        // Update preview
                        updateSelectedDatesPreview();

                        // Update calendar cell styling
                        const cell = document.querySelector(
                            `.date-cell[data-date="${dateToRemove}"]`);
                        if (cell) {
                            cell.classList.remove('bg-blue-500', 'text-white');
                        }
                    });
                });
            }

            // Add days of week header with highlighting for operation days
            const weekdayHeader = document.createElement('div');
            weekdayHeader.className = 'grid grid-cols-7 gap-1 text-center mb-2 border-b pb-2';

            ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'].forEach((day, index) => {
                // Convert display index to JS day (0=Sunday, 1=Monday, etc.)
                // index 0 = Sen (Monday) which is 1 in JS
                // index 6 = Min (Sunday) which is 0 in JS
                const jsDay = index === 6 ? 0 : index + 1;
                const isOperationDay = jsOperationDays.includes(jsDay);

                const dayElement = document.createElement('div');
                dayElement.className =
                    `text-xs font-medium p-1 rounded ${isOperationDay ? 'text-green-700 bg-green-50' : 'text-gray-400'}`;
                dayElement.textContent = day;
                weekdayHeader.appendChild(dayElement);
            });

            calendarDates.appendChild(weekdayHeader);

            // Generate dates for next 3 months
            const today = new Date();
            const endDate = new Date(today);
            endDate.setMonth(today.getMonth() + 3);

            let currentDate = new Date(today);
            currentDate.setHours(0, 0, 0, 0);

            // Get the first day of the month and adjust for calendar display
            const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const firstDayOfWeek = firstDayOfMonth.getDay(); // 0=Sunday, 1=Monday, etc.

            // Adjust for our calendar (Monday as first day)
            const dayOffset = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

            // Add empty cells for days before the first day of month
            for (let i = 0; i < dayOffset; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'h-8 text-center';
                calendarDates.appendChild(emptyCell);
            }

            // Generate calendar cells for each date
            while (currentDate <= endDate) {
                const dateCell = document.createElement('div');
                const dateString = currentDate.toISOString().split('T')[0];
                const dayOfWeek = currentDate.getDay(); // 0=Sunday, 1=Monday, etc.

                // Check if this date falls on a valid operation day
                const isOperationDay = jsOperationDays.includes(dayOfWeek);

                // Check if date is in the past
                const isPastDate = currentDate < today;

                // Debug log for days
                if (currentDate.getDate() === 1 || currentDate.getDate() === 15) {
                    console.log(
                        `Day check: ${dateString}, day of week: ${dayOfWeek}, is operation day: ${isOperationDay}`
                    );
                }

                dateCell.textContent = currentDate.getDate();

                if (isOperationDay && !isPastDate) {
                    dateCell.className =
                        'date-cell h-8 flex items-center justify-center rounded cursor-pointer hover:bg-blue-100 text-center text-sm border border-green-200';
                    dateCell.setAttribute('data-date', dateString);
                    dateCell.setAttribute('title', `${getDayName(currentDate)} - Hari Operasi`);

                    dateCell.addEventListener('click', function() {
                        const dateValue = this.getAttribute('data-date');

                        // Toggle selection
                        if (this.classList.contains('bg-blue-500')) {
                            this.classList.remove('bg-blue-500', 'text-white');
                            selectedDates = selectedDates.filter(d => d !== dateValue);
                        } else {
                            this.classList.add('bg-blue-500', 'text-white');
                            selectedDates.push(dateValue);
                        }

                        // Sort selected dates
                        selectedDates.sort();

                        // Update hidden input with selected dates
                        selectedDatesInput.value = selectedDates.join(',');

                        // Update preview
                        updateSelectedDatesPreview();
                    });
                } else if (isPastDate) {
                    // Past dates are disabled
                    dateCell.className =
                        'h-8 flex items-center justify-center text-gray-300 text-center text-sm bg-gray-100 opacity-50';
                    dateCell.setAttribute('title', `${getDayName(currentDate)} - Tanggal telah lewat`);
                } else {
                    // Non-operation days
                    dateCell.className =
                        'h-8 flex items-center justify-center text-gray-300 text-center text-sm bg-gray-100';
                    dateCell.setAttribute('title', `${getDayName(currentDate)} - Bukan hari operasi`);
                }

                calendarDates.appendChild(dateCell);

                // Move to next day
                currentDate.setDate(currentDate.getDate() + 1);

                // If we're at the start of a new month, add a month divider
                if (currentDate.getDate() === 1) {
                    const monthDivider = document.createElement('div');
                    monthDivider.className =
                        'col-span-7 text-center py-2 my-2 border-t border-b text-xs font-medium text-gray-600 bg-gray-50';

                    const monthNames = [
                        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
                    ];
                    monthDivider.innerHTML =
                        `<i class="fas fa-calendar-alt mr-1"></i> ${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

                    calendarDates.appendChild(monthDivider);

                    // Reset calendar alignment for the new month
                    const newMonthFirstDay = currentDate.getDay();
                    const newDayOffset = newMonthFirstDay === 0 ? 6 : newMonthFirstDay - 1;

                    for (let i = 0; i < newDayOffset; i++) {
                        const emptyCell = document.createElement('div');
                        emptyCell.className = 'h-8 text-center';
                        calendarDates.appendChild(emptyCell);
                    }
                }
            }

            // Initialize preview
            updateSelectedDatesPreview();
            console.log('Calendar generation complete');
        }

        // ====== Date type switcher for Add Modal ======
        const dateTypeSelect = document.getElementById('date_type');
        const singleDateFields = document.getElementById('singleDateFields');
        const rangeDateFields = document.getElementById('rangeDateFields');
        const daysFields = document.getElementById('daysFields');
        const multipleDateFields = document.getElementById('multipleDateFields');

        if (dateTypeSelect) {
            dateTypeSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                console.log(`Date type changed to: ${selectedValue}`);

                // Hide all fields first
                if (singleDateFields) singleDateFields.classList.add('hidden');
                if (rangeDateFields) rangeDateFields.classList.add('hidden');
                if (daysFields) daysFields.classList.add('hidden');
                if (multipleDateFields) multipleDateFields.classList.add('hidden');

                // Show only relevant fields with smooth transition
                if (selectedValue === 'single' && singleDateFields) {
                    singleDateFields.classList.remove('hidden');
                } else if (selectedValue === 'range' && rangeDateFields) {
                    rangeDateFields.classList.remove('hidden');
                    // Update preview when showing range fields
                    updateRangeDatePreview();
                } else if (selectedValue === 'days' && daysFields) {
                    daysFields.classList.remove('hidden');
                } else if (selectedValue === 'multiple' && multipleDateFields) {
                    multipleDateFields.classList.remove('hidden');
                    generateCalendar(); // Generate calendar when this view is selected
                }

                // Re-setup date inputs with enhanced validation after showing fields
                setTimeout(setupDateInputs, 100);
            });
        }

        // Add operation days header when modal is opened
        const addDateBtn = document.getElementById('addDateBtn');
        const emptyAddDateBtn = document.getElementById('emptyAddDateBtn');
        const addDateForm = document.getElementById('addDateForm');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddBtn = document.getElementById('cancelAddBtn');

        function prepareAddModal() {
            // Setup date inputs with enhanced validation
            setupDateInputs();

            // If range fields are visible, update preview
            if (rangeDateFields && !rangeDateFields.classList.contains('hidden')) {
                updateRangeDatePreview();
            }

            // If calendar is visible, generate it
            if (multipleDateFields && !multipleDateFields.classList.contains('hidden')) {
                generateCalendar();
            }

            // Setup days mode if visible
            if (daysFields && !daysFields.classList.contains('hidden')) {
                setupDaysMode();
            } else {
                // Initialize days checkboxes anyway for when they become visible
                setupDaysCheckboxes();
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
        if (addDateForm) {
            // Add debug output to help troubleshoot
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    console.log('Submit button clicked');

                    // Debug info about form
                    const dateType = dateTypeSelect ? dateTypeSelect.value : '';
                    console.log('Current date_type:', dateType);

                    // For 'days' type, check checkboxes
                    if (dateType === 'days') {
                        const daysChecked = document.querySelectorAll('input[name="days[]"]:checked');
                        console.log('Days checked:', Array.from(daysChecked).map(cb => cb.value));
                    }
                });
            }

            addDateForm.addEventListener('submit', function(e) {
                console.log('Form submitted');

                // Validate form based on date type
                const dateType = dateTypeSelect ? dateTypeSelect.value : '';
                console.log('Validating form with date_type:', dateType);

                if (dateType === 'single') {
                    const singleDate = document.getElementById('single_date');
                    const singleDateWarning = document.getElementById('single_date_warning');
                    const singleDateDay = document.getElementById('single_date_day');

                    if (!singleDate.value) {
                        e.preventDefault();
                        showToast('Silakan pilih tanggal', 'error');
                        return false;
                    }

                    if (!validateDateInput(singleDate, singleDateWarning, singleDateDay)) {
                        e.preventDefault();
                        showToast('Tanggal yang dipilih tidak sesuai dengan hari operasi kapal',
                            'error');
                        return false;
                    }
                } else if (dateType === 'range') {
                    const startDate = document.getElementById('start_date');
                    const endDate = document.getElementById('end_date');
                    const startDateWarning = document.getElementById('start_date_warning');
                    const endDateWarning = document.getElementById('end_date_warning');
                    const startDateDay = document.getElementById('start_date_day');
                    const endDateDay = document.getElementById('end_date_day');

                    if (!startDate.value || !endDate.value) {
                        e.preventDefault();
                        showToast('Silakan pilih tanggal mulai dan tanggal akhir', 'error');
                        return false;
                    }

                    if (!validateDateInput(startDate, startDateWarning, startDateDay)) {
                        e.preventDefault();
                        showToast('Tanggal mulai tidak sesuai dengan hari operasi kapal', 'error');
                        return false;
                    }

                    // Check if there are any valid operation days in the range
                    const start = new Date(startDate.value);
                    const end = new Date(endDate.value);
                    let hasValidDays = false;

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
                } else if (dateType === 'days') {
                    const daysChecked = document.querySelectorAll('input[name="days[]"]:checked');
                    const daysStartDate = document.getElementById('days_start_date');
                    const daysEndDate = document.getElementById('days_end_date');

                    if (daysChecked.length === 0) {
                        e.preventDefault();
                        showToast('Silakan pilih minimal satu hari', 'error');
                        console.error('No days selected');
                        return false;
                    }

                    if (!daysStartDate.value || !daysEndDate.value) {
                        e.preventDefault();
                        showToast('Silakan pilih tanggal mulai dan tanggal akhir', 'error');
                        console.error('Missing date range');
                        return false;
                    }

                    // Validate start date is before or equal to end date
                    const startDate = new Date(daysStartDate.value);
                    const endDate = new Date(daysEndDate.value);

                    if (startDate > endDate) {
                        e.preventDefault();
                        showToast('Tanggal mulai harus sebelum atau sama dengan tanggal akhir',
                            'error');
                        console.error('Start date after end date');
                        return false;
                    }

                    // Console log all form data for debugging
                    console.log('Form data for days mode:');
                    console.log('- date_type:', dateType);
                    console.log('- days:', Array.from(daysChecked).map(cb => cb.value));
                    console.log('- days_start_date:', daysStartDate.value);
                    console.log('- days_end_date:', daysEndDate.value);
                    console.log('- status:', document.getElementById('status').value);
                } else if (dateType === 'multiple') {
                    const selectedDates = document.getElementById('selected_dates').value;

                    if (!selectedDates) {
                        e.preventDefault();
                        showToast('Silakan pilih minimal satu tanggal', 'error');
                        return false;
                    }
                }

                // Form looks valid, allow submission
                console.log('Form validation passed, submitting...');
                return true;
            });
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
                    icon = '<i class="fas fa-check-circle text-green-500 mr-2"></i>';
                    break;
                case 'error':
                    bgColor = 'bg-red-100';
                    textColor = 'text-red-800';
                    icon = '<i class="fas fa-exclamation-circle text-red-500 mr-2"></i>';
                    break;
                case 'warning':
                    bgColor = 'bg-yellow-100';
                    textColor = 'text-yellow-800';
                    icon = '<i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>';
                    break;
                default: // info
                    bgColor = 'bg-blue-100';
                    textColor = 'text-blue-800';
                    icon = '<i class="fas fa-info-circle text-blue-500 mr-2"></i>';
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

        // ====== Edit Date Modal - Simplified (No Date Editing) ======
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
                        `<i class="fas fa-calendar-day mr-1"></i> Jadwal tanggal <strong>${formattedDate}</strong> (${dayName})`;
                }

                // Set status
                document.getElementById('edit_status').value = status;

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

        // Auto-hide alerts after 5 seconds
        const autoHideAlerts = document.querySelectorAll('.fade-out-alert');
        autoHideAlerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });

        // Set up the days checkboxes based on schedule operation days
        function setupDaysCheckboxes() {
            // Check if schedule days data is available
            if (scheduleDays && scheduleDays.length > 0) {
                console.log('Setting up days checkboxes with scheduleDays:', scheduleDays);

                // Clear all checkboxes first
                document.querySelectorAll('input[name="days[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                });

                // Pre-check the days that match schedule operation days
                scheduleDays.forEach(day => {
                    // Make sure day is treated as a number for comparison
                    const dayNum = parseInt(day, 10);
                    const checkbox = document.getElementById(`day_${dayNum}`);
                    if (checkbox) {
                        console.log(`Checking day_${dayNum}`);
                        checkbox.checked = true;
                    }
                });

                // Make sure at least one checkbox is checked
                const hasChecked = Array.from(document.querySelectorAll('input[name="days[]"]')).some(cb => cb
                    .checked);
                if (!hasChecked && document.querySelectorAll('input[name="days[]"]').length > 0) {
                    // If none are checked, check the first one
                    document.querySelector('input[name="days[]"]').checked = true;
                }
            }
        }

        // Call setup function when form is shown
        function setupDaysMode() {
            console.log('Setting up days mode...');
            setupDaysCheckboxes();

            // Add change event listener to ensure at least one day is always selected
            document.querySelectorAll('input[name="days[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // After this checkbox change, check if at least one is still checked
                    const hasChecked = Array.from(document.querySelectorAll(
                        'input[name="days[]"]')).some(cb => cb.checked);
                    if (!hasChecked) {
                        // If the user unchecked the last checked box, show warning and recheck it
                        showToast('Minimal satu hari harus dipilih', 'warning');
                        this.checked = true;
                    }
                });
            });

            // Initialize date fields
            const daysStartDate = document.getElementById('days_start_date');
            const daysEndDate = document.getElementById('days_end_date');

            if (daysStartDate && daysEndDate) {
                // Set minimum dates
                const today = new Date().toISOString().split('T')[0];
                daysStartDate.min = today;
                daysEndDate.min = today;

                // Set default values if not set
                if (!daysStartDate.value) {
                    daysStartDate.value = today;
                }

                if (!daysEndDate.value) {
                    const defaultEndDate = new Date();
                    defaultEndDate.setDate(defaultEndDate.getDate() + 30);
                    daysEndDate.value = defaultEndDate.toISOString().split('T')[0];
                }

                // Add validation
                daysStartDate.addEventListener('change', function() {
                    if (daysEndDate.value && this.value > daysEndDate.value) {
                        showToast('Tanggal mulai tidak boleh setelah tanggal akhir', 'error');
                        this.value = daysEndDate.value;
                    }
                });

                daysEndDate.addEventListener('change', function() {
                    if (daysStartDate.value && this.value < daysStartDate.value) {
                        showToast('Tanggal akhir tidak boleh sebelum tanggal mulai', 'error');
                        this.value = daysStartDate.value;
                    }
                });
            }

            console.log('Days mode setup complete');
        }

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

                // Validate operation day only if field exists
                const singleDateWarning = document.getElementById('single_date_warning');
                const singleDateDay = document.getElementById('single_date_day');
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
            } else if (dateType === 'days') {
                console.log('Validating days mode...');

                const daysChecked = document.querySelectorAll('input[name="days[]"]:checked');
                const daysStartDate = document.getElementById('days_start_date');
                const daysEndDate = document.getElementById('days_end_date');

                console.log('Checked days:', Array.from(daysChecked).map(cb => cb.value));
                console.log('Start date:', daysStartDate?.value);
                console.log('End date:', daysEndDate?.value);

                if (!daysChecked || daysChecked.length === 0) {
                    e.preventDefault();
                    showToast('Silakan pilih minimal satu hari', 'error');
                    return false;
                }

                if (!daysStartDate || !daysStartDate.value || !daysEndDate || !daysEndDate.value) {
                    e.preventDefault();
                    showToast('Silakan pilih tanggal mulai dan tanggal akhir', 'error');
                    return false;
                }

                // Validate start date is before or equal to end date
                if (daysStartDate.value > daysEndDate.value) {
                    e.preventDefault();
                    showToast('Tanggal mulai harus sebelum atau sama dengan tanggal akhir', 'error');
                    return false;
                }
            } else if (dateType === 'multiple') {
                const selectedDates = document.getElementById('selected_dates');

                if (!selectedDates || !selectedDates.value) {
                    e.preventDefault();
                    showToast('Silakan pilih minimal satu tanggal', 'error');
                    return false;
                }
            }

            console.log('Form validation passed');
            return true;
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

        // Init calendar if we start on multiple date view
        if (dateTypeSelect && dateTypeSelect.value === 'multiple' && multipleDateFields && !multipleDateFields
            .classList.contains('hidden')) {
            generateCalendar();
        }

        console.log('Schedule dates management initialization complete');
    });

    document.addEventListener('DOMContentLoaded', function() {
        const addDateForm = document.getElementById('addDateForm');

        if (addDateForm) {
            addDateForm.addEventListener('submit', function(e) {
                return validateAddDateForm(e);
            });
        }

        // Update dateTypeSelect event listener to properly handle days mode
        const dateTypeSelect = document.getElementById('date_type');
        if (dateTypeSelect) {
            dateTypeSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                console.log(`Date type changed to: ${selectedValue}`);

                // Hide all fields first
                const singleDateFields = document.getElementById('singleDateFields');
                const rangeDateFields = document.getElementById('rangeDateFields');
                const daysFields = document.getElementById('daysFields');
                const multipleDateFields = document.getElementById('multipleDateFields');

                if (singleDateFields) singleDateFields.classList.add('hidden');
                if (rangeDateFields) rangeDateFields.classList.add('hidden');
                if (daysFields) daysFields.classList.add('hidden');
                if (multipleDateFields) multipleDateFields.classList.add('hidden');

                // Show only relevant fields with smooth transition
                if (selectedValue === 'single' && singleDateFields) {
                    singleDateFields.classList.remove('hidden');
                } else if (selectedValue === 'range' && rangeDateFields) {
                    rangeDateFields.classList.remove('hidden');
                    // Update preview when showing range fields
                    if (typeof updateRangeDatePreview === 'function') {
                        updateRangeDatePreview();
                    }
                } else if (selectedValue === 'days' && daysFields) {
                    daysFields.classList.remove('hidden');
                    setupDaysMode(); // Setup days mode
                } else if (selectedValue === 'multiple' && multipleDateFields) {
                    multipleDateFields.classList.remove('hidden');
                    if (typeof generateCalendar === 'function') {
                        generateCalendar(); // Generate calendar when this view is selected
                    }
                }

                // Re-setup date inputs with enhanced validation after showing fields
                setTimeout(function() {
                    if (typeof setupDateInputs === 'function') {
                        setupDateInputs();
                    }
                }, 100);
            });
        }
    });
</script>
