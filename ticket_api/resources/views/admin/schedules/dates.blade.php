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
                <button id="addDateBtn" type="button"
                    onclick="document.getElementById('addDateModal').classList.remove('hidden'); document.getElementById('addDateModal').classList.add('flex');"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
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
                        <p class="font-medium">{{ $schedule->departure_time }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Estimasi Tiba:</p>
                        <p class="font-medium">{{ $schedule->arrival_time }}</p>
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
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
                <select id="status" name="status"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="AVAILABLE" {{ request('status') == 'AVAILABLE' ? 'selected' : '' }}>Tersedia</option>
                    <option value="UNAVAILABLE" {{ request('status') == 'UNAVAILABLE' ? 'selected' : '' }}>Tidak Tersedia
                    </option>
                </select>
            </div>
            <div class="mt-4 md:mt-6">
                <button id="filterBtn" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
            </div>
        </div>

        <!-- Dates Table -->
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
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                            Kendaraan</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status
                        </th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Aksi
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
                                    @if (is_object($schedule->ferry))
                                        M: {{ $date->motorcycle_count ?? 0 }} /
                                        {{ $schedule->ferry->capacity_vehicle_motorcycle }}<br>
                                        C: {{ $date->car_count ?? 0 }} / {{ $schedule->ferry->capacity_vehicle_car }}<br>
                                        B: {{ $date->bus_count ?? 0 }} / {{ $schedule->ferry->capacity_vehicle_bus }}<br>
                                        T: {{ $date->truck_count ?? 0 }} / {{ $schedule->ferry->capacity_vehicle_truck }}
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
                                    <button class="edit-date-btn text-yellow-500 hover:text-yellow-700 mr-2"
                                        data-id="{{ $date->id }}" data-date="{{ $date->date }}"
                                        data-status="{{ $date->status }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="delete-date-btn text-red-500 hover:text-red-700"
                                        data-id="{{ $date->id }}"
                                        data-date="{{ \Carbon\Carbon::parse($date->date)->format('d/m/Y') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada
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
    </div>

    <!-- Add Date Modal -->
    <div id="addDateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Tambah Tanggal Jadwal</h3>
                <button type="button" id="closeAddModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addDateForm" action="{{ route('admin.schedules.dates.store', $schedule) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="date_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Tanggal</label>
                    <select id="date_type" name="date_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="single">Tanggal Tunggal</option>
                        <option value="range">Rentang Tanggal</option>
                        <option value="days">Berdasarkan Hari</option>
                    </select>
                </div>

                <div id="singleDateFields">
                    <div class="mb-4">
                        <label for="single_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <input type="date" id="single_date" name="single_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div id="rangeDateFields" class="hidden">
                    <div class="mb-4">
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div id="daysFields" class="hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Hari</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <input type="checkbox" id="monday" name="days[]" value="1">
                                <label for="monday">Senin</label>
                            </div>
                            <div>
                                <input type="checkbox" id="tuesday" name="days[]" value="2">
                                <label for="tuesday">Selasa</label>
                            </div>
                            <div>
                                <input type="checkbox" id="wednesday" name="days[]" value="3">
                                <label for="wednesday">Rabu</label>
                            </div>
                            <div>
                                <input type="checkbox" id="thursday" name="days[]" value="4">
                                <label for="thursday">Kamis</label>
                            </div>
                            <div>
                                <input type="checkbox" id="friday" name="days[]" value="5">
                                <label for="friday">Jumat</label>
                            </div>
                            <div>
                                <input type="checkbox" id="saturday" name="days[]" value="6">
                                <label for="saturday">Sabtu</label>
                            </div>
                            <div>
                                <input type="checkbox" id="sunday" name="days[]" value="7">
                                <label for="sunday">Minggu</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="days_start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                            Mulai</label>
                        <input type="date" id="days_start_date" name="days_start_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="days_end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                            Akhir</label>
                        <input type="date" id="days_end_date" name="days_end_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="AVAILABLE">Tersedia</option>
                        <option value="UNAVAILABLE">Tidak Tersedia</option>
                    </select>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" id="cancelAddBtn"
                        class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Date Modal -->
    <div id="editDateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Edit Tanggal Jadwal</h3>
                <button id="closeEditModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editDateForm" action="#" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_date_id" name="date_id">

                <div class="mb-4">
                    <label for="edit_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" id="edit_date" name="date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="edit_status" name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="AVAILABLE">Tersedia</option>
                        <option value="UNAVAILABLE">Tidak Tersedia</option>
                    </select>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" id="cancelEditBtn"
                        class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Date Modal -->
    <div id="deleteDateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Konfirmasi Hapus</h3>
                <button id="closeDeleteModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p id="deleteConfirmText" class="mb-4">Apakah Anda yakin ingin menghapus tanggal ini?</p>
            <form id="deleteDateForm" action="#" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end mt-6">
                    <button type="button" id="cancelDeleteBtn"
                        class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2">
                        Batal
                    </button>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">
                        Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded');

            // Date type change handler
            const dateTypeSelect = document.getElementById('date_type');
            const singleDateFields = document.getElementById('singleDateFields');
            const rangeDateFields = document.getElementById('rangeDateFields');
            const daysFields = document.getElementById('daysFields');

            if (dateTypeSelect) {
                dateTypeSelect.addEventListener('change', function() {
                    const selectedValue = this.value;

                    singleDateFields.classList.add('hidden');
                    rangeDateFields.classList.add('hidden');
                    daysFields.classList.add('hidden');

                    if (selectedValue === 'single') {
                        singleDateFields.classList.remove('hidden');
                    } else if (selectedValue === 'range') {
                        rangeDateFields.classList.remove('hidden');
                    } else if (selectedValue === 'days') {
                        daysFields.classList.remove('hidden');
                    }
                });
            }

            // Filter form submission
            const filterBtn = document.getElementById('filterBtn');
            if (filterBtn) {
                filterBtn.addEventListener('click', function() {
                    const month = document.getElementById('month').value;
                    const year = document.getElementById('year').value;
                    const status = document.getElementById('status').value;

                    let url = new URL(window.location.href);
                    if (month) url.searchParams.set('month', month);
                    else url.searchParams.delete('month');

                    if (year) url.searchParams.set('year', year);
                    else url.searchParams.delete('year');

                    if (status) url.searchParams.set('status', status);
                    else url.searchParams.delete('status');

                    window.location.href = url.toString();
                });
            }

            // Add date modal - PERBAIKAN
            const addDateBtn = document.getElementById('addDateBtn');
            const addDateModal = document.getElementById('addDateModal');
            const closeAddModal = document.getElementById('closeAddModal');
            const cancelAddBtn = document.getElementById('cancelAddBtn');

            console.log('Add Date Button:', addDateBtn);
            console.log('Modal element:', addDateModal);

            // Gunakan inline onclick sebagai solusi terakhir jika event listener tidak bekerja
            if (addDateBtn) {
                addDateBtn.onclick = function() {
                    console.log('Button clicked');
                    if (addDateModal) {
                        addDateModal.classList.remove('hidden');
                        addDateModal.classList.add('flex');
                        console.log('Modal should be visible now');
                    } else {
                        console.error('Modal element not found!');
                    }
                    return false; // Prevent default
                };
            }

            if (closeAddModal) {
                closeAddModal.onclick = function() {
                    addDateModal.classList.add('hidden');
                    addDateModal.classList.remove('flex');
                    return false;
                };
            }

            if (cancelAddBtn) {
                cancelAddBtn.onclick = function() {
                    addDateModal.classList.add('hidden');
                    addDateModal.classList.remove('flex');
                    return false;
                };
            }

            // Edit date modal
            const editButtons = document.querySelectorAll('.edit-date-btn');
            const editDateModal = document.getElementById('editDateModal');
            const closeEditModal = document.getElementById('closeEditModal');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const editDateForm = document.getElementById('editDateForm');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const dateId = this.getAttribute('data-id');
                    const date = this.getAttribute('data-date');
                    const status = this.getAttribute('data-status');

                    document.getElementById('edit_date_id').value = dateId;
                    document.getElementById('edit_date').value = date;
                    document.getElementById('edit_status').value = status;

                    editDateForm.action = `/admin/schedules/dates/${dateId}`;
                    editDateModal.classList.remove('hidden');
                    editDateModal.classList.add('flex');
                });
            });

            if (closeEditModal) {
                closeEditModal.onclick = function() {
                    editDateModal.classList.add('hidden');
                    editDateModal.classList.remove('flex');
                    return false;
                };
            }

            if (cancelEditBtn) {
                cancelEditBtn.onclick = function() {
                    editDateModal.classList.add('hidden');
                    editDateModal.classList.remove('flex');
                    return false;
                };
            }

            // Delete date modal
            const deleteButtons = document.querySelectorAll('.delete-date-btn');
            const deleteDateModal = document.getElementById('deleteDateModal');
            const closeDeleteModal = document.getElementById('closeDeleteModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            const deleteDateForm = document.getElementById('deleteDateForm');
            const deleteConfirmText = document.getElementById('deleteConfirmText');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const dateId = this.getAttribute('data-id');
                    const dateText = this.getAttribute('data-date');

                    deleteConfirmText.textContent =
                        `Apakah Anda yakin ingin menghapus jadwal untuk tanggal ${dateText}?`;
                    deleteDateForm.action = `/admin/schedules/dates/${dateId}`;
                    deleteDateModal.classList.remove('hidden');
                    deleteDateModal.classList.add('flex');
                });
            });

            if (closeDeleteModal) {
                closeDeleteModal.onclick = function() {
                    deleteDateModal.classList.add('hidden');
                    deleteDateModal.classList.remove('flex');
                    return false;
                };
            }

            if (cancelDeleteBtn) {
                cancelDeleteBtn.onclick = function() {
                    deleteDateModal.classList.add('hidden');
                    deleteDateModal.classList.remove('flex');
                    return false;
                };
            }
        });
    </script>
@endpush
