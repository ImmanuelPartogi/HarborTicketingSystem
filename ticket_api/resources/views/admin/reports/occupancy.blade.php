<!-- Occupancy Report Template -->
@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-xl p-5 sm:p-7 transition-all duration-300 hover:shadow-xl">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-ship text-indigo-600 mr-3"></i>
                Laporan Tingkat Okupansi Kapal
            </h1>
            <p class="text-gray-500 mt-1">Analisis kapasitas dan penggunaan armada kapal</p>
        </div>
        <a href="{{ route('admin.reports.export.occupancy') }}" class="flex items-center justify-center px-4 py-2.5 bg-green-600 text-white rounded-lg transition-all duration-300 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium text-sm">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </a>
    </div>

    <!-- Filter Section -->
    <div class="mb-8 bg-gray-50 rounded-xl p-5 shadow-sm">
        <form action="{{ route('admin.reports.occupancy') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <div>
                    <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-2">Kapal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-ship"></i>
                        </div>
                        <select id="ferry_id" name="ferry_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300">
                            <option value="">Semua Kapal</option>
                            @foreach($ferries as $ferry)
                            <option value="{{ $ferry->id }}" {{ request('ferry_id') == $ferry->id ? 'selected' : '' }}>
                                {{ $ferry->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-3 flex items-center justify-center transition-all duration-300">
                        <i class="fas fa-filter mr-2"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Chart Section -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-chart-pie text-indigo-500 mr-2"></i>
            Tingkat Okupansi per Kapal
        </h2>
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <canvas id="occupancyChart" height="100"></canvas>
        </div>
    </div>

    <!-- Desktop view - Table -->
    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapal</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Perjalanan</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas Total</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penumpang Terangkut</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Okupansi</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($occupancyData as $data)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="py-3.5 px-4 text-sm font-medium text-gray-900">
                        <div class="flex items-center">
                            <i class="fas fa-ship text-blue-500 mr-2"></i>
                            {{ $data->ferry_name }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                            {{ $data->origin }}
                            <i class="fas fa-long-arrow-alt-right mx-2 text-gray-400"></i>
                            {{ $data->destination }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-route text-purple-500 mr-2"></i>
                            {{ $data->trip_count }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-users text-indigo-500 mr-2"></i>
                            {{ $data->total_capacity }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-user-check text-green-500 mr-2"></i>
                            {{ $data->total_passengers }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm font-medium text-gray-900">
                        <div class="flex items-center">
                            <div class="w-16 bg-gray-200 rounded-full h-2.5 mr-2 overflow-hidden">
                                <div class="h-2.5 rounded-full
                                    @if($data->occupancy_rate >= 85) bg-green-500
                                    @elseif($data->occupancy_rate >= 70) bg-blue-500
                                    @elseif($data->occupancy_rate >= 50) bg-yellow-500
                                    @else bg-red-500 @endif"
                                    style="width: {{ min(100, $data->occupancy_rate) }}%">
                                </div>
                            </div>
                            {{ number_format($data->occupancy_rate, 1) }}%
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm">
                        @if($data->occupancy_rate >= 85)
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">Sangat Baik</span>
                        @elseif($data->occupancy_rate >= 70)
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">Baik</span>
                        @elseif($data->occupancy_rate >= 50)
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">Cukup</span>
                        @else
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">Rendah</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 px-4 text-center text-gray-500 bg-gray-50 italic">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                            <p>Tidak ada data untuk ditampilkan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile view - Cards -->
    <div class="md:hidden space-y-4">
        @forelse($occupancyData as $data)
        <div class="bg-white border rounded-xl shadow-sm p-4 transition-all duration-300 hover:shadow-md">
            <div class="flex justify-between items-center mb-3">
                <span class="font-medium text-gray-800 text-lg flex items-center">
                    <i class="fas fa-ship text-blue-500 mr-2"></i>
                    {{ $data->ferry_name }}
                </span>
                @if($data->occupancy_rate >= 85)
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">Sangat Baik</span>
                @elseif($data->occupancy_rate >= 70)
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">Baik</span>
                @elseif($data->occupancy_rate >= 50)
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">Cukup</span>
                @else
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">Rendah</span>
                @endif
            </div>
            <div class="mb-3">
                <p class="text-gray-600 flex items-center">
                    <i class="fas fa-map-marker-alt text-red-500 mr-1.5"></i>
                    {{ $data->origin }}
                    <i class="fas fa-long-arrow-alt-right mx-1.5 text-gray-400"></i>
                    {{ $data->destination }}
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                <div>
                    <p class="text-gray-500 mb-1">Total Perjalanan</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-route text-purple-500 mr-1.5"></i>
                        {{ $data->trip_count }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Kapasitas Total</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-users text-indigo-500 mr-1.5"></i>
                        {{ $data->total_capacity }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Penumpang</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user-check text-green-500 mr-1.5"></i>
                        {{ $data->total_passengers }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Okupansi</p>
                    <p class="font-medium text-gray-900">{{ number_format($data->occupancy_rate, 1) }}%</p>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                <div class="h-2.5 rounded-full
                    @if($data->occupancy_rate >= 85) bg-green-500
                    @elseif($data->occupancy_rate >= 70) bg-blue-500
                    @elseif($data->occupancy_rate >= 50) bg-yellow-500
                    @else bg-red-500 @endif"
                    style="width: {{ min(100, $data->occupancy_rate) }}%">
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border rounded-xl p-6 text-center text-gray-500 shadow-sm">
            <div class="flex flex-col items-center justify-center">
                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
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
        // Format data for the chart
        const ferryNames = @json($occupancyData->pluck('ferry_name'));
        const occupancyRates = @json($occupancyData->pluck('occupancy_rate'));
        const routeInfo = @json($occupancyData->map(function($item) {
            return $item->origin . ' - ' . $item->destination;
        }));

        // Create color array based on occupancy rates
        const colorArray = occupancyRates.map(rate => {
            if (rate >= 85) return 'rgba(16, 185, 129, 0.8)';  // Green
            if (rate >= 70) return 'rgba(79, 70, 229, 0.8)';   // Indigo/Blue
            if (rate >= 50) return 'rgba(245, 158, 11, 0.8)';  // Yellow/Amber
            return 'rgba(239, 68, 68, 0.8)';                   // Red
        });

        const ctx = document.getElementById('occupancyChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ferryNames,
                datasets: [{
                    label: 'Tingkat Okupansi (%)',
                    data: occupancyRates,
                    backgroundColor: colorArray,
                    borderColor: colorArray.map(color => color.replace('0.8', '1')),
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(156, 163, 175, 0.15)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        bodySpacing: 6,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        cornerRadius: 8,
                        callbacks: {
                            afterTitle: function(context) {
                                const index = context[0].dataIndex;
                                return routeInfo[index];
                            },
                            label: function(context) {
                                return 'Okupansi: ' + context.raw.toFixed(1) + '%';
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
