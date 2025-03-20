@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Edit Jadwal</h1>
            <a href="{{ route('admin.schedules.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h2 class="text-lg font-semibold mb-2">Data Jadwal Saat Ini</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Rute:</p>
                    <p class="font-medium">
                        @if (isset($schedule->route) && is_object($schedule->route))
                            {{ $schedule->route->origin }} - {{ $schedule->route->destination }}
                            @if($schedule->route->status != 'ACTIVE')
                                <span class="ml-2 px-2 py-1 rounded-full text-xs {{ $schedule->route->status == 'WEATHER_ISSUE' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $schedule->route->status == 'WEATHER_ISSUE' ? 'Masalah Cuaca' : 'Tidak Aktif' }}
                                </span>
                            @endif
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Kapal:</p>
                    <p class="font-medium">
                        @if (isset($schedule->ferry) && is_object($schedule->ferry))
                            {{ $schedule->ferry->name }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Waktu Keberangkatan:</p>
                    <p class="font-medium">{{ $schedule->departure_time->format('H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Estimasi Tiba:</p>
                    <p class="font-medium">{{ $schedule->arrival_time->format('H:i') ?? '-' }}</p>
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
                        @elseif($schedule->status == 'DEPARTED')
                            <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                                Selesai
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                Tidak Aktif
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Hari Operasi:</p>
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
                        {{ !empty($dayLabels) ? implode(', ', $dayLabels) : '-' }}
                    </p>
                </div>
            </div>
        </div>

        @php
            $isStatusFinal = in_array($schedule->status, ['FULL', 'DEPARTED']);
        @endphp

        @if($isStatusFinal)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
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
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
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

        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label for="route_id" class="block text-sm font-medium text-gray-700 mb-2">Rute</label>
                    <select id="route_id" name="route_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('route_id') border-red-500 @enderror">
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
                    @error('route_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-2">Kapal</label>
                    <select id="ferry_id" name="ferry_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('ferry_id') border-red-500 @enderror">
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
                    @error('ferry_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="departure_time" class="block text-sm font-medium text-gray-700 mb-2">Waktu
                        Keberangkatan</label>
                    <input type="time" id="departure_time" name="departure_time"
                        value="{{ old('departure_time', isset($schedule->departure_time) ? date('H:i', strtotime($schedule->departure_time)) : '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('departure_time') border-red-500 @enderror">
                    @error('departure_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="arrival_time" class="block text-sm font-medium text-gray-700 mb-2">Estimasi Waktu
                        Tiba</label>
                    <input type="time" id="arrival_time" name="arrival_time"
                        value="{{ old('arrival_time', isset($schedule->arrival_time) ? date('H:i', strtotime($schedule->arrival_time)) : '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('arrival_time') border-red-500 @enderror">
                    @error('arrival_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="days" class="block text-sm font-medium text-gray-700 mb-2">Hari Operasi</label>
                    <div class="grid grid-cols-7 gap-2">
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
                            <div class="flex items-center">
                                <input type="checkbox" id="day{{ $dayNumber }}" name="days[]"
                                    value="{{ $dayNumber }}" {{ $isChecked ? 'checked' : '' }} class="mr-2">
                                <label for="day{{ $dayNumber }}">{{ $day }}</label>
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
                        <div class="flex items-center py-2">
                            <span class="px-3 py-2 rounded-md bg-gray-100 text-gray-800 w-full">
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
                        <select id="status" name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                            <option value="ACTIVE"
                                {{ old('status', strtoupper($schedule->status)) == 'ACTIVE' ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="CANCELLED"
                                {{ old('status', strtoupper($schedule->status)) == 'CANCELLED' ? 'selected' : '' }}>
                                Tidak Aktif
                            </option>
                        </select>
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
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
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
        });
    </script>
@endpush
