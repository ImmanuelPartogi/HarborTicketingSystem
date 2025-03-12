@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Laporan Bulanan</h1>
        <a href="{{ route('admin.reports.export.monthly') }}?year={{ $year }}&month={{ $month }}" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </a>
    </div>

    <div class="mb-6">
        <form action="{{ route('admin.reports.monthly') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select id="month" name="month" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                    </option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select id="year" name="year" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                    Tampilkan
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-700">Total Pemesanan</h3>
            <p class="text-3xl font-bold mt-2">{{ $bookingsCount }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h3 class="text-lg font-semibold text-green-700">Total Pendapatan</h3>
            <p class="text-3xl font-bold mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <h3 class="text-lg font-semibold text-purple-700">Total Penumpang</h3>
            <p class="text-3xl font-bold mt-2">{{ $passengerCount }}</p>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Pendapatan Harian</h2>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <canvas id="dailyRevenueChart" height="100"></canvas>
        </div>
    </div>

    <h2 class="text-xl font-semibold mb-4">Performa Rute</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rute</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jumlah Pemesanan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jumlah Penumpang</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Pendapatan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rata-rata per Booking</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routeStats as $route)
                <tr>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->origin }} - {{ $route->destination }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->booking_count }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->passenger_count }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($route->revenue, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($route->booking_count > 0 ? $route->revenue / $route->booking_count : 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data untuk ditampilkan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('dailyRevenueChart').getContext('2d');

        const dailyData = @json($dailyRevenue);
        const labels = dailyData.map(item => item.date);
        const values = dailyData.map(item => item.total);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan Harian (Rp)',
                    data: values,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Pendapatan: Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
@endsection
