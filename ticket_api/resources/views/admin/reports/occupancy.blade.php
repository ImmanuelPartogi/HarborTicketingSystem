@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Laporan Tingkat Okupansi Kapal</h1>
        <a href="{{ route('admin.reports.export.occupancy') }}" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </a>
    </div>

    <div class="mb-6">
        <form action="{{ route('admin.reports.occupancy') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from', date('Y-m-01')) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Hingga Tanggal</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to', date('Y-m-t')) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div>
                <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-1">Kapal</label>
                <select id="ferry_id" name="ferry_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Semua Kapal</option>
                    @foreach($ferries as $ferry)
                    <option value="{{ $ferry->id }}" {{ request('ferry_id') == $ferry->id ? 'selected' : '' }}>
                        {{ $ferry->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                    Tampilkan
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Kapal</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rute</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Total Perjalanan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Kapasitas Total</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Penumpang Terangkut</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rata-rata Okupansi</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($occupancyData as $data)
                <tr>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $data->ferry_name }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $data->origin }} - {{ $data->destination }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $data->trip_count }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $data->total_capacity }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $data->total_passengers }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ number_format($data->occupancy_rate, 1) }}%</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        @if($data->occupancy_rate >= 85)
                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Sangat Baik</span>
                        @elseif($data->occupancy_rate >= 70)
                        <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Baik</span>
                        @elseif($data->occupancy_rate >= 50)
                        <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Cukup</span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Rendah</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data untuk ditampilkan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
