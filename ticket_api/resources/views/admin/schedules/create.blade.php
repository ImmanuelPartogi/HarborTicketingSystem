@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Tambah Jadwal Baru</h1>
    </div>

    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.schedules.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="route_id" class="block text-sm font-medium text-gray-700 mb-1">Rute <span class="text-red-500">*</span></label>
                <select id="route_id" name="route_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Pilih Rute</option>
                    @foreach($routes as $route)
                    <option value="{{ $route->id }}" {{ old('route_id') == $route->id ? 'selected' : '' }}>
                        {{ $route->origin }} - {{ $route->destination }} ({{ $route->duration }} menit)
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-1">Kapal <span class="text-red-500">*</span></label>
                <select id="ferry_id" name="ferry_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Pilih Kapal</option>
                    @foreach($ferries as $ferry)
                    <option value="{{ $ferry->id }}" {{ old('ferry_id') == $ferry->id ? 'selected' : '' }}>
                        {{ $ferry->name }} (Kapasitas: {{ $ferry->capacity_passenger }} penumpang)
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="departure_time" class="block text-sm font-medium text-gray-700 mb-1">Waktu Keberangkatan <span class="text-red-500">*</span></label>
                <input type="time" id="departure_time" name="departure_time" value="{{ old('departure_time') }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="arrival_time" class="block text-sm font-medium text-gray-700 mb-1">Waktu Kedatangan <span class="text-red-500">*</span></label>
                <input type="time" id="arrival_time" name="arrival_time" value="{{ old('arrival_time') }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="CANCELLED" {{ old('status') == 'CANCELLED' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="DELAYED" {{ old('status') == 'DELAYED' ? 'selected' : '' }}>Tertunda</option>
                    <option value="FULL" {{ old('status') == 'FULL' ? 'selected' : '' }}>Penuh</option>
                </select>
            </div>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Hari Operasional <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-7 gap-4">
                <div>
                    <input type="checkbox" id="day_1" name="days[]" value="1" class="w-4 h-4 text-blue-600" {{ is_array(old('days')) && in_array('1', old('days')) ? 'checked' : '' }}>
                    <label for="day_1" class="ml-2 text-sm font-medium text-gray-700">Senin</label>
                </div>
                <div>
                    <input type="checkbox" id="day_2" name="days[]" value="2" class="w-4 h-4 text-blue-600" {{ is_array(old('days')) && in_array('2', old('days')) ? 'checked' : '' }}>
                    <label for="day_2" class="ml-2 text-sm font-medium text-gray-700">Selasa</label>
                </div>
                <div>
                    <input type="checkbox" id="day_3" name="days[]" value="3" class="w-4 h-4 text-blue-600" {{ is_array(old('days')) && in_array('3', old('days')) ? 'checked' : '' }}>
                    <label for="day_3" class="ml-2 text-sm font-medium text-gray-700">Rabu</label>
                </div>
                <div>
                    <input type="checkbox" id="day_4" name="days[]" value="4" class="w-4 h-4 text-blue-600" {{ is_array(old('days')) && in_array('4', old('days')) ? 'checked' : '' }}>
                    <label for="day_4" class="ml-2 text-sm font-medium text-gray-700">Kamis</label>
                </div>
                <div>
                    <input type="checkbox" id="day_5" name="days[]" value="5" class="w-4 h-4 text-blue-600" {{ is_array(old('days')) && in_array('5', old('days')) ? 'checked' : '' }}>
                    <label for="day_5" class="ml-2 text-sm font-medium text-gray-700">Jumat</label>
                </div>
                <div>
                    <input type="checkbox" id="day_6" name="days[]" value="6" class="w-4 h-4 text-blue-600" {{ is_array(old('days')) && in_array('6', old('days')) ? 'checked' : '' }}>
                    <label for="day_6" class="ml-2 text-sm font-medium text-gray-700">Sabtu</label>
                </div>
                <div>
                    <input type="checkbox" id="day_7" name="days[]" value="7" class="w-4 h-4 text-blue-600" {{ is_array(old('days')) && in_array('7', old('days')) ? 'checked' : '' }}>
                    <label for="day_7" class="ml-2 text-sm font-medium text-gray-700">Minggu</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('admin.schedules.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
