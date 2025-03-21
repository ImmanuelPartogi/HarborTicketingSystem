@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-xl p-5 sm:p-7 transition-all duration-300 hover:shadow-xl">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-route text-indigo-600 mr-3"></i>
                Laporan Performa Rute
            </h1>
            <p class="text-gray-500 mt-1">Analisa performa rute perjalanan berdasarkan periode</p>
        </div>
        <a href="{{ route('admin.reports.export.routes') }}" class="flex items-center justify-center px-4 py-2.5 bg-green-600 text-white rounded-lg transition-all duration-300 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium text-sm">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </a>
    </div>

    <!-- Filter Section -->
    <div class="mb-8 bg-gray-50 rounded-xl p-5 shadow-sm">
        <form action="{{ route('admin.reports.routes') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from', date('Y-m-01')) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300">
                    </div>
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Hingga Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to', date('Y-m-t')) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300">
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full sm:w-auto text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-3 flex items-center justify-center transition-all duration-300">
                        <i class="fas fa-filter mr-2"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-50 p-5 rounded-xl border border-blue-200 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center mb-3">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-ship text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-blue-700">Total Rute Aktif</h3>
            </div>
            <p class="text-3xl font-bold text-blue-800">{{ count($routeStats) }}</p>
        </div>
        <div class="bg-green-50 p-5 rounded-xl border border-green-200 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center mb-3">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-green-700">Total Penumpang</h3>
            </div>
            <p class="text-3xl font-bold text-green-800">{{ $routeStats->sum('passenger_count') }}</p>
        </div>
        <div class="bg-purple-50 p-5 rounded-xl border border-purple-200 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center mb-3">
                <div class="bg-purple-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-purple-700">Total Pendapatan</h3>
            </div>
            <p class="text-3xl font-bold text-purple-800">Rp {{ number_format($routeStats->sum('revenue'), 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="mb-8 bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <h2 class="text-xl font-semibold mb-4">Visualisasi Performa Rute</h2>
        <canvas id="routePerformanceChart" height="80"></canvas>
    </div>

    <!-- Desktop view - Table -->
    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Pemesanan</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Penumpang</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Harga Tiket</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Penumpang</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($routeStats as $route)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="py-4 px-4 text-sm font-medium text-gray-900">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                            {{ $route->origin }}
                            <i class="fas fa-long-arrow-alt-right mx-2 text-gray-400"></i>
                            {{ $route->destination }}
                        </div>
                    </td>
                    <td class="py-4 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-ticket-alt text-blue-500 mr-2"></i>
                            {{ $route->booking_count }}
                        </div>
                    </td>
                    <td class="py-4 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-users text-indigo-500 mr-2"></i>
                            {{ $route->passenger_count }}
                        </div>
                    </td>
                    <td class="py-4 px-4 text-sm font-medium text-gray-900">
                        <div class="flex items-center">
                            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                            Rp {{ number_format($route->revenue, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="py-4 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-tag text-orange-500 mr-2"></i>
                            Rp {{ number_format($route->passenger_count > 0 ? $route->revenue / $route->passenger_count : 0, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="py-4 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-user-friends text-purple-500 mr-2"></i>
                            {{ number_format($route->booking_count > 0 ? $route->passenger_count / $route->booking_count : 0, 1) }}
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 px-4 text-center text-gray-500 bg-gray-50 italic">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-search text-4xl mb-3 text-gray-300"></i>
                            <p>Tidak ada data untuk ditampilkan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile view - Cards -->
    <div class="md:hidden space-y-5">
        @forelse($routeStats as $route)
        <div class="bg-white border rounded-xl shadow-sm p-5 transition-all duration-300 hover:shadow-md">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-lg text-gray-800">
                        <i class="fas fa-route text-indigo-600 mr-2"></i> Rute
                    </h3>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                        {{ $route->booking_count }} Booking
                    </span>
                </div>
                <p class="text-gray-700 flex items-center">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                    {{ $route->origin }}
                    <i class="fas fa-long-arrow-alt-right mx-2 text-gray-400"></i>
                    {{ $route->destination }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div>
                    <p class="text-gray-500 mb-1">Jumlah Penumpang</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-users text-indigo-500 mr-1.5"></i>
                        {{ $route->passenger_count }} orang
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Pendapatan</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-money-bill-wave text-green-500 mr-1.5"></i>
                        Rp {{ number_format($route->revenue, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Rata-rata Harga Tiket</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-tag text-orange-500 mr-1.5"></i>
                        Rp {{ number_format($route->passenger_count > 0 ? $route->revenue / $route->passenger_count : 0, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Rata-rata Penumpang</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user-friends text-purple-500 mr-1.5"></i>
                        {{ number_format($route->booking_count > 0 ? $route->passenger_count / $route->booking_count : 0, 1) }}
                    </p>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-chart-line mr-1"></i> Performa Route
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.reports.details', ['route_id' => $route->id]) }}" class="px-3 py-1.5 text-xs bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-lg transition-colors duration-200">
                            <i class="fas fa-chart-bar mr-1"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border rounded-xl p-8 text-center text-gray-500 shadow-sm">
            <div class="flex flex-col items-center justify-center">
                <i class="fas fa-search text-4xl mb-3 text-gray-300"></i>
                <p>Tidak ada data untuk ditampilkan</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data for chart
        const routeLabels = @json($routeStats->pluck('origin')->map(function($item, $key) use ($routeStats) {
            return $item . ' - ' . $routeStats[$key]->destination;
        }));
        const passengerData = @json($routeStats->pluck('passenger_count'));
        const revenueData = @json($routeStats->pluck('revenue'));

        // Set up the chart
        const ctx = document.getElementById('routePerformanceChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: routeLabels,
                datasets: [
                    {
                        label: 'Jumlah Penumpang',
                        data: passengerData,
                        backgroundColor: 'rgba(99, 102, 241, 0.5)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pendapatan (dalam juta Rp)',
                        data: revenueData.map(revenue => revenue / 1000000), // Convert to millions
                        backgroundColor: 'rgba(16, 185, 129, 0.5)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        type: 'bar'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah'
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.raw;

                                if (label.includes('Pendapatan')) {
                                    return label + ': Rp ' + (value * 1000000).toLocaleString('id-ID');
                                } else {
                                    return label + ': ' + value;
                                }
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
