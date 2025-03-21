<!-- Fixed Monthly Report Template -->
@extends('admin.layouts.app')

@section('content')
    <div class="bg-white shadow-xl rounded-2xl p-6 sm:p-8 transition-all duration-300">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 pb-6 border-b border-gray-100">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center group">
                    <span
                        class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-4 transition-all duration-300 group-hover:bg-indigo-200">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </span>
                    Laporan Bulanan
                </h1>
                <p class="text-gray-500 mt-2 ml-1">Ringkasan aktivitas dan pendapatan bulanan</p>
            </div>
            <a href="{{ route('admin.reports.export.monthly') }}?year={{ $year }}&month={{ $month }}"
                class="flex items-center justify-center px-5 py-3 bg-emerald-600 text-white rounded-xl transition-all duration-300 hover:bg-emerald-700 hover:shadow-md focus:ring-4 focus:ring-emerald-300 font-medium text-sm">
                <i class="fas fa-file-excel mr-2.5"></i> Export Excel
            </a>
        </div>

        <!-- Filter Section -->
        <div class="mb-10 bg-gray-50 rounded-xl p-6 shadow-sm border border-gray-100">
            <form action="{{ route('admin.reports.monthly') }}" method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="month" class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-indigo-600">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <select id="month" name="month"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3.5 transition-all duration-200 hover:border-indigo-300 shadow-sm appearance-none">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="year" class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-indigo-600">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <select id="year" name="year"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3.5 transition-all duration-200 hover:border-indigo-300 shadow-sm appearance-none">
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-xl text-sm px-6 py-3.5 flex items-center justify-center transition-all duration-300 hover:shadow-md">
                            <i class="fas fa-filter mr-2.5"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div
                class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100 transition-all duration-300 hover:shadow-lg hover:translate-y-[-2px] group">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 p-3.5 rounded-xl mr-4 group-hover:bg-blue-200 transition-all duration-300">
                        <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-blue-800">Total Pemesanan</h3>
                </div>
                <p class="text-3xl font-bold text-blue-800">{{ $bookingsCount }}</p>
                <div class="mt-2 text-sm text-blue-600">
                    <i class="fas fa-chart-line mr-1.5"></i> Periode:
                    {{ \Carbon\Carbon::create(null, $month, 1)->format('F') }} {{ $year }}
                </div>
            </div>
            <div
                class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-xl border border-green-100 transition-all duration-300 hover:shadow-lg hover:translate-y-[-2px] group">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 p-3.5 rounded-xl mr-4 group-hover:bg-green-200 transition-all duration-300">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-green-800">Total Pendapatan</h3>
                </div>
                <p class="text-3xl font-bold text-green-800">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <div class="mt-2 text-sm text-green-600">
                    <i class="fas fa-chart-line mr-1.5"></i> Pendapatan bulan ini
                </div>
            </div>
            <div
                class="bg-gradient-to-br from-purple-50 to-violet-50 p-6 rounded-xl border border-purple-100 transition-all duration-300 hover:shadow-lg hover:translate-y-[-2px] group">
                <div class="flex items-center mb-4">
                    <div class="bg-purple-100 p-3.5 rounded-xl mr-4 group-hover:bg-purple-200 transition-all duration-300">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-purple-800">Total Penumpang</h3>
                </div>
                <p class="text-3xl font-bold text-purple-800">{{ $passengerCount }}</p>
                <div class="mt-2 text-sm text-purple-600">
                    <i class="fas fa-chart-line mr-1.5"></i>
                    @if ($bookingsCount > 0)
                        {{ round($passengerCount / $bookingsCount, 1) }} per pemesanan
                    @else
                        0 per pemesanan
                    @endif
                </div>
            </div>
        </div>

        <!-- Daily Revenue Chart -->
        <div class="mb-10">
            <h2 class="text-xl font-semibold mb-5 flex items-center">
                <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg mr-3">
                    <i class="fas fa-chart-line"></i>
                </span>
                Pendapatan Harian
            </h2>
            <div
                class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                <canvas id="dailyRevenueChart" height="110"></canvas>
            </div>
        </div>

        <!-- Route Performance -->
        <div>
            <h2 class="text-xl font-semibold mb-5 flex items-center">
                <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg mr-3">
                    <i class="fas fa-route"></i>
                </span>
                Performa Rute
            </h2>

            <!-- Desktop view - Table -->
            <div
                class="hidden md:block overflow-hidden rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute
                            </th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah Pemesanan</th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah Penumpang</th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pendapatan</th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rata-rata per Booking</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($routeStats as $route)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="py-4 px-5 text-sm font-medium text-gray-900">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3 text-red-600">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <span>{{ $route->origin }}</span>
                                        <span class="mx-2 text-gray-400"><i class="fas fa-long-arrow-alt-right"></i></span>
                                        <span>{{ $route->destination }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <div
                                            class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-blue-600">
                                            <i class="fas fa-ticket-alt text-xs"></i>
                                        </div>
                                        {{ $route->booking_count }}
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <div
                                            class="w-7 h-7 bg-purple-100 rounded-full flex items-center justify-center mr-3 text-purple-600">
                                            <i class="fas fa-users text-xs"></i>
                                        </div>
                                        {{ $route->passenger_count }}
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-sm font-medium text-gray-900">
                                    <div class="flex items-center">
                                        <div
                                            class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center mr-3 text-green-600">
                                            <i class="fas fa-money-bill-wave text-xs"></i>
                                        </div>
                                        Rp {{ number_format($route->revenue, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <div
                                            class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center mr-3 text-indigo-600">
                                            <i class="fas fa-calculator text-xs"></i>
                                        </div>
                                        Rp
                                        {{ number_format($route->booking_count > 0 ? $route->revenue / $route->booking_count : 0, 0, ',', '.') }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 px-5 text-center text-gray-500 bg-gray-50 italic">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-3">
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
                @forelse($routeStats as $route)
                    <div
                        class="bg-white border rounded-xl shadow-sm p-5 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
                        <div class="mb-4">
                            <span class="font-medium text-gray-800 text-lg flex items-center">
                                <div
                                    class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3 text-red-600">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <span>{{ $route->origin }}</span>
                                <span class="mx-2 text-gray-400"><i class="fas fa-long-arrow-alt-right"></i></span>
                                <span>{{ $route->destination }}</span>
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-sm mb-2">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-500 mb-1 text-xs font-medium">Jumlah Pemesanan</p>
                                <p class="font-medium text-gray-900 flex items-center">
                                    <i class="fas fa-ticket-alt text-blue-500 mr-1.5"></i>
                                    {{ $route->booking_count }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-500 mb-1 text-xs font-medium">Jumlah Penumpang</p>
                                <p class="font-medium text-gray-900 flex items-center">
                                    <i class="fas fa-users text-purple-500 mr-1.5"></i>
                                    {{ $route->passenger_count }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-500 mb-1 text-xs font-medium">Pendapatan</p>
                                <p class="font-medium text-gray-900 flex items-center">
                                    <i class="fas fa-money-bill-wave text-green-500 mr-1.5"></i>
                                    Rp {{ number_format($route->revenue, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-500 mb-1 text-xs font-medium">Rata-rata per Booking</p>
                                <p class="font-medium text-gray-900 flex items-center">
                                    <i class="fas fa-calculator text-indigo-500 mr-1.5"></i>
                                    Rp
                                    {{ number_format($route->booking_count > 0 ? $route->revenue / $route->booking_count : 0, 0, ',', '.') }}
                                </p>
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
    </div>

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Define common chart colors
                const colors = {
                    indigo: {
                        primary: 'rgba(79, 70, 229, 1)',
                        light: 'rgba(79, 70, 229, 0.5)',
                        veryLight: 'rgba(79, 70, 229, 0.1)'
                    },
                    green: {
                        primary: 'rgba(16, 185, 129, 1)',
                        light: 'rgba(16, 185, 129, 0.5)',
                        veryLight: 'rgba(16, 185, 129, 0.1)'
                    },
                    blue: {
                        primary: 'rgba(59, 130, 246, 1)',
                        light: 'rgba(59, 130, 246, 0.5)',
                        veryLight: 'rgba(59, 130, 246, 0.1)'
                    },
                    purple: {
                        primary: 'rgba(124, 58, 237, 1)',
                        light: 'rgba(124, 58, 237, 0.5)',
                        veryLight: 'rgba(124, 58, 237, 0.1)'
                    }
                };

                const ctx = document.getElementById('dailyRevenueChart').getContext('2d');

                const dailyData = @json($dailyRevenue);
                const labels = dailyData.map(item => item.date);
                const values = dailyData.map(item => item.total);

                // Create gradient for area under line
                const gradient = ctx.createLinearGradient(0, 0, 0, 350);
                gradient.addColorStop(0, 'rgba(79, 70, 229, 0.5)');
                gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Pendapatan Harian (Rp)',
                            data: values,
                            backgroundColor: gradient,
                            borderColor: colors.indigo.primary,
                            borderWidth: 3,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: colors.indigo.primary,
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointHoverBackgroundColor: colors.indigo.primary,
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.15)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000000) {
                                            return 'Rp ' + (value / 1000000).toFixed(1) + ' jt';
                                        } else {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    },
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.15)'
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                }
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
                                displayColors: false,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
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
