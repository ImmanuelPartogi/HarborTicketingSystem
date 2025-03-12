@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Tambah Kapal Ferry Baru</h1>
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

    <form action="{{ route('admin.ferries.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kapal <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="MAINTENANCE" {{ old('status') == 'MAINTENANCE' ? 'selected' : '' }}>Pemeliharaan</option>
                    <option value="INACTIVE" {{ old('status') == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>

            <div>
                <label for="capacity_passenger" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Penumpang <span class="text-red-500">*</span></label>
                <input type="number" id="capacity_passenger" name="capacity_passenger" value="{{ old('capacity_passenger') }}" required min="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="capacity_vehicle_motorcycle" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Motor <span class="text-red-500">*</span></label>
                <input type="number" id="capacity_vehicle_motorcycle" name="capacity_vehicle_motorcycle" value="{{ old('capacity_vehicle_motorcycle') }}" required min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="capacity_vehicle_car" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Mobil <span class="text-red-500">*</span></label>
                <input type="number" id="capacity_vehicle_car" name="capacity_vehicle_car" value="{{ old('capacity_vehicle_car') }}" required min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="capacity_vehicle_bus" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Bus <span class="text-red-500">*</span></label>
                <input type="number" id="capacity_vehicle_bus" name="capacity_vehicle_bus" value="{{ old('capacity_vehicle_bus') }}" required min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="capacity_vehicle_truck" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Truk <span class="text-red-500">*</span></label>
                <input type="number" id="capacity_vehicle_truck" name="capacity_vehicle_truck" value="{{ old('capacity_vehicle_truck') }}" required min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Foto Kapal</label>
                <input type="file" id="image" name="image" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
        </div>

        <div class="mt-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea id="description" name="description" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('description') }}</textarea>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('admin.ferries.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
