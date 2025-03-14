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
                    <p class="font-medium">{{ $schedule->departure_time ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Estimasi Tiba:</p>
                    <p class="font-medium">{{ $schedule->arrival_time ?? '-' }}</p>
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
                <div>
                    <p class="text-sm text-gray-600">Harga Ekonomi:</p>
                    <p class="font-medium">Rp {{ number_format($schedule->price_economy ?? 0, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Harga Bisnis:</p>
                    <p class="font-medium">Rp {{ number_format($schedule->price_business ?? 0, 0, ',', '.') }}</p>
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

                <!-- Perbaikan untuk field estimasi waktu tiba -->
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
                    <label for="price_economy" class="block text-sm font-medium text-gray-700 mb-2">Harga Ekonomi</label>
                    <input type="number" id="price_economy" name="price_economy"
                        value="{{ old('price_economy', $schedule->price_economy) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('price_economy') border-red-500 @enderror">
                    @error('price_economy')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="price_business" class="block text-sm font-medium text-gray-700 mb-2">Harga Bisnis</label>
                    <input type="number" id="price_business" name="price_business"
                        value="{{ old('price_business', $schedule->price_business) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('price_business') border-red-500 @enderror">
                    @error('price_business')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                        <option value="ACTIVE"
                            {{ old('status', strtoupper($schedule->status)) == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                        <option value="DELAYED"
                            {{ old('status', strtoupper($schedule->status)) == 'DELAYED' ? 'selected' : '' }}>Tertunda
                        </option>
                        <option value="FULL"
                            {{ old('status', strtoupper($schedule->status)) == 'FULL' ? 'selected' : '' }}>Penuh</option>
                        <option value="CANCELLED"
                            {{ old('status', strtoupper($schedule->status)) == 'CANCELLED' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
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
