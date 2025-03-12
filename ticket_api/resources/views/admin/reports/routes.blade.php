@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Laporan Performa Rute</h1>
        <a href="{{ route('admin.reports.export.routes') }}" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </a>
    </div>

    <div class="mb-6">
        <form action="{{ route('admin.reports.routes') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from', date('Y-m-01')) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Hingga Tanggal</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to', date('Y-m-t')) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
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
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rute</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jumlah Pemesanan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jumlah Penumpang</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Pendapatan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rata-rata Harga Tiket</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rata-rata Penumpang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routeStats as $route)
                <tr>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->origin }} - {{ $route->destination }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->booking_count }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->passenger_count }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($route->revenue, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($route->revenue / $route->passenger_count, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ number_format($route->passenger_count / $route->booking_count, 1) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data untuk ditampilkan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
