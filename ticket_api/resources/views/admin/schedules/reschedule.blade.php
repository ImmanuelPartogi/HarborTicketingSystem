<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">Reschedule Jadwal {{ $schedule->route->origin }} - {{ $schedule->route->destination }}</h1>
        <a href="{{ route('admin.schedules.show', $schedule->id) }}"
            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Info tentang jadwal yang sedang di-reschedule -->
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600">Kapal:</p>
                <p class="font-medium">{{ $schedule->ferry->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Waktu Keberangkatan Asal:</p>
                <p class="font-medium">{{ $schedule->departure_time->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status Saat Ini:</p>
                <span class="px-2 py-1 rounded-full text-xs {{ $schedule->status == 'DELAYED' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                    {{ $schedule->getStatusLabelAttribute() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Tanggal yang terdampak -->
    <div class="mb-6">
        <h3 class="text-lg font-medium mb-3">Tanggal Keberangkatan yang Terdampak</h3>

        @if($weatherAffectedDates->count() > 0)
            <div class="border rounded-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Penumpang
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($weatherAffectedDates as $date)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $date->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                    {{ $date->getStatusLabelAttribute() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $date->passenger_count }} orang
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-gray-50 p-4 rounded border text-center">
                <p class="text-gray-600">Tidak ada tanggal yang terdampak masalah cuaca</p>
            </div>
        @endif
    </div>

    <!-- Form Reschedule -->
    <form action="{{ route('admin.schedules.reschedule', $schedule) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Tentang Penjadwalan Ulang</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Perubahan jadwal baru akan berlaku untuk tanggal baru yang Anda pilih.
                        Semua tanggal yang terdampak masalah cuaca akan dibatalkan dan penumpang akan diarahkan ke jadwal baru.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Baru <span class="text-red-500">*</span></label>
                <input type="date" id="date" name="date" required min="{{ date('Y-m-d') }}"
                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="time" class="block text-sm font-medium text-gray-700 mb-1">Waktu Keberangkatan Baru <span class="text-red-500">*</span></label>
                <input type="time" id="time" name="time" required
                    value="{{ $schedule->departure_time->format('H:i') }}"
                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
        </div>

        <div class="mt-6">
            <h4 class="text-md font-medium mb-2">Opsi Penjadwalan Ulang</h4>

            <div class="mb-4">
                <div class="flex items-center mb-2">
                    <input type="radio" id="reschedule_type_all" name="reschedule_type" value="all" checked
                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                    <label for="reschedule_type_all" class="ml-2 block text-sm font-medium text-gray-700">
                        Reschedule semua tanggal yang terdampak masalah cuaca
                    </label>
                </div>
                <div class="flex items-center">
                    <input type="radio" id="reschedule_type_selected" name="reschedule_type" value="selected"
                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                    <label for="reschedule_type_selected" class="ml-2 block text-sm font-medium text-gray-700">
                        Pilih tanggal spesifik yang akan di-reschedule
                    </label>
                </div>
            </div>

            <div id="affected_dates_select" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Tanggal untuk Di-reschedule</label>
                <div class="max-h-48 overflow-y-auto p-4 bg-gray-50 rounded border">
                    @foreach($weatherAffectedDates as $date)
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="date_{{ $date->id }}" name="affected_dates[]" value="{{ $date->date->format('Y-m-d') }}"
                            class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                        <label for="date_{{ $date->id }}" class="ml-2 text-sm text-gray-700">
                            {{ $date->date->format('d/m/Y') }} - {{ $date->passenger_count }} penumpang
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label for="status_after_reschedule" class="block text-sm font-medium text-gray-700 mb-1">Status Setelah Reschedule</label>
                <select id="status_after_reschedule" name="status_after_reschedule"
                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="ACTIVE">Aktif</option>
                    <option value="DELAYED">Tetap Tertunda</option>
                </select>
            </div>

            <div>
                <label for="notification_message" class="block text-sm font-medium text-gray-700 mb-1">Pesan Notifikasi (opsional)</label>
                <textarea id="notification_message" name="notification_message" rows="2"
                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="Pesan untuk penumpang tentang perubahan jadwal"></textarea>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('admin.schedules.show', $schedule->id) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-calendar-alt mr-1"></i> Reschedule
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rescheduleTypeAll = document.getElementById('reschedule_type_all');
        const rescheduleTypeSelected = document.getElementById('reschedule_type_selected');
        const affectedDatesSelect = document.getElementById('affected_dates_select');

        function toggleAffectedDatesSelect() {
            if (rescheduleTypeSelected.checked) {
                affectedDatesSelect.classList.remove('hidden');
            } else {
                affectedDatesSelect.classList.add('hidden');
            }
        }

        rescheduleTypeAll.addEventListener('change', toggleAffectedDatesSelect);
        rescheduleTypeSelected.addEventListener('change', toggleAffectedDatesSelect);
    });
</script>
