@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Kelola Tanggal Jadwal</h1>
            <div>
                <a href="{{ route('admin.schedules.show', $schedule) }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
                <button type="button" id="addDateBtn" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                    <i class="fas fa-plus mr-2"></i> Tambah Tanggal
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Informasi Jadwal</h2>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Rute:</p>
                        <p class="font-medium">
                            @if (is_object($schedule->route))
                                {{ $schedule->route->origin }} - {{ $schedule->route->destination }}
                            @else
                                Rute tidak tersedia
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Kapal:</p>
                        <p class="font-medium">
                            @if (is_object($schedule->ferry))
                                {{ $schedule->ferry->name }}
                            @else
                                Kapal tidak tersedia
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Waktu Keberangkatan:</p>
                        <p class="font-medium">{{ $schedule->departure_time->format('H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Estimasi Tiba:</p>
                        <p class="font-medium">{{ $schedule->arrival_time->format('H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Hari Operasi:</p>
                        <p class="font-medium">
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
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status:</p>
                        <p class="font-medium">
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
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <form action="{{ url()->current() }}" method="GET" id="filterForm" class="mb-6 bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-3">Filter Tanggal</h3>
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Bulan:</label>
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
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun:</label>
                    <select id="year" name="year"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Tahun</option>
                        @foreach (range(date('Y'), date('Y') + 1) as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
                    <select id="status" name="status"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="AVAILABLE" {{ request('status') == 'AVAILABLE' ? 'selected' : '' }}>Tersedia
                        </option>
                        <option value="UNAVAILABLE" {{ request('status') == 'UNAVAILABLE' ? 'selected' : '' }}>Tidak
                            Tersedia
                        </option>
                    </select>
                </div>
                <div class="mt-4 md:mt-6">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                        <i class="fas fa-filter mr-2"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Dates Table -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-semibold mb-3">Daftar Tanggal Jadwal</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">#
                            </th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Tanggal
                            </th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Hari
                            </th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Penumpang</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Kendaraan</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Status
                            </th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scheduleDates ?? [] as $date)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm font-medium">
                                    {{ is_object($date) && $date->date ? \Carbon\Carbon::parse($date->date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    {{ is_object($date) && $date->date ? \Carbon\Carbon::parse($date->date)->translatedFormat('l') : '-' }}
                                </td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    @if (is_object($schedule->ferry))
                                        <span
                                            class="font-medium">{{ is_object($date) ? $date->passenger_count ?? 0 : 0 }}</span>
                                        /
                                        {{ $schedule->ferry->capacity_passenger }}
                                    @else
                                        {{ is_object($date) ? $date->passenger_count ?? 0 : 0 }} / -
                                    @endif
                                </td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    @if (is_object($schedule->ferry))
                                        <div class="grid grid-cols-2 gap-1">
                                            <div>Motor: <span
                                                    class="font-medium">{{ is_object($date) ? $date->motorcycle_count ?? 0 : 0 }}</span>
                                                /
                                                {{ $schedule->ferry->capacity_vehicle_motorcycle }}</div>
                                            <div>Mobil: <span class="font-medium">{{ $date->car_count ?? 0 }}</span> /
                                                {{ $schedule->ferry->capacity_vehicle_car }}</div>
                                            <div>Bus: <span class="font-medium">{{ $date->bus_count ?? 0 }}</span> /
                                                {{ $schedule->ferry->capacity_vehicle_bus }}</div>
                                            <div>Truk: <span class="font-medium">{{ $date->truck_count ?? 0 }}</span> /
                                                {{ $schedule->ferry->capacity_vehicle_truck }}</div>
                                        </div>
                                    @else
                                        -
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
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    <div class="flex space-x-2">
                                        <button class="edit-date-btn text-yellow-500 hover:text-yellow-700 mr-2"
                                            data-id="{{ $date->id }}" data-date="{{ $date->date }}"
                                            data-status="{{ $date->status }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="delete-date-btn text-red-500 hover:text-red-700"
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
                                <td colspan="7"
                                    class="py-6 px-4 border-b border-gray-200 text-sm text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-calendar-times text-4xl mb-2"></i>
                                        <p>Tidak ada tanggal terjadwal</p>
                                        <button id="emptyAddDateBtn"
                                            class="mt-3 bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded text-sm">
                                            <i class="fas fa-plus mr-1"></i> Tambah Tanggal
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (isset($scheduleDates) &&
                $scheduleDates instanceof \Illuminate\Pagination\LengthAwarePaginator &&
                $scheduleDates->hasPages())
            <div class="mt-4">
                {{ $scheduleDates->appends(request()->except('page'))->links() }}
            </div>
        @endif
    </div>

    <!-- Add Date Modal -->
    <div id="addDateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-800">Tambah Tanggal Jadwal</h3>
                    <button type="button" id="closeAddModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Operation Days Info -->
                <div class="bg-blue-50 p-3 rounded-lg mb-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span>Hari operasi: <strong>{{ implode(', ', $dayLabels) }}</strong></span>
                    </p>
                    <p class="text-xs text-blue-600 mt-1">Hanya tanggal yang jatuh pada hari operasi yang dapat dipilih.
                    </p>
                </div>

                <form id="addDateForm" action="{{ route('admin.schedules.dates.store', $schedule) }}" method="POST"
                    class="space-y-4">
                    @csrf
                    <div>
                        <label for="date_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe
                            Penambahan</label>
                        <select id="date_type" name="date_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="single">Tanggal Tunggal</option>
                            <option value="range">Rentang Tanggal</option>
                            <option value="multiple">Pilih Beberapa Tanggal</option>
                        </select>
                    </div>

                    <!-- Single date fields section -->
                    <div id="singleDateFields">
                        <div>
                            <label for="single_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <div class="flex items-center">
                                <input type="date" id="single_date" name="single_date"
                                    class="date-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <span id="single_date_day" class="ml-2 font-medium"></span>
                            </div>
                            <div id="single_date_warning" class="hidden mt-1 text-xs text-red-500"></div>
                            <div class="date-type-info text-xs text-blue-600 mt-1"></div>
                        </div>
                    </div>

                    <!-- Range date fields section -->
                    <div id="rangeDateFields" class="hidden space-y-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Mulai</label>
                            <div class="flex items-center">
                                <input type="date" id="start_date" name="start_date"
                                    class="date-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <span id="start_date_day" class="ml-2 font-medium"></span>
                            </div>
                            <div id="start_date_warning" class="hidden mt-1 text-xs text-red-500"></div>
                            <div class="date-type-info text-xs text-blue-600 mt-1"></div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Akhir</label>
                            <div class="flex items-center">
                                <input type="date" id="end_date" name="end_date"
                                    class="date-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <span id="end_date_day" class="ml-2 font-medium"></span>
                            </div>
                            <div id="end_date_warning" class="hidden mt-1 text-xs text-red-500"></div>
                            <p class="text-xs text-gray-500 mt-1">Catatan: Hanya tanggal yang jatuh pada hari operasi yang
                                akan dibuat.</p>
                        </div>

                        <!-- Preview of valid operation days in the range -->
                        <div id="range_date_preview" class="bg-gray-50 rounded p-2"></div>
                    </div>

                    <!-- Multiple date fields section -->
                    <div id="multipleDateFields" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Tanggal (3 Bulan Ke
                            Depan)</label>

                        <!-- Operation days info -->
                        <div class="bg-blue-50 p-2 rounded-lg mb-3 border border-blue-200">
                            <p class="text-xs text-blue-800 font-medium">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span id="calendar-info">Hari operasi: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu,
                                    Minggu</span>
                            </p>
                        </div>

                        <!-- Calendar Legend -->
                        <div class="flex items-center text-xs mb-2 bg-gray-50 p-2 rounded">
                            <div class="flex items-center mr-4">
                                <div class="w-3 h-3 bg-gray-300 rounded mr-1"></div>
                                <span>Bukan hari operasi</span>
                            </div>
                            <div class="flex items-center mr-4">
                                <div class="w-3 h-3 border border-green-200 rounded mr-1"></div>
                                <span>Hari operasi</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded mr-1"></div>
                                <span>Tanggal dipilih</span>
                            </div>
                        </div>

                        <div id="date_calendar" class="bg-gray-50 p-3 rounded-lg border max-h-60 overflow-y-auto">
                            <div id="calendar_dates" class="grid grid-cols-7 gap-1">
                                <!-- Calendar dates will be generated by JS -->
                            </div>
                            <input type="hidden" id="selected_dates" name="selected_dates" value="">
                        </div>

                        <!-- Preview of selected dates -->
                        <div id="selected_dates_preview" class="mt-2"></div>

                        <p class="text-xs text-gray-500 mt-1">Hanya hari operasi yang dapat dipilih (ditampilkan dengan
                            warna normal).</p>
                    </div>

                    <!-- Days selection fields if needed -->
                    <div id="daysFields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Hari</label>
                            <div class="grid grid-cols-2 gap-2">
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
                            <label for="days_start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date" id="days_start_date" name="days_start_date"
                                class="date-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="days_end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                            <input type="date" id="days_end_date" name="days_end_date"
                                class="date-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="AVAILABLE">Tersedia</option>
                            <option value="UNAVAILABLE">Tidak Tersedia</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                        <button type="button" id="cancelAddBtn"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded">
                            Batal
                        </button>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Date Modal - Simplified Version (No Date Editing) -->
    <div id="editDateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Status Jadwal</h3>
                    <button type="button" id="closeEditModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editDateForm" action="#" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_date_id" name="date_id">
                    <input type="hidden" id="original_date" name="date">

                    <div class="bg-blue-50 p-3 rounded-lg mb-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="currentDateDisplay" class="font-medium">Jadwal: </span>
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Status
                            Jadwal</label>
                        <select id="edit_status" name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="AVAILABLE">Tersedia</option>
                            <option value="UNAVAILABLE">Tidak Tersedia</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="edit_status_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Alasan Perubahan Status <span class="text-sm text-gray-500">(opsional)</span>
                        </label>
                        <input type="text" id="edit_status_reason" name="status_reason"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Contoh: Maintenance, Libur, dll">
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                        <button type="button" id="cancelEditBtn"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded">
                            Batal
                        </button>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Date Modal -->
    <div id="deleteDateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-800">Konfirmasi Hapus</h3>
                    <button type="button" id="closeDeleteModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-6">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mr-2"></i>
                    <p id="deleteConfirmText" class="text-gray-700">Apakah Anda yakin ingin menghapus tanggal ini?</p>
                </div>
                <form id="deleteDateForm" action="#" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2">
                        <button type="button" id="cancelDeleteBtn"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded">
                            Batal
                        </button>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">
                            <i class="fas fa-trash mr-1"></i> Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

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

                const dayOfWeek = date.getDay(); // 0=Sunday, 1=Monday, etc.
                return jsOperationDays.includes(dayOfWeek);
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
                    dayDisplayElement.classList.remove('text-green-600');
                    dayDisplayElement.classList.add('text-red-600');
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
                    dayDisplayElement.classList.remove('text-red-600');
                    dayDisplayElement.classList.add('text-green-600');
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
                    dayDisplayElement.className = 'ml-2 font-medium';
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
                    '<p class="text-red-500 text-xs mt-2">Rentang tanggal tidak valid</p>';
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
                    '<p class="text-yellow-600 text-xs mt-2">Tidak ada tanggal operasi dalam rentang ini</p>';
            } else {
                previewElement.innerHTML = `
                <div class="text-xs mt-2">
                    <p class="font-medium text-gray-700">Tanggal operasi yang akan dibuat (${validDates.length}):</p>
                    <div class="mt-1 grid grid-cols-2 sm:grid-cols-3 gap-1 bg-gray-50 p-2 rounded max-h-24 overflow-y-auto">
                        ${validDates.map(date => `
                            <div class="text-xs text-gray-800">
                                ${formatDisplayDate(date.toISOString().split('T')[0])}
                                <span class="text-green-600">(${getDayName(date)})</span>
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
                        '<p class="text-gray-500 text-xs">Belum ada tanggal yang dipilih</p>';
                    return;
                }

                selectedDatesPreview.innerHTML = `
            <p class="font-medium text-gray-700 text-xs">Tanggal yang dipilih (${selectedDates.length}):</p>
            <div class="mt-1 flex flex-wrap gap-1 bg-gray-50 p-2 rounded max-h-24 overflow-y-auto">
                ${selectedDates.map(dateStr => {
                    const date = new Date(dateStr);
                    return `
                        <div class="text-xs text-white bg-blue-500 rounded px-1 py-0.5 flex items-center">
                            ${formatDisplayDate(dateStr)}
                            <span class="ml-1">(${getDayName(date)})</span>
                            <button type="button" class="remove-date ml-1" data-date="${dateStr}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

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
            weekdayHeader.className = 'grid grid-cols-7 gap-1 text-center mb-2';

            ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'].forEach((day, index) => {
                // Convert display index to JS day (0=Sunday, 1=Monday, etc.)
                // index 0 = Sen (Monday) which is 1 in JS
                // index 6 = Min (Sunday) which is 0 in JS
                const jsDay = index === 6 ? 0 : index + 1;
                const isOperationDay = jsOperationDays.includes(jsDay);

                const dayElement = document.createElement('div');
                dayElement.className =
                    `text-xs font-medium ${isOperationDay ? 'text-green-600' : 'text-gray-400'}`;
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

                // Debug log for days
                if (currentDate.getDate() === 1 || currentDate.getDate() === 15) {
                    console.log(
                        `Day check: ${dateString}, day of week: ${dayOfWeek}, is operation day: ${isOperationDay}`
                    );
                }

                dateCell.textContent = currentDate.getDate();

                if (isOperationDay) {
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
                } else {
                    dateCell.className =
                        'h-8 flex items-center justify-center text-gray-300 text-center text-sm';
                    dateCell.setAttribute('title', `${getDayName(currentDate)} - Bukan hari operasi`);
                }

                calendarDates.appendChild(dateCell);

                // Move to next day
                currentDate.setDate(currentDate.getDate() + 1);

                // If we're at the start of a new month, add a month divider
                if (currentDate.getDate() === 1) {
                    const monthDivider = document.createElement('div');
                    monthDivider.className =
                        'col-span-7 text-center py-1 border-t mt-1 mb-1 text-xs font-medium text-gray-500';

                    const monthNames = [
                        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
                    ];
                    monthDivider.textContent = monthNames[currentDate.getMonth()] + ' ' + currentDate
                        .getFullYear();

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

                // Show only relevant fields
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

        // Create header with operation days information
        function addOperationDaysHeader() {
            // Check if header already exists
            const existingHeader = document.querySelector('.operation-days-header');
            if (existingHeader) return;

            const operationInfoDiv = document.createElement('div');
            operationInfoDiv.className =
                'operation-days-header bg-yellow-50 p-3 rounded-lg mb-4 border border-yellow-200';
            operationInfoDiv.innerHTML = `
            <p class="text-sm font-medium text-yellow-800">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <span>Perhatian: Hanya tanggal yang jatuh pada hari-hari operasi yang valid: <strong>${operationDayNames.join(', ')}</strong></span>
            </p>
            <p class="text-xs text-yellow-700 mt-1">Tanggal yang dipilih harus sesuai dengan hari operasi kapal.</p>
        `;

            // Add before the date type selector
            const dateTypeDiv = dateTypeSelect ? dateTypeSelect.closest('div') : null;
            if (dateTypeDiv && dateTypeDiv.parentNode) {
                dateTypeDiv.parentNode.insertBefore(operationInfoDiv, dateTypeDiv);
            }
        }

        // Add operation days header when modal is opened
        const addDateBtn = document.getElementById('addDateBtn');
        const emptyAddDateBtn = document.getElementById('emptyAddDateBtn');
        const addDateForm = document.getElementById('addDateForm');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddBtn = document.getElementById('cancelAddBtn');

        function prepareAddModal() {
            // Add operation days header
            addOperationDaysHeader();

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
            addDateForm.addEventListener('submit', function(e) {
                console.log('Form submitted');

                // Validate form based on date type
                const dateType = dateTypeSelect ? dateTypeSelect.value : '';

                if (dateType === 'single') {
                    const singleDate = document.getElementById('single_date');
                    const singleDateWarning = document.getElementById('single_date_warning');
                    const singleDateDay = document.getElementById('single_date_day');

                    if (!singleDate.value) {
                        e.preventDefault();
                        alert('Silakan pilih tanggal');
                        return false;
                    }

                    if (!validateDateInput(singleDate, singleDateWarning, singleDateDay)) {
                        e.preventDefault();
                        alert('Tanggal yang dipilih tidak sesuai dengan hari operasi kapal');
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
                        alert('Silakan pilih tanggal mulai dan tanggal akhir');
                        return false;
                    }

                    if (!validateDateInput(startDate, startDateWarning, startDateDay)) {
                        e.preventDefault();
                        alert('Tanggal mulai tidak sesuai dengan hari operasi kapal');
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
                        alert('Tidak ada hari operasi dalam rentang tanggal yang dipilih');
                        return false;
                    }
                } else if (dateType === 'days') {
                    const daysChecked = document.querySelectorAll('input[name="days[]"]:checked');
                    const daysStartDate = document.getElementById('days_start_date');
                    const daysEndDate = document.getElementById('days_end_date');

                    if (daysChecked.length === 0) {
                        e.preventDefault();
                        alert('Silakan pilih minimal satu hari');
                        return false;
                    }

                    if (!daysStartDate.value || !daysEndDate.value) {
                        e.preventDefault();
                        alert('Silakan pilih tanggal mulai dan tanggal akhir');
                        return false;
                    }
                } else if (dateType === 'multiple') {
                    const selectedDates = document.getElementById('selected_dates').value;

                    if (!selectedDates) {
                        e.preventDefault();
                        alert('Silakan pilih minimal satu tanggal');
                        return false;
                    }
                }

                // Form looks valid, allow submission
                console.log('Form validation passed, submitting...');
                return true;
            });
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
                    currentDateDisplay.textContent =
                        `Jadwal tanggal: ${formattedDate} (${dayName})`;
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
                    deleteConfirmText.textContent =
                        `Apakah Anda yakin ingin menghapus jadwal untuk tanggal ${dateText}?`;
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

        // Init calendar if we start on multiple date view
        if (dateTypeSelect && dateTypeSelect.value === 'multiple' && multipleDateFields && !multipleDateFields.classList.contains('hidden')) {
            generateCalendar();
        }

        console.log('Schedule dates management initialization complete');
    });
</script>
