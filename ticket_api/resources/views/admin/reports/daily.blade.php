<!-- Daily Report Template -->
@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-xl p-5 sm:p-7 transition-all duration-300 hover:shadow-xl">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-chart-line text-indigo-600 mr-3"></i>
                Laporan Harian
            </h1>
            <p class="text-gray-500 mt-1">Ringkasan aktivitas dan pendapatan harian</p>
        </div>
        <a href="{{ route('admin.reports.export.daily') }}?date={{ request('date', date('Y-m-d')) }}" class="flex items-center justify-center px-4 py-2.5 bg-green-600 text-white rounded-lg transition-all duration-300 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium text-sm">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </a>
    </div>

    <!-- Filter Section -->
    <div class="mb-8 bg-gray-50 rounded-xl p-5 shadow-sm">
        <form action="{{ route('admin.reports.daily') }}" method="GET">
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-grow max-w-xs">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300">
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full sm:w-auto text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-3 flex items-center justify-center transition-all duration-300">
                        <i class="fas fa-search mr-2"></i> Tampilkan
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
                    <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-blue-700">Total Pemesanan</h3>
            </div>
            <p class="text-3xl font-bold text-blue-800">{{ $totalBookings }}</p>
        </div>
        <div class="bg-green-50 p-5 rounded-xl border border-green-200 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center mb-3">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-green-700">Total Pendapatan</h3>
            </div>
            <p class="text-3xl font-bold text-green-800">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-purple-50 p-5 rounded-xl border border-purple-200 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center mb-3">
                <div class="bg-purple-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-purple-700">Total Penumpang</h3>
            </div>
            <p class="text-3xl font-bold text-purple-800">{{ $totalPassengers }}</p>
            <div class="text-sm text-purple-700 mt-2 flex items-center">
                <i class="fas fa-car mr-1.5"></i> {{ $totalVehicles }} kendaraan
            </div>
        </div>
    </div>

    <!-- Hourly Bookings Chart -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-chart-bar text-indigo-500 mr-2"></i>
            Aktivitas Pemesanan per Jam
        </h2>
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <canvas id="hourlyBookingsChart" height="100"></canvas>
        </div>
    </div>

    <!-- Two Column Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Hourly Stats -->
        <div>
            <h2 class="text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-clock text-indigo-500 mr-2"></i>
                Detail Pemesanan per Jam
            </h2>

            <!-- Desktop view - Table -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                            <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemesanan</th>
                            <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penumpang</th>
                            <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($hourlyStats as $hour => $stats)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-3.5 px-4 text-sm font-medium text-gray-900">
                                <div class="flex items-center">
                                    <i class="far fa-clock text-blue-500 mr-2"></i>
                                    {{ $hour }}:00 - {{ $hour }}:59
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-ticket-alt text-indigo-500 mr-2"></i>
                                    {{ $stats['bookings'] }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-users text-purple-500 mr-2"></i>
                                    {{ $stats['passengers'] }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-sm font-medium text-gray-900">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                    Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 px-4 text-center text-gray-500 bg-gray-50 italic">
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
                @forelse($hourlyStats as $hour => $stats)
                <div class="bg-white border rounded-xl shadow-sm p-4 transition-all duration-300 hover:shadow-md">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-medium text-gray-800 text-lg flex items-center">
                            <i class="far fa-clock text-blue-500 mr-2"></i>
                            {{ $hour }}:00 - {{ $hour }}:59
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Pemesanan</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-ticket-alt text-indigo-500 mr-1.5"></i>
                                {{ $stats['bookings'] }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 mb-1">Penumpang</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-users text-purple-500 mr-1.5"></i>
                                {{ $stats['passengers'] }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 mb-1">Pendapatan</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-money-bill-wave text-green-500 mr-1.5"></i>
                                Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                            </p>
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

        <!-- Payment Methods -->
        <div>
            <h2 class="text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-credit-card text-indigo-500 mr-2"></i>
                Metode Pembayaran
            </h2>
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-4 h-64">
                <canvas id="paymentMethodChart"></canvas>
            </div>

            <!-- Desktop view - Table -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                            <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($paymentMethods as $method)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-3.5 px-4 text-sm font-medium text-gray-900">
                                <div class="flex items-center">
                                    @if(strpos(strtolower($method->payment_method), 'bank') !== false)
                                        <i class="fas fa-university text-blue-500 mr-2"></i>
                                    @elseif(strpos(strtolower($method->payment_method), 'kartu') !== false || strpos(strtolower($method->payment_method), 'card') !== false)
                                        <i class="fas fa-credit-card text-indigo-500 mr-2"></i>
                                    @elseif(strpos(strtolower($method->payment_method), 'tunai') !== false || strpos(strtolower($method->payment_method), 'cash') !== false)
                                        <i class="fas fa-money-bill-alt text-green-500 mr-2"></i>
                                    @elseif(strpos(strtolower($method->payment_method), 'ewallet') !== false || strpos(strtolower($method->payment_method), 'e-wallet') !== false)
                                        <i class="fas fa-wallet text-purple-500 mr-2"></i>
                                    @else
                                        <i class="fas fa-money-check text-gray-500 mr-2"></i>
                                    @endif
                                    {{ $method->payment_method }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-sm text-gray-600">{{ $method->count }}</td>
                            <td class="py-3.5 px-4 text-sm font-medium text-gray-900">Rp {{ number_format($method->total, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 px-4 text-center text-gray-500 bg-gray-50 italic">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
                                    <p>Tidak ada data untuk ditampilkan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile view - Cards -->
            <div class="md:hidden space-y-3">
                @forelse($paymentMethods as $method)
                <div class="bg-white border rounded-xl shadow-sm p-4 transition-all duration-300 hover:shadow-md">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-medium text-gray-800 flex items-center">
                            @if(strpos(strtolower($method->payment_method), 'bank') !== false)
                                <i class="fas fa-university text-blue-500 mr-2"></i>
                            @elseif(strpos(strtolower($method->payment_method), 'kartu') !== false || strpos(strtolower($method->payment_method), 'card') !== false)
                                <i class="fas fa-credit-card text-indigo-500 mr-2"></i>
                            @elseif(strpos(strtolower($method->payment_method), 'tunai') !== false || strpos(strtolower($method->payment_method), 'cash') !== false)
                                <i class="fas fa-money-bill-alt text-green-500 mr-2"></i>
                            @elseif(strpos(strtolower($method->payment_method), 'ewallet') !== false || strpos(strtolower($method->payment_method), 'e-wallet') !== false)
                                <i class="fas fa-wallet text-purple-500 mr-2"></i>
                            @else
                                <i class="fas fa-money-check text-gray-500 mr-2"></i>
                            @endif
                            {{ $method->payment_method }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Jumlah Transaksi</p>
                            <p class="font-medium text-gray-900">{{ $method->count }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 mb-1">Total</p>
                            <p class="font-medium text-gray-900">Rp {{ number_format($method->total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white border rounded-xl p-6 text-center text-gray-500 shadow-sm">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
                        <p>Tidak ada data untuk ditampilkan</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Route Performance -->
    <div>
        <h2 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-route text-indigo-500 mr-2"></i>
            Performa Rute
        </h2>
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
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
                        <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata per Booking</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($routeStats as $route => $stats)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="py-3.5 px-4 text-sm font-medium text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                {{ $route }}
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-ticket-alt text-blue-500 mr-2"></i>
                                {{ $stats['bookings'] }}
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-users text-purple-500 mr-2"></i>
                                {{ $stats['passengers'] }}
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-sm font-medium text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-calculator text-indigo-500 mr-2"></i>
                                Rp {{ number_format($stats['bookings'] > 0 ? $stats['revenue'] / $stats['bookings'] : 0, 0, ',', '.') }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 px-4 text-center text-gray-500 bg-gray-50 italic">
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
            @forelse($routeStats as $route => $stats)
            <div class="bg-white border rounded-xl shadow-sm p-4 transition-all duration-300 hover:shadow-md">
                <div class="mb-3">
                    <span class="font-medium text-gray-800 text-lg flex items-center">
                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                        {{ $route }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm mb-2">
                    <div>
                        <p class="text-gray-500 mb-1">Jumlah Pemesanan</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-ticket-alt text-blue-500 mr-1.5"></i>
                            {{ $stats['bookings'] }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Jumlah Penumpang</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-users text-purple-500 mr-1.5"></i>
                            {{ $stats['passengers'] }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Pendapatan</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-money-bill-wave text-green-500 mr-1.5"></i>
                            Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Rata-rata per Booking</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-calculator text-indigo-500 mr-1.5"></i>
                            Rp {{ number_format($stats['bookings'] > 0 ? $stats['revenue'] / $stats['bookings'] : 0, 0, ',', '.') }}
                        </p>
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
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    bodySpacing: 6,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    cornerRadius: 8
                }
            }
        };

        // Hourly Bookings Chart
        const hourlyCtx = document.getElementById('hourlyBookingsChart').getContext('2d');
        const hourlyLabels = [];
        const hourlyBookings = [];
        const hourlyRevenue = [];

        @foreach($hourlyStats as $hour => $stats)
            hourlyLabels.push('{{ $hour }}:00');
            hourlyBookings.push({{ $stats['bookings'] }});
            hourlyRevenue.push({{ $stats['revenue'] }});
        @endforeach

        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlyLabels,
                datasets: [
                    {
                        label: 'Jumlah Pemesanan',
                        data: hourlyBookings,
                        backgroundColor: 'rgba(79, 70, 229, 0.5)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                        borderRadius: 6
                    },
                    {
                        label: 'Pendapatan (Rp)',
                        data: hourlyRevenue,
                        type: 'line',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        yAxisID: 'y1',
                        tension: 0.2,
                        pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                        pointRadius: 4
                    }
                ]
            },
            options: {
                ...defaultOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Jumlah Pemesanan',
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(156, 163, 175, 0.15)'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Pendapatan (Rp)',
                            font: {
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(156, 163, 175, 0.15)'
                        }
                    }
                },
                plugins: {
                    ...defaultOptions.plugins,
                    tooltip: {
                        ...defaultOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.raw;
                                if (label.includes('Pendapatan')) {
                                    return label + ': Rp ' + value.toLocaleString('id-ID');
                                } else {
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                }
            }
        });

        // Payment Method Chart
        const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
        const paymentLabels = [];
        const paymentCounts = [];
        const paymentColors = [
            'rgba(79, 70, 229, 0.8)',  // Indigo
            'rgba(16, 185, 129, 0.8)', // Green
            'rgba(245, 158, 11, 0.8)', // Amber
            'rgba(239, 68, 68, 0.8)',  // Red
            'rgba(124, 58, 237, 0.8)'  // Purple
        ];

        @foreach($paymentMethods as $method)
            paymentLabels.push('{{ $method->payment_method }}');
            paymentCounts.push({{ $method->count }});
        @endforeach

        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: paymentLabels,
                datasets: [{
                    data: paymentCounts,
                    backgroundColor: paymentColors,
                    borderWidth: 0,
                    borderRadius: 3,
                    hoverOffset: 5
                }]
            },
            options: {
                ...defaultOptions,
                cutout: '60%',
                plugins: {
                    ...defaultOptions.plugins,
                    tooltip: {
                        ...defaultOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Route Performance Chart
        const routeCtx = document.getElementById('routePerformanceChart').getContext('2d');
        const routeLabels = [];
        const routePassengers = [];
        const routeRevenue = [];

        @foreach($routeStats as $route => $stats)
            routeLabels.push('{{ $route }}');
            routePassengers.push({{ $stats['passengers'] }});
            routeRevenue.push({{ $stats['revenue'] }});
        @endforeach

        new Chart(routeCtx, {
            type: 'bar',
            data: {
                labels: routeLabels,
                datasets: [
                    {
                        label: 'Jumlah Penumpang',
                        data: routePassengers,
                        backgroundColor: 'rgba(124, 58, 237, 0.6)',
                        borderColor: 'rgba(124, 58, 237, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Pendapatan (Rp)',
                        data: routeRevenue,
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        hidden: true,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                ...defaultOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(156, 163, 175, 0.15)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    ...defaultOptions.plugins,
                    tooltip: {
                        ...defaultOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.raw;
                                if (label.includes('Pendapatan')) {
                                    return label + ': Rp ' + value.toLocaleString('id-ID');
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
