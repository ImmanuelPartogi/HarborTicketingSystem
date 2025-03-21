<!-- Improved Occupancy Report Template -->
@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-xl rounded-2xl p-6 sm:p-8 transition-all duration-300">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 pb-6 border-b border-gray-100">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center group">
                <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-4 transition-all duration-300 group-hover:bg-indigo-200">
                    <i class="fas fa-ship text-xl"></i>
                </span>
                Laporan Tingkat Okupansi Kapal
            </h1>
            <p class="text-gray-500 mt-2 ml-1">Analisis kapasitas dan penggunaan armada kapal</p>
        </div>
        <a href="{{ route('admin.reports.export.occupancy') }}" class="flex items-center justify-center px-5 py-3 bg-emerald-600 text-white rounded-xl transition-all duration-300 hover:bg-emerald-700 hover:shadow-md focus:ring-4 focus:ring-emerald-300 font-medium text-sm">
            <i class="fas fa-file-excel mr-2.5"></i> Export Excel
        </a>
    </div>

    <!-- Filter Section -->
    <div class="mb-10 bg-gray-50 rounded-xl p-6 shadow-sm border border-gray-100">
        <form action="{{ route('admin.reports.occupancy') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-gray-700 mb-2">Dari Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-indigo-600">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from', date('Y-m-01')) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3.5 transition-all duration-200 hover:border-indigo-300 shadow-sm">
                    </div>
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-semibold text-gray-700 mb-2">Hingga Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-indigo-600">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to', date('Y-m-t')) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3.5 transition-all duration-200 hover:border-indigo-300 shadow-sm">
                    </div>
                </div>
                <div>
                    <label for="ferry_id" class="block text-sm font-semibold text-gray-700 mb-2">Kapal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-indigo-600">
                            <i class="fas fa-ship"></i>
                        </div>
                        <select id="ferry_id" name="ferry_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3.5 transition-all duration-200 hover:border-indigo-300 shadow-sm appearance-none">
                            <option value="">Semua Kapal</option>
                            @foreach($ferries as $ferry)
                            <option value="{{ $ferry->id }}" {{ request('ferry_id') == $ferry->id ? 'selected' : '' }}>
                                {{ $ferry->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-xl text-sm px-6 py-3.5 flex items-center justify-center transition-all duration-300 hover:shadow-md">
                        <i class="fas fa-filter mr-2.5"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Chart Section -->
    <div class="mb-10">
        <h2 class="text-xl font-semibold mb-5 flex items-center">
            <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg mr-3">
                <i class="fas fa-chart-pie"></i>
            </span>
            Tingkat Okupansi per Kapal
        </h2>
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
            <canvas id="occupancyChart" height="110"></canvas>
        </div>
    </div>

    <!-- Desktop view - Table -->
    <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50">
                    <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapal</th>
                    <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                    <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Perjalanan</th>
                    <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas Total</th>
                    <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penumpang Terangkut</th>
                    <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Okupansi</th>
                    <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($occupancyData as $data)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="py-4 px-5 text-sm font-medium text-gray-900">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-blue-600">
                                <i class="fas fa-ship"></i>
                            </div>
                            {{ $data->ferry_name }}
                        </div>
                    </td>
                    <td class="py-4 px-5 text-sm text-gray-600">
                        <div class="flex items-center">
                            <div class="w-7 h-7 bg-red-100 rounded-full flex items-center justify-center mr-2 text-red-600">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                            <span>{{ $data->origin }}</span>
                            <span class="mx-2 text-gray-400"><i class="fas fa-long-arrow-alt-right"></i></span>
                            <span>{{ $data->destination }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-5 text-sm text-gray-600">
                        <div class="flex items-center">
                            <div class="w-7 h-7 bg-purple-100 rounded-full flex items-center justify-center mr-3 text-purple-600">
                                <i class="fas fa-route text-xs"></i>
                            </div>
                            {{ $data->trip_count }}
                        </div>
                    </td>
                    <td class="py-4 px-5 text-sm text-gray-600">
                        <div class="flex items-center">
                            <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center mr-3 text-indigo-600">
                                <i class="fas fa-users text-xs"></i>
                            </div>
                            {{ $data->total_capacity }}
                        </div>
                    </td>
                    <td class="py-4 px-5 text-sm text-gray-600">
                        <div class="flex items-center">
                            <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center mr-3 text-green-600">
                                <i class="fas fa-user-check text-xs"></i>
                            </div>
                            {{ $data->total_passengers }}
                        </div>
                    </td>
                    <td class="py-4 px-5 text-sm font-medium text-gray-900">
                        <div class="flex items-center">
                            <div class="w-20 bg-gray-200 rounded-full h-3 mr-3 overflow-hidden">
                                <div class="h-3 rounded-full
                                    @if($data->occupancy_rate >= 85) bg-gradient-to-r from-green-400 to-green-600
                                    @elseif($data->occupancy_rate >= 70) bg-gradient-to-r from-blue-400 to-blue-600
                                    @elseif($data->occupancy_rate >= 50) bg-gradient-to-r from-yellow-400 to-yellow-600
                                    @else bg-gradient-to-r from-red-400 to-red-600 @endif"
                                    style="width: {{ min(100, $data->occupancy_rate) }}%">
                                </div>
                            </div>
                            {{ number_format($data->occupancy_rate, 1) }}%
                        </div>
                    </td>
                    <td class="py-4 px-5 text-sm">
                        @if($data->occupancy_rate >= 85)
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                            <i class="fas fa-check-circle mr-1"></i> Sangat Baik
                        </span>
                        @elseif($data->occupancy_rate >= 70)
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                            <i class="fas fa-thumbs-up mr-1"></i> Baik
                        </span>
                        @elseif($data->occupancy_rate >= 50)
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                            <i class="fas fa-exclamation-circle mr-1"></i> Cukup
                        </span>
                        @else
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Rendah
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-10 px-5 text-center text-gray-500 bg-gray-50 italic">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-inbox text-3xl text-gray-400"></i>
                            </div>
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
        <div class="bg-white border rounded-xl shadow-sm p-5 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
            <div class="flex justify-between items-center mb-4">
                <span class="font-medium text-gray-800 text-lg flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-blue-600">
                        <i class="fas fa-ship"></i>
                    </div>
                    {{ $data->ferry_name }}
                </span>
                @if($data->occupancy_rate >= 85)
                <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                    <i class="fas fa-check-circle mr-1"></i> Sangat Baik
                </span>
                @elseif($data->occupancy_rate >= 70)
                <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                    <i class="fas fa-thumbs-up mr-1"></i> Baik
                </span>
                @elseif($data->occupancy_rate >= 50)
                <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                    <i class="fas fa-exclamation-circle mr-1"></i> Cukup
                </span>
                @else
                <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Rendah
                </span>
                @endif
            </div>
            <div class="mb-4 bg-gray-50 rounded-lg p-3">
                <p class="text-gray-500 mb-1 text-xs font-medium">Rute</p>
                <p class="font-medium text-gray-900 flex items-center">
                    <i class="fas fa-map-marker-alt text-red-500 mr-1.5"></i>
                    {{ $data->origin }}
                    <i class="fas fa-long-arrow-alt-right mx-1.5 text-gray-400"></i>
                    {{ $data->destination }}
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-gray-500 mb-1 text-xs font-medium">Total Perjalanan</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-route text-purple-500 mr-1.5"></i>
                        {{ $data->trip_count }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-gray-500 mb-1 text-xs font-medium">Kapasitas Total</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-users text-indigo-500 mr-1.5"></i>
                        {{ $data->total_capacity }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-gray-500 mb-1 text-xs font-medium">Penumpang</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user-check text-green-500 mr-1.5"></i>
                        {{ $data->total_passengers }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-gray-500 mb-1 text-xs font-medium">Okupansi</p>
                    <p class="font-medium text-gray-900">{{ number_format($data->occupancy_rate, 1) }}%</p>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full transition-all duration-300
                    @if($data->occupancy_rate >= 85) bg-gradient-to-r from-green-400 to-green-600
                    @elseif($data->occupancy_rate >= 70) bg-gradient-to-r from-blue-400 to-blue-600
                    @elseif($data->occupancy_rate >= 50) bg-gradient-to-r from-yellow-400 to-yellow-600
                    @else bg-gradient-to-r from-red-400 to-red-600 @endif"
                    style="width: {{ min(100, $data->occupancy_rate) }}%">
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border rounded-xl p-8 text-center text-gray-500 shadow-sm">
            <div class="flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-inbox text-3xl text-gray-400"></i>
                </div>
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
        // Define common chart colors
        const colors = {
            green: {
                primary: 'rgba(16, 185, 129, 0.8)',
                border: 'rgba(16, 185, 129, 1)'
            },
            blue: {
                primary: 'rgba(59, 130, 246, 0.8)',
                border: 'rgba(59, 130, 246, 1)'
            },
            yellow: {
                primary: 'rgba(245, 158, 11, 0.8)',
                border: 'rgba(245, 158, 11, 1)'
            },
            red: {
                primary: 'rgba(239, 68, 68, 0.8)',
                border: 'rgba(239, 68, 68, 1)'
            }
        };

        // Format data for the chart
        const ferryNames = @json($occupancyData->pluck('ferry_name'));
        const occupancyRates = @json($occupancyData->pluck('occupancy_rate'));
        const routeInfo = @json($occupancyData->map(function($item) {
            return $item->origin . ' - ' . $item->destination;
        }));
        const totalCapacities = @json($occupancyData->pluck('total_capacity'));
        const totalPassengers = @json($occupancyData->pluck('total_passengers'));

        // Create color array based on occupancy rates
        const colorArray = occupancyRates.map(rate => {
            if (rate >= 85) return colors.green.primary;
            if (rate >= 70) return colors.blue.primary;
            if (rate >= 50) return colors.yellow.primary;
            return colors.red.primary;
        });

        const borderColorArray = occupancyRates.map(rate => {
            if (rate >= 85) return colors.green.border;
            if (rate >= 70) return colors.blue.border;
            if (rate >= 50) return colors.yellow.border;
            return colors.red.border;
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
                    borderColor: borderColorArray,
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBorderWidth: 3
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
                        },
                        title: {
                            display: true,
                            text: 'Persentase Okupansi',
                            font: {
                                size: 13,
                                weight: 'bold'
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
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        bodySpacing: 8,
                        padding: 16,
                        boxPadding: 8,
                        usePointStyle: true,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                return ferryNames[index];
                            },
                            afterTitle: function(context) {
                                const index = context[0].dataIndex;
                                return 'Rute: ' + routeInfo[index];
                            },
                            label: function(context) {
                                const index = context.dataIndex;
                                return [
                                    'Okupansi: ' + context.raw.toFixed(1) + '%',
                                    'Kapasitas Total: ' + totalCapacities[index],
                                    'Penumpang Terangkut: ' + totalPassengers[index]
                                ];
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
