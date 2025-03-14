@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Jadwal</h1>
        <a href="{{ route('admin.schedules.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="mb-4">
                <label for="route_id" class="block text-sm font-medium text-gray-700 mb-2">Rute</label>
                <select id="route_id" name="route_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('route_id') border-red-500 @enderror">
                    <option value="">Pilih Rute</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ old('route_id', $schedule->route_id) == $route->id ? 'selected' : '' }}>
                            {{ $route->origin }} - {{ $route->destination }}
                        </option>
                    @endforeach
                </select>
                @error('route_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-2">Kapal</label>
                <select id="ferry_id" name="ferry_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('ferry_id') border-red-500 @enderror">
                    <option value="">Pilih Kapal</option>
                    @foreach($ferries as $ferry)
                        <option value="{{ $ferry->id }}" {{ old('ferry_id', $schedule->ferry_id) == $ferry->id ? 'selected' : '' }}>
                            {{ $ferry->name }} ({{ $ferry->status }})
                        </option>
                    @endforeach
                </select>
                @error('ferry_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="departure_time" class="block text-sm font-medium text-gray-700 mb-2">Waktu Keberangkatan</label>
                <input type="time" id="departure_time" name="departure_time" value="{{ old('departure_time', $schedule->departure_time) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('departure_time') border-red-500 @enderror">
                @error('departure_time')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="arrival_time" class="block text-sm font-medium text-gray-700 mb-2">Estimasi Waktu Tiba</label>
                <input type="time" id="arrival_time" name="arrival_time" value="{{ old('arrival_time', $schedule->arrival_time) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('arrival_time') border-red-500 @enderror">
                @error('arrival_time')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="days" class="block text-sm font-medium text-gray-700 mb-2">Hari Operasi</label>
                <div class="grid grid-cols-7 gap-2">
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $index => $day)
                        @php
                            $dayNumber = $index + 1;
                            if ($dayNumber == 7) $dayNumber = 0; // Sunday is 0 in Carbon but we're storing as 7
                            $isChecked = in_array($dayNumber, explode(',', $schedule->days ?? ''));
                        @endphp
                        <div class="flex items-center">
                            <input type="checkbox" id="day{{ $dayNumber }}" name="days[]" value="{{ $dayNumber }}"
                                {{ $isChecked ? 'checked' : '' }} class="mr-2">
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
                <input type="number" id="price_economy" name="price_economy" value="{{ old('price_economy', $schedule->price_economy) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('price_economy') border-red-500 @enderror">
                @error('price_economy')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="price_business" class="block text-sm font-medium text-gray-700 mb-2">Harga Bisnis</label>
                <input type="number" id="price_business" name="price_business" value="{{ old('price_business', $schedule->price_business) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('price_business') border-red-500 @enderror">
                @error('price_business')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                    <option value="ACTIVE" {{ old('status', $schedule->status) == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="INACTIVE" {{ old('status', $schedule->status) == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
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
    // Any additional JavaScript for this page
</script>
@endpush
