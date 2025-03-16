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
                <form id="addDateForm" action="{{ route('admin.schedules.dates.store', $schedule) }}" method="POST"
                    class="space-y-4">
                    @csrf
                    <div>
                        <label for="date_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Tanggal</label>
                        <select id="date_type" name="date_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="single">Tanggal Tunggal</option>
                            <option value="range">Rentang Tanggal</option>
                            <option value="days">Berdasarkan Hari</option>
                        </select>
                    </div>

                    <div id="singleDateFields">
                        <div>
                            <label for="single_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <input type="date" id="single_date" name="single_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div id="rangeDateFields" class="hidden space-y-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Mulai</label>
                            <input type="date" id="start_date" name="start_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Akhir</label>
                            <input type="date" id="end_date" name="end_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div id="daysFields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Hari</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center">
                                    <input type="checkbox" id="monday" name="days[]" value="1" class="mr-2">
                                    <label for="monday">Senin</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="tuesday" name="days[]" value="2" class="mr-2">
                                    <label for="tuesday">Selasa</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="wednesday" name="days[]" value="3" class="mr-2">
                                    <label for="wednesday">Rabu</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="thursday" name="days[]" value="4" class="mr-2">
                                    <label for="thursday">Kamis</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="friday" name="days[]" value="5" class="mr-2">
                                    <label for="friday">Jumat</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="saturday" name="days[]" value="6" class="mr-2">
                                    <label for="saturday">Sabtu</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="sunday" name="days[]" value="7" class="mr-2">
                                    <label for="sunday">Minggu</label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="days_start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Mulai</label>
                            <input type="date" id="days_start_date" name="days_start_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="days_end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                                Akhir</label>
                            <input type="date" id="days_end_date" name="days_end_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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

    <!-- Edit Date Modal -->
    <div id="editDateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Jadwal Kapal</h3>
                    <button type="button" id="closeEditModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editDateForm" action="#" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_date_id" name="date_id">

                    <div>
                        <label for="edit_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <input type="date" id="edit_date" name="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="edit_status" name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="AVAILABLE">Tersedia</option>
                            <option value="FULL">Penuh</option>
                            <option value="CANCELLED">Dibatalkan</option>
                            <option value="DEPARTED">Sudah Berangkat</option>
                            <option value="WEATHER_ISSUE">Masalah Cuaca</option>
                        </select>
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

        // ====== Modal utility functions ======
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

        // ====== Date type switcher ======
        const dateTypeSelect = document.getElementById('date_type');
        const singleDateFields = document.getElementById('singleDateFields');
        const rangeDateFields = document.getElementById('rangeDateFields');
        const daysFields = document.getElementById('daysFields');

        if (dateTypeSelect) {
            dateTypeSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                console.log(`Date type changed to: ${selectedValue}`);

                // Hide all fields first
                singleDateFields.classList.add('hidden');
                rangeDateFields.classList.add('hidden');
                daysFields.classList.add('hidden');

                // Show only relevant fields
                if (selectedValue === 'single') {
                    singleDateFields.classList.remove('hidden');
                } else if (selectedValue === 'range') {
                    rangeDateFields.classList.remove('hidden');
                } else if (selectedValue === 'days') {
                    daysFields.classList.remove('hidden');
                }
            });
        }

        // ====== Add Date Modal ======
        const addDateBtn = document.getElementById('addDateBtn');
        const emptyAddDateBtn = document.getElementById('emptyAddDateBtn');
        const addDateForm = document.getElementById('addDateForm');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddBtn = document.getElementById('cancelAddBtn');

        if (addDateBtn) {
            addDateBtn.addEventListener('click', function() {
                console.log('Add Date button clicked');
                openModal('addDateModal');
            });
        }

        if (emptyAddDateBtn) {
            emptyAddDateBtn.addEventListener('click', function() {
                console.log('Empty Add Date button clicked');
                openModal('addDateModal');
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
                const dateType = document.getElementById('date_type').value;

                if (dateType === 'single') {
                    const singleDate = document.getElementById('single_date').value;
                    if (!singleDate) {
                        e.preventDefault();
                        alert('Silakan pilih tanggal');
                        return false;
                    }
                } else if (dateType === 'range') {
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    if (!startDate || !endDate) {
                        e.preventDefault();
                        alert('Silakan pilih tanggal mulai dan tanggal akhir');
                        return false;
                    }
                } else if (dateType === 'days') {
                    const daysChecked = document.querySelectorAll('input[name="days[]"]:checked');
                    const daysStartDate = document.getElementById('days_start_date').value;
                    const daysEndDate = document.getElementById('days_end_date').value;

                    if (daysChecked.length === 0) {
                        e.preventDefault();
                        alert('Silakan pilih minimal satu hari');
                        return false;
                    }

                    if (!daysStartDate || !daysEndDate) {
                        e.preventDefault();
                        alert('Silakan pilih tanggal mulai dan tanggal akhir');
                        return false;
                    }
                }

                // Form looks valid, allow submission
                console.log('Form validation passed, submitting...');
                return true;
            });
        }

        // ====== Edit Date Modal ======
        const editButtons = document.querySelectorAll('.edit-date-btn');
        const editDateForm = document.getElementById('editDateForm');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEditBtn = document.getElementById('cancelEditBtn');

        // Dapatkan ID jadwal dari URL
        const urlParts = window.location.pathname.split('/');
        const scheduleId = urlParts[urlParts.indexOf('schedules') + 1];

        editButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const dateId = this.getAttribute('data-id');
                const dateValue = this.getAttribute('data-date');
                const status = this.getAttribute('data-status');

                console.log('Edit date ID:', dateId);
                console.log('Edit date value:', dateValue);
                console.log('Edit status:', status);

                // Atur nilai form
                document.getElementById('edit_date_id').value = dateId;
                document.getElementById('edit_date').value = dateValue;
                document.getElementById('edit_status').value = status;

                // Set URL aksi form yang benar
                editDateForm.action = `/admin/schedules/${scheduleId}/dates/${dateId}`;
                openModal('editDateModal');
            });
        });

        if (closeEditModal) {
            closeEditModal.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal('editDateModal');
            });
        }

        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal('editDateModal');
            });
        }

        // Edit form validation
        if (editDateForm) {
            editDateForm.addEventListener('submit', function(e) {
                const editDate = document.getElementById('edit_date').value;
                if (!editDate) {
                    e.preventDefault();
                    alert('Silakan pilih tanggal');
                    return false;
                }
                return true;
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

                deleteConfirmText.textContent =
                    `Apakah Anda yakin ingin menghapus jadwal untuk tanggal ${dateText}?`;
                deleteDateForm.action = `/admin/schedules/dates/${dateId}`;
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
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            if (!input.value) {
                input.value = today;
            }
        });

        console.log('Schedule dates management initialization complete');
    });
</script>
