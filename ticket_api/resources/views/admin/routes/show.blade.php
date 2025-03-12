@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Rute</h1>
        <div>
            <a href="{{ route('admin.routes.edit', $route->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded mr-2">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('admin.routes.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

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
                        <span class="px-2 py-1 rounded-full text-xs {{ $route->status == 'ACTIVE' ? 'bg-green-100 text-green-800' : ($route->status == 'WEATHER_ISSUE' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
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

    <div class="mt-6">
        <h3 class="text-xl font-semibold mb-4">Jadwal Keberangkatan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Kapal</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Hari</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Waktu Keberangkatan</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Waktu Kedatangan</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($route->schedules as $schedule)
                    <tr>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $schedule->ferry->name }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $schedule->days }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs {{ $schedule->status == 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $schedule->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada jadwal keberangkatan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
