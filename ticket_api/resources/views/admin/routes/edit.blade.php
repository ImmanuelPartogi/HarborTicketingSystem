@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Rute</h1>
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

    <form action="{{ route('admin.routes.update', $route->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">Pelabuhan Asal <span class="text-red-500">*</span></label>
                <input type="text" id="origin" name="origin" value="{{ old('origin', $route->origin) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Pelabuhan Tujuan <span class="text-red-500">*</span></label>
                <input type="text" id="destination" name="destination" value="{{ old('destination', $route->destination) }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="distance" class="block text-sm font-medium text-gray-700 mb-1">Jarak (KM)</label>
                <input type="number" id="distance" name="distance" value="{{ old('distance', $route->distance) }}" step="0.01" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Durasi (Menit) <span class="text-red-500">*</span></label>
                <input type="number" id="duration" name="duration" value="{{ old('duration', $route->duration) }}" required min="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Dasar <span class="text-red-500">*</span></label>
                <input type="number" id="base_price" name="base_price" value="{{ old('base_price', $route->base_price) }}" required min="0" step="1000" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="ACTIVE" {{ old('status', $route->status) == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="INACTIVE" {{ old('status', $route->status) == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                    <option value="WEATHER_ISSUE" {{ old('status', $route->status) == 'WEATHER_ISSUE' ? 'selected' : '' }}>Masalah Cuaca</option>
                </select>
            </div>
        </div>

        <h3 class="text-lg font-semibold mt-6 mb-3">Harga Tambahan untuk Kendaraan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="motorcycle_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Motor <span class="text-red-500">*</span></label>
                <input type="number" id="motorcycle_price" name="motorcycle_price" value="{{ old('motorcycle_price', $route->motorcycle_price) }}" required min="0" step="1000" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="car_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Mobil <span class="text-red-500">*</span></label>
                <input type="number" id="car_price" name="car_price" value="{{ old('car_price', $route->car_price) }}" required min="0" step="1000" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="bus_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Bus <span class="text-red-500">*</span></label>
                <input type="number" id="bus_price" name="bus_price" value="{{ old('bus_price', $route->bus_price) }}" required min="0" step="1000" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="truck_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Truk <span class="text-red-500">*</span></label>
                <input type="number" id="truck_price" name="truck_price" value="{{ old('truck_price', $route->truck_price) }}" required min="0" step="1000" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('admin.routes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
