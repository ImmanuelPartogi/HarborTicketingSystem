@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">Detail Rute</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.routes.edit', $route->id) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('admin.routes.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
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

        <!-- Status Alert untuk rute dengan masalah cuaca atau non-aktif -->
        @if ($route->status != 'ACTIVE')
            <div class="mb-6 {{ $route->status == 'WEATHER_ISSUE' ? 'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700' }} p-4 rounded-r"
                role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        @if ($route->status == 'WEATHER_ISSUE')
                            <i class="fas fa-cloud-rain text-yellow-500"></i>
                        @else
                            <i class="fas fa-ban text-red-500"></i>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="font-medium">
                            @if ($route->status == 'WEATHER_ISSUE')
                                Rute ini saat ini memiliki masalah cuaca.
                            @else
                                Rute ini saat ini tidak aktif.
                            @endif
                        </p>
                        <p class="text-sm mt-1">
                            @if ($route->status_reason)
                                Alasan: {{ $route->status_reason }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3 flex items-center">
                    <i class="fas fa-map-marked-alt mr-2 text-blue-500"></i> Informasi Rute
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Pelabuhan Asal:</div>
                        <div class="font-medium">{{ $route->origin }}</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Pelabuhan Tujuan:</div>
                        <div class="font-medium">{{ $route->destination }}</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Jarak:</div>
                        <div class="font-medium">{{ $route->distance ? $route->distance . ' KM' : '-' }}</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Durasi:</div>
                        <div class="font-medium">{{ $route->duration }} menit ({{ floor($route->duration / 60) }} jam
                            {{ $route->duration % 60 }} menit)</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Status:</div>
                        <div>
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $route->status == 'ACTIVE' ? 'bg-green-100 text-green-800' : ($route->status == 'WEATHER_ISSUE' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $route->status == 'ACTIVE' ? 'Aktif' : ($route->status == 'WEATHER_ISSUE' ? 'Masalah Cuaca' : 'Tidak Aktif') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3 flex items-center">
                    <i class="fas fa-money-bill-wave mr-2 text-green-500"></i> Informasi Harga
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Harga Dasar:</div>
                        <div class="font-medium">Rp {{ number_format($route->base_price, 0, ',', '.') }}</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Harga Motor:</div>
                        <div class="font-medium">Rp {{ number_format($route->motorcycle_price, 0, ',', '.') }}</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Harga Mobil:</div>
                        <div class="font-medium">Rp {{ number_format($route->car_price, 0, ',', '.') }}</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Harga Bus:</div>
                        <div class="font-medium">Rp {{ number_format($route->bus_price, 0, ',', '.') }}</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 text-gray-600">Harga Truk:</div>
                        <div class="font-medium">Rp {{ number_format($route->truck_price, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        

        <div class="mt-6">
            <h3 class="text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-ship mr-2 text-blue-500"></i> Jadwal Keberangkatan
            </h3>
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kapal</th>
                            <th scope="col"
                                class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hari</th>
                            <th scope="col"
                                class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu Keberangkatan</th>
                            <th scope="col"
                                class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu Kedatangan</th>
                            <th scope="col"
                                class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $schedule->ferry->name }}</td>
                                <td class="py-3 px-4 text-sm text-gray-500">
                                    @php
                                        $days = explode(',', $schedule->days);
                                        $dayNames = [
                                            '1' => 'Sen',
                                            '2' => 'Sel',
                                            '3' => 'Rab',
                                            '4' => 'Kam',
                                            '5' => 'Jum',
                                            '6' => 'Sab',
                                            '7' => 'Min',
                                        ];
                                        $dayLabels = [];
                                        foreach ($days as $day) {
                                            if (isset($dayNames[$day])) {
                                                $dayLabels[] = $dayNames[$day];
                                            }
                                        }
                                        echo implode(', ', $dayLabels);
                                    @endphp
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</td>
                                <td class="py-3 px-4 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}</td>
                                <td class="py-3 px-4 text-sm">
                                    @if ($schedule->status == 'ACTIVE')
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Aktif
                                        </span>
                                    @elseif($schedule->status == 'DELAYED')
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> Tertunda
                                            @if ($route->status == 'WEATHER_ISSUE')
                                                (Cuaca)
                                            @endif
                                        </span>
                                    @elseif($schedule->status == 'FULL')
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <i class="fas fa-users mr-1"></i> Penuh
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-ban mr-1"></i> Dibatalkan
                                            @if ($route->status == 'INACTIVE')
                                                (Rute)
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.schedules.show', $schedule->id) }}"
                                            class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded-lg transition"
                                            title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                            class="text-yellow-600 hover:text-yellow-900 bg-yellow-100 hover:bg-yellow-200 p-2 rounded-lg transition"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if ($schedule->status == 'DELAYED')
                                            <button type="button"
                                                class="reschedule-btn text-purple-600 hover:text-purple-900 bg-purple-100 hover:bg-purple-200 p-2 rounded-lg transition"
                                                data-id="{{ $schedule->id }}"
                                                data-departure="{{ $schedule->departure_time->format('H:i') }}"
                                                title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 px-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-ship text-gray-300 text-5xl mb-3"></i>
                                        <p class="text-lg font-medium">Tidak ada jadwal keberangkatan</p>
                                        <p class="text-sm text-gray-400 mb-3">Belum ada jadwal yang ditambahkan untuk rute
                                            ini</p>
                                        <a href="{{ route('admin.schedules.create', ['route_id' => $route->id]) }}"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            <i class="fas fa-plus mr-2"></i> Tambah Jadwal Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Enhanced Reschedule Modal with affected dates selection -->
    <div id="rescheduleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full w-full p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg relative">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-800">Reschedule Jadwal</h3>
                    <button type="button" id="closeRescheduleModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="rescheduleForm" action="#" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="reschedule_schedule_id" name="schedule_id">

                    <div>
                        <label for="reschedule_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                            Baru</label>
                        <input type="date" id="reschedule_date" name="date" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="reschedule_time" class="block text-sm font-medium text-gray-700 mb-2">Waktu
                            Keberangkatan Baru</label>
                        <input type="time" id="reschedule_time" name="time" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Reschedule</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="reschedule_all" name="reschedule_type" value="all" checked
                                    class="mr-2">
                                <label for="reschedule_all" class="text-sm text-gray-700">
                                    Reschedule semua tanggal tertunda
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="reschedule_selected" name="reschedule_type" value="selected"
                                    class="mr-2">
                                <label for="reschedule_selected" class="text-sm text-gray-700">
                                    Reschedule tanggal tertunda yang dipilih
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="affected_dates_container" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal yang akan direschedule</label>
                        <div id="affected_dates_list"
                            class="border rounded-md p-3 max-h-40 overflow-y-auto bg-gray-50 space-y-2">
                            <!-- JS will populate this -->
                            <p class="text-gray-500 text-sm italic">Memuat tanggal...</p>
                        </div>
                    </div>

                    <div>
                        <label for="status_after_reschedule" class="block text-sm font-medium text-gray-700 mb-2">Status
                            Setelah Reschedule</label>
                        <select id="status_after_reschedule" name="status_after_reschedule" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="ACTIVE">Aktif</option>
                            <option value="DELAYED">Tetap Tertunda</option>
                        </select>
                    </div>

                    <div>
                        <label for="notification_message" class="block text-sm font-medium text-gray-700 mb-2">Pesan
                            Notifikasi (opsional)</label>
                        <textarea id="notification_message" name="notification_message" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Pesan untuk penumpang tentang perubahan jadwal"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                        <button type="button" id="cancelRescheduleBtn"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded">
                            Batal
                        </button>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            <i class="fas fa-calendar-alt mr-1"></i> Reschedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const statusRadios = document.querySelectorAll('input[name="status"]');
            const statusNote = document.getElementById('status_note');
            const durationSettings = document.getElementById('durationSettings');
            const affectDaysSelect = document.getElementById('affect_days');
            const durationExplanation = document.getElementById('duration_explanation');

            // Initial state
            updateStatusNote();
            toggleDurationSettings();
            updateDurationExplanation();

            // Status change event
            statusRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    updateStatusNote();
                    toggleDurationSettings();
                    updateDurationExplanation();
                });
            });

            // Affect days change event
            affectDaysSelect.addEventListener('change', updateDurationExplanation);

            // Functions
            function getSelectedStatus() {
                return document.querySelector('input[name="status"]:checked').value;
            }

            function updateStatusNote() {
                const status = getSelectedStatus();

                if (status === 'ACTIVE') {
                    statusNote.innerHTML =
                        'Mengubah status menjadi <strong>Aktif</strong> akan membuka kembali rute ini untuk operasi.';
                } else if (status === 'WEATHER_ISSUE') {
                    statusNote.innerHTML =
                        'Status <strong>Masalah Cuaca</strong> bersifat sementara dan akan otomatis berubah kembali menjadi <strong>Aktif</strong> setelah periode waktu yang ditentukan.';
                } else {
                    statusNote.innerHTML =
                        'Status <strong>Tidak Aktif</strong> menandai rute ini tidak beroperasi sampai diaktifkan kembali secara manual.';
                }
            }

            function toggleDurationSettings() {
                const status = getSelectedStatus();

                // Only show duration settings for WEATHER_ISSUE status
                if (status === 'WEATHER_ISSUE') {
                    durationSettings.classList.remove('hidden');
                } else {
                    durationSettings.classList.add('hidden');
                }
            }

            function updateDurationExplanation() {
                const days = affectDaysSelect.value;
                durationExplanation.innerHTML =
                    `Setelah <span class="font-medium">${days} hari</span>, status akan otomatis kembali ke <span class="text-green-600 font-medium">Aktif</span> jika saat ini diubah ke <span class="text-yellow-600 font-medium">Masalah Cuaca</span>.`;
            }
        });
    </script>
@endsection
