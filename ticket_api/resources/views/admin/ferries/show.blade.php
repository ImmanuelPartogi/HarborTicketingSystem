@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Kapal Ferry</h1>
        <div>
            <a href="{{ route('admin.ferries.edit', $ferry->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded mr-2">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('admin.ferries.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="col-span-1">
            <div class="bg-gray-100 p-4 rounded-lg">
                @if($ferry->image)
                    <img src="{{ asset('storage/' . $ferry->image) }}" alt="{{ $ferry->name }}" class="w-full h-auto rounded-lg">
                @else
                    <div class="bg-gray-300 h-48 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ship text-gray-500 text-5xl"></i>
                    </div>
                @endif
                <h2 class="text-xl font-bold mt-4">{{ $ferry->name }}</h2>
                <div class="mt-2">
                    <span class="px-2 py-1 rounded-full text-xs {{ $ferry->status == 'ACTIVE' ? 'bg-green-100 text-green-800' : ($ferry->status == 'MAINTENANCE' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $ferry->status == 'ACTIVE' ? 'Aktif' : ($ferry->status == 'MAINTENANCE' ? 'Pemeliharaan' : 'Tidak Aktif') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-span-2">
            <div class="bg-gray-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3">Informasi Kapasitas</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <i class="fas fa-users text-blue-600 mr-3 text-xl"></i>
                        <div>
                            <p class="text-sm text-gray-500">Kapasitas Penumpang</p>
                            <p class="font-bold">{{ number_format($ferry->capacity_passenger) }} Orang</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-motorcycle text-blue-600 mr-3 text-xl"></i>
                        <div>
                            <p class="text-sm text-gray-500">Kapasitas Motor</p>
                            <p class="font-bold">{{ number_format($ferry->capacity_vehicle_motorcycle) }} Unit</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-car text-blue-600 mr-3 text-xl"></i>
                        <div>
                            <p class="text-sm text-gray-500">Kapasitas Mobil</p>
                            <p class="font-bold">{{ number_format($ferry->capacity_vehicle_car) }} Unit</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-bus text-blue-600 mr-3 text-xl"></i>
                        <div>
                            <p class="text-sm text-gray-500">Kapasitas Bus</p>
                            <p class="font-bold">{{ number_format($ferry->capacity_vehicle_bus) }} Unit</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-truck text-blue-600 mr-3 text-xl"></i>
                        <div>
                            <p class="text-sm text-gray-500">Kapasitas Truk</p>
                            <p class="font-bold">{{ number_format($ferry->capacity_vehicle_truck) }} Unit</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($ferry->description)
            <div class="bg-gray-100 p-4 rounded-lg mt-4">
                <h3 class="text-lg font-semibold mb-3">Deskripsi</h3>
                <p>{{ $ferry->description }}</p>
            </div>
            @endif

            <div class="bg-gray-100 p-4 rounded-lg mt-4">
                <h3 class="text-lg font-semibold mb-3">Informasi Tambahan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Dibuat Pada</p>
                        <p>{{ $ferry->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Terakhir Diperbarui</p>
                        <p>{{ $ferry->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-xl font-semibold mb-4">Jadwal Keberangkatan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rute</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Hari</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Waktu</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ferry->schedules as $schedule)
                    <tr>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $schedule->route->origin }} - {{ $schedule->route->destination }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                            {{ $schedule->days }}
                        </td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs {{ $schedule->status == 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $schedule->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada jadwal keberangkatan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
