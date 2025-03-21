@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-800 p-6 text-white relative">
            <div class="absolute inset-0 overflow-hidden">
                <svg class="absolute right-0 bottom-0 opacity-10 h-64 w-64" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <path fill="white" d="M46.5,-75.3C58.9,-68.9,67.3,-53.9,74.4,-38.7C81.6,-23.5,87.6,-8.1,85.8,6.3C84,20.7,74.2,34,63,44.4C51.8,54.8,39.2,62.3,25.2,68.2C11.1,74,-4.4,78.2,-19.6,76.1C-34.8,74,-49.6,65.7,-59.5,53.6C-69.4,41.5,-74.3,25.5,-77.6,8.5C-80.9,-8.5,-82.5,-26.5,-75.8,-40C-69.1,-53.5,-54.1,-62.4,-39.3,-67.4C-24.6,-72.5,-10.1,-73.7,4.4,-80.8C18.9,-87.9,34.1,-81.8,46.5,-75.3Z" transform="translate(100 100)" />
                </svg>
            </div>
            <div class="flex justify-between items-center relative z-10">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-edit mr-3 text-blue-200"></i> Edit Jadwal
                </h1>
                <a href="{{ route('admin.schedules.index') }}" class="bg-white/20 hover:bg-white/30 text-white py-2 px-4 rounded-lg transition-colors shadow-sm backdrop-blur-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>

        <div class="p-6">
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0 text-red-500 text-xl">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0 text-green-500 text-xl">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-gray-50 p-5 rounded-xl mb-6 shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold mb-3 text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i> Data Jadwal Saat Ini
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Rute:</p>
                        <p class="font-medium flex items-center">
                            @if (isset($schedule->route) && is_object($schedule->route))
                                <i class="fas fa-route mr-2 text-blue-600"></i>
                                {{ $schedule->route->origin }} - {{ $schedule->route->destination }}
                                @if($schedule->route->status != 'ACTIVE')
                                    <span class="ml-2 px-2 py-1 rounded-full text-xs {{ $schedule->route->status == 'WEATHER_ISSUE' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800' }} shadow-sm">
                                        {{ $schedule->route->status == 'WEATHER_ISSUE' ? 'Masalah Cuaca' : 'Tidak Aktif' }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Kapal:</p>
                        <p class="font-medium flex items-center">
                            @if (isset($schedule->ferry) && is_object($schedule->ferry))
                                <i class="fas fa-ship mr-2 text-blue-600"></i>
                                {{ $schedule->ferry->name }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Waktu Keberangkatan:</p>
                        <p class="font-medium flex items-center">
                            <i class="fas fa-hourglass-start mr-2 text-green-600"></i>
                            {{ $schedule->departure_time->format('H:i') ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Estimasi Tiba:</p>
                        <p class="font-medium flex items-center">
                            <i class="fas fa-hourglass-end mr-2 text-red-600"></i>
                            {{ $schedule->arrival_time->format('H:i') ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Status:</p>
                        <p class="font-medium">
                            @if ($schedule->status == 'ACTIVE')
                                <span class="px-3 py-1.5 rounded-full text-xs bg-green-100 text-green-800 shadow-sm">
                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                </span>
                            @elseif($schedule->status == 'DELAYED')
                                <span class="px-3 py-1.5 rounded-full text-xs bg-orange-100 text-orange-800 shadow-sm">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Tertunda
                                </span>
                            @elseif($schedule->status == 'FULL')
                                <span class="px-3 py-1.5 rounded-full text-xs bg-blue-100 text-blue-800 shadow-sm">
                                    <i class="fas fa-users mr-1"></i> Penuh
                                </span>
                            @elseif($schedule->status == 'DEPARTED')
                                <span class="px-3 py-1.5 rounded-full text-xs bg-purple-100 text-purple-800 shadow-sm">
                                    <i class="fas fa-ship mr-1"></i> Selesai
                                </span>
                            @else
                                <span class="px-3 py-1.5 rounded-full text-xs bg-red-100 text-red-800 shadow-sm">
                                    <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Hari Operasi:</p>
                        <p class="font-medium">
                            @php
                                $scheduleDays = !empty($schedule->days) ? explode(',', $schedule->days) : [];
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
                                foreach ($scheduleDays as $day) {
                                    if (isset($dayNames[$day])) {
                                        $dayLabels[] = $dayNames[$day];
                                    }
                                }
                            @endphp
                            <div class="flex flex-wrap gap-1">
                                @foreach ($dayLabels as $day)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-medium rounded-md bg-blue-100 text-blue-800 shadow-sm">
                                        {{ $day }}
                                    </span>
                                @endforeach
                            </div>
                        </p>
                    </div>
                </div>
            </div>

            @php
                $isStatusFinal = in_array($schedule->status, ['FULL', 'DEPARTED']);
            @endphp

            @if($isStatusFinal)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0 text-yellow-500 text-xl">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Jadwal ini memiliki status <strong>{{ $schedule->status == 'FULL' ? 'Penuh' : 'Selesai' }}</strong> yang merupakan status final.
                                Status tidak dapat diubah, namun Anda tetap dapat mengedit detail jadwal lainnya.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0 text-blue-500 text-xl">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Informasi Status:</strong> Perubahan status jadwal bersifat independen dari status rute.
                                Jika status diubah menjadi <strong>Tidak Aktif</strong>, tanggal jadwal terkait yang belum berstatus final
                                akan otomatis diubah menjadi <strong>Tidak Tersedia</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label for="route_id" class="block text-sm font-medium text-gray-700 mb-2">Rute</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-route text-gray-400"></i>
                            </div>
                            <select id="route_id" name="route_id"
                                class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('route_id') border-red-500 @enderror">
                                <option value="">Pilih Rute</option>
                                @if (isset($routes) && count($routes) > 0)
                                    @foreach ($routes as $route)
                                        <option value="{{ $route->id }}"
                                            {{ old('route_id', (string) $schedule->route_id) == (string) $route->id ? 'selected' : '' }}>
                                            {{ $route->origin }} - {{ $route->destination }}
                                            @if($route->status != 'ACTIVE')
                                                ({{ $route->status == 'WEATHER_ISSUE' ? 'Masalah Cuaca' : 'Tidak Aktif' }})
                                            @endif
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @error('route_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-2">Kapal</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-ship text-gray-400"></i>
                            </div>
                            <select id="ferry_id" name="ferry_id"
                                class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('ferry_id') border-red-500 @enderror">
                                <option value="">Pilih Kapal</option>
                                @if (isset($ferries) && count($ferries) > 0)
                                    @foreach ($ferries as $ferry)
                                        <option value="{{ $ferry->id }}"
                                            {{ old('ferry_id', $schedule->ferry_id) == $ferry->id ? 'selected' : '' }}>
                                            {{ $ferry->name }} ({{ $ferry->status }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @error('ferry_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="departure_time" class="block text-sm font-medium text-gray-700 mb-2">Waktu
                            Keberangkatan</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                            <input type="time" id="departure_time" name="departure_time"
                                value="{{ old('departure_time', isset($schedule->departure_time) ? date('H:i', strtotime($schedule->departure_time)) : '') }}"
                                class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('departure_time') border-red-500 @enderror">
                        </div>
                        @error('departure_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="arrival_time" class="block text-sm font-medium text-gray-700 mb-2">Estimasi Waktu
                            Tiba</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                            <input type="time" id="arrival_time" name="arrival_time"
                                value="{{ old('arrival_time', isset($schedule->arrival_time) ? date('H:i', strtotime($schedule->arrival_time)) : '') }}"
                                class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('arrival_time') border-red-500 @enderror">
                        </div>
                        @error('arrival_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label for="days" class="block text-sm font-medium text-gray-700 mb-2">Hari Operasi</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-7 gap-3">
                            @php
                                $scheduleDays = !empty($schedule->days) ? explode(',', $schedule->days) : [];
                            @endphp
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $index => $day)
                                @php
                                    $dayNumber = $index + 1;
                                    if ($dayNumber == 7) {
                                        $dayNumber = 0;
                                    } // Sunday is 0 in Carbon but we're storing as 7
                                    $isChecked = in_array((string) $dayNumber, $scheduleDays);
                                @endphp
                                <div class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg shadow-sm hover:bg-blue-50 transition-colors cursor-pointer">
                                    <input type="checkbox" id="day{{ $dayNumber }}" name="days[]"
                                        value="{{ $dayNumber }}" {{ $isChecked ? 'checked' : '' }} class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <label for="day{{ $dayNumber }}" class="ml-2 text-sm font-medium text-gray-900 cursor-pointer select-none">{{ $day }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('days')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        @if($isStatusFinal)
                            <input type="hidden" name="status" value="{{ $schedule->status }}">
                            <div class="flex items-center p-3 bg-gray-100 rounded-lg">
                                <span class="px-3 py-1.5 rounded-md bg-gray-200 text-gray-800 w-full flex items-center shadow-inner">
                                    <i class="fas fa-lock-alt mr-2 text-gray-500"></i>
                                    @if($schedule->status == 'FULL')
                                        Penuh (Status Final)
                                    @else
                                        Selesai (Status Final)
                                    @endif
                                </span>
                                <div class="ml-2 text-gray-500">
                                    <i class="fas fa-lock" title="Status final tidak dapat diubah"></i>
                                </div>
                            </div>
                            <p class="text-gray-500 text-xs mt-1">Status final tidak dapat diubah</p>
                        @else
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-toggle-on text-gray-400"></i>
                                </div>
                                <select id="status" name="status"
                                    class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('status') border-red-500 @enderror">
                                    <option value="ACTIVE"
                                        {{ old('status', strtoupper($schedule->status)) == 'ACTIVE' ? 'selected' : '' }}>
                                        Aktif
                                    </option>
                                    <option value="CANCELLED"
                                        {{ old('status', strtoupper($schedule->status)) == 'CANCELLED' ? 'selected' : '' }}>
                                        Tidak Aktif
                                    </option>
                                </select>
                            </div>
                            <div class="mt-2 text-xs text-gray-600">
                                <div class="flex items-start mb-1">
                                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-1"></i>
                                    <span>Perubahan status akan mempengaruhi tanggal jadwal terkait.</span>
                                </div>
                            </div>
                        @endif
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-5 rounded-lg transition shadow-sm flex items-center">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure at least one day is selected
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const dayCheckboxes = document.querySelectorAll('input[name="days[]"]:checked');
                if (dayCheckboxes.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu hari operasi.');
                }
            });

            // Make entire day container clickable
            const dayContainers = document.querySelectorAll('.flex.items-center.p-3.bg-gray-50');
            dayContainers.forEach(container => {
                container.addEventListener('click', function(e) {
                    // Don't handle if the click was on the checkbox itself
                    if (e.target.type !== 'checkbox') {
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        checkbox.checked = !checkbox.checked;
                    }
                });
            });
        });
    </script>
@endpush
