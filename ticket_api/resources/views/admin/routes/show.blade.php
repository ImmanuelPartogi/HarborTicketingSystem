@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Detail Rute</h1>
            <div>
                <a href="{{ route('admin.routes.edit', $route->id) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded mr-2">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('admin.routes.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>

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
                <h3 class="text-lg font-semibold mb-3">Informasi Rute</h3>
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
                        <div class="font-medium">{{ $route->duration }} menit</div>
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
                <h3 class="text-lg font-semibold mb-3">Informasi Harga</h3>
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

        <!-- Form Update Status Rute - Enhanced with Time Windows -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Update Status Rute</h3>
            <form action="{{ route('admin.routes.update-status', $route) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="ACTIVE" {{ $route->status == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                            <option value="INACTIVE" {{ $route->status == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif
                            </option>
                            <option value="WEATHER_ISSUE" {{ $route->status == 'WEATHER_ISSUE' ? 'selected' : '' }}>Masalah
                                Cuaca</option>
                        </select>
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Alasan (opsional)</label>
                        <input type="text" id="reason" name="reason" value="{{ $route->status_reason }}"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="apply_to_schedules" name="apply_to_schedules" value="1"
                            class="mr-2" checked>
                        <label for="apply_to_schedules" class="text-sm text-gray-700">
                            Terapkan perubahan status ke jadwal yang menggunakan rute ini
                        </label>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">
                        Jika dicentang, jadwal yang menggunakan rute ini akan diperbarui statusnya sesuai:
                        <br>• Rute Aktif → Jadwal Aktif
                        <br>• Rute Tidak Aktif → Jadwal Dibatalkan
                        <br>• Rute Masalah Cuaca → Jadwal Tertunda (bisa di-reschedule)
                    </p>
                </div>

                <!-- Enhanced time window settings -->
                <div class="time-window-options mt-4 border-t pt-4">
                    <h4 class="text-md font-medium mb-2">Pengaturan Waktu dan Tanggal</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                            <input type="time" id="start_time" name="start_time"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika semua waktu terpengaruh</p>
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
                            <input type="time" id="end_time" name="end_time"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika semua waktu terpengaruh</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="affect_days" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Hari ke Depan</label>
                        <select id="affect_days" name="affect_days"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="1">1 hari</option>
                            <option value="2">2 hari</option>
                            <option value="3" selected>3 hari</option>
                            <option value="5">5 hari</option>
                            <option value="7">7 hari</option>
                            <option value="14">14 hari</option>
                            <option value="30">30 hari</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Berapa hari ke depan jadwal akan terpengaruh</p>
                    </div>
                </div>

                <div class="mt-4 bg-blue-50 p-3 rounded-lg border border-blue-200">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Perubahan Status Fleksibel:</strong> Sistem akan hanya mengubah jadwal dalam rentang waktu yang ditentukan.
                        Misalnya, jika Anda menandai masalah cuaca dari jam 08:00-14:00 untuk 2 hari ke depan,
                        hanya keberangkatan pada jam tersebut yang akan ditandai tertunda.
                    </p>
                </div>

                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                        <i class="fas fa-save mr-2"></i> Update Status
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6">
            <h3 class="text-xl font-semibold mb-4">Jadwal Keberangkatan</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Kapal</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Hari</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Waktu Keberangkatan</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Waktu Kedatangan</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Status</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $schedule->ferry->name }}</td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
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
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}</td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                    @if ($schedule->status == 'ACTIVE')
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Aktif
                                        </span>
                                    @elseif($schedule->status == 'DELAYED')
                                        <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> Tertunda
                                            @if ($route->status == 'WEATHER_ISSUE')
                                                (Cuaca)
                                            @endif
                                        </span>
                                    @elseif($schedule->status == 'FULL')
                                        <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                            <i class="fas fa-users mr-1"></i> Penuh
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                            <i class="fas fa-ban mr-1"></i> Dibatalkan
                                            @if ($route->status == 'INACTIVE')
                                                (Rute)
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 border-b border-gray-200 text-sm">
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
                                <td colspan="6" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak
                                    ada jadwal keberangkatan</td>
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
                        <div id="affected_dates_list" class="border rounded-md p-3 max-h-40 overflow-y-auto bg-gray-50 space-y-2">
                            <!-- JS will populate this -->
                            <p class="text-gray-500 text-sm italic">Memuat tanggal...</p>
                        </div>
                    </div>

                    <div>
                        <label for="status_after_reschedule" class="block text-sm font-medium text-gray-700 mb-2">Status Setelah Reschedule</label>
                        <select id="status_after_reschedule" name="status_after_reschedule" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="ACTIVE">Aktif</option>
                            <option value="DELAYED">Tetap Tertunda</option>
                        </select>
                    </div>

                    <div>
                        <label for="notification_message" class="block text-sm font-medium text-gray-700 mb-2">Pesan Notifikasi (opsional)</label>
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
        // Function to open modal
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.style.opacity = '1';
                }, 10);
            }
        }

        // Function to close modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }

        // Apply to schedules checkbox toggle
        const applyToSchedulesCheckbox = document.getElementById('apply_to_schedules');
        const timeWindowOptions = document.querySelector('.time-window-options');

        if (applyToSchedulesCheckbox && timeWindowOptions) {
            applyToSchedulesCheckbox.addEventListener('change', function() {
                timeWindowOptions.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Toggle affected dates container based on reschedule type
        const rescheduleAllRadio = document.getElementById('reschedule_all');
        const rescheduleSelectedRadio = document.getElementById('reschedule_selected');
        const affectedDatesContainer = document.getElementById('affected_dates_container');

        if (rescheduleAllRadio && rescheduleSelectedRadio && affectedDatesContainer) {
            function toggleAffectedDatesContainer() {
                affectedDatesContainer.classList.toggle('hidden', rescheduleAllRadio.checked);
            }

            rescheduleAllRadio.addEventListener('change', toggleAffectedDatesContainer);
            rescheduleSelectedRadio.addEventListener('change', toggleAffectedDatesContainer);
        }

        // Reschedule button event handlers
        const rescheduleButtons = document.querySelectorAll('.reschedule-btn');
        const rescheduleForm = document.getElementById('rescheduleForm');
        const closeRescheduleModal = document.getElementById('closeRescheduleModal');
        const cancelRescheduleBtn = document.getElementById('cancelRescheduleBtn');
        const affectedDatesList = document.getElementById('affected_dates_list');

        rescheduleButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const scheduleId = this.getAttribute('data-id');
                const departureTime = this.getAttribute('data-departure');

                // Atur nilai default form (tanggal besok dan waktu asli)
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const tomorrowFormatted = tomorrow.toISOString().split('T')[0];

                document.getElementById('reschedule_schedule_id').value = scheduleId;
                document.getElementById('reschedule_date').value = tomorrowFormatted;
                document.getElementById('reschedule_time').value = departureTime;

                // Set URL action
                rescheduleForm.action = `/admin/schedules/${scheduleId}/reschedule`;

                // Fetch affected dates for this schedule
                if (affectedDatesList) {
                    affectedDatesList.innerHTML = '<p class="text-gray-500 text-sm italic">Memuat tanggal...</p>';

                    fetch(`/admin/schedules/${scheduleId}/weather-affected-dates`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.dates && data.dates.length > 0) {
                                affectedDatesList.innerHTML = '';
                                data.dates.forEach(date => {
                                    const checkbox = document.createElement('div');
                                    checkbox.className = 'flex items-center';
                                    checkbox.innerHTML = `
                                        <input type="checkbox" id="date_${date.id}" name="affected_dates[]" value="${date.date}" class="mr-2" checked>
                                        <label for="date_${date.id}" class="text-sm">${date.formatted_date}</label>
                                    `;
                                    affectedDatesList.appendChild(checkbox);
                                });
                            } else {
                                affectedDatesList.innerHTML = '<p class="text-gray-500 text-sm italic">Tidak ada tanggal terdampak cuaca</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching affected dates:', error);
                            affectedDatesList.innerHTML = '<p class="text-red-500 text-sm italic">Gagal memuat tanggal</p>';
                        });
                }

                openModal('rescheduleModal');
            });
        });

        if (closeRescheduleModal) {
            closeRescheduleModal.addEventListener('click', function() {
                closeModal('rescheduleModal');
            });
        }

        if (cancelRescheduleBtn) {
            cancelRescheduleBtn.addEventListener('click', function() {
                closeModal('rescheduleModal');
            });
        }
    });
</script>
@endsection
