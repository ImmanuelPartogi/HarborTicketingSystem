@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Laporan Harian</h1>
        <a href="{{ route('admin.reports.export.daily') }}?date={{ request('date', date('Y-m-d')) }}" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </a>
    </div>

    <div class="mb-6">
        <form action="{{ route('admin.reports.daily') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
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
            <p class="text-3xl font-bold mt-2">{{ $totalBookings }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h3 class="text-lg font-semibold text-green-700">Total Pendapatan</h3>
            <p class="text-3xl font-bold mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <h3 class="text-lg font-semibold text-purple-700">Total Penumpang</h3>
            <p class="text-3xl font-bold mt-2">{{ $totalPassengers }}</p>
            <div class="text-sm text-purple-700 mt-1">
                {{ $totalVehicles }} kendaraan
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Aktivitas Pemesanan per Jam</h2>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <canvas id="hourlyBookingsChart" height="100"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
            <h2 class="text-xl font-semibold mb-4">Detail Pemesanan per Jam</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jam</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Pemesanan</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Penumpang</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hourlyStats as $hour => $stats)
                        <tr>
                            <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $hour }}:00 - {{ $hour }}:59</td>
                            <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $stats['bookings'] }}</td>
                            <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $stats['passengers'] }}</td>
                            <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data untuk ditampilkan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-4">Metode Pembayaran</h2>
            <div class="bg-white p-4 rounded-lg border border-gray-200 h-64">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Metode</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jumlah</th>
                            <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentMethods as $method)
                        <tr>
                            <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $method->payment_method }}</td>
                            <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $method->count }}</td>
                            <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($method->total, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data untuk ditampilkan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-xl font-semibold mb-4">Performa Rute</h2>
        <div class="bg-white p-4 rounded-lg border border-gray-200 mb-4">
            <canvas id="routePerformanceChart" height="80"></canvas>
        </div>
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
                    @forelse($routeStats as $route => $stats)
                    <tr>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $stats['bookings'] }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $stats['passengers'] }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                            Rp {{ number_format($stats['bookings'] > 0 ? $stats['revenue'] / $stats['bookings'] : 0, 0, ',', '.') }}
                        </td>
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
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Pendapatan (Rp)',
                        data: hourlyRevenue,
                        type: 'line',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Jumlah Pemesanan'
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
                            text: 'Pendapatan (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
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
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)'
        ];

        @foreach($paymentMethods as $method)
            paymentLabels.push('{{ $method->payment_method }}');
            paymentCounts.push({{ $method->count }});
        @endforeach

        new Chart(paymentCtx, {
            type: 'pie',
            data: {
                labels: paymentLabels,
                datasets: [{
                    data: paymentCounts,
                    backgroundColor: paymentColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
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
                        backgroundColor: 'rgba(124, 58, 237, 0.5)',
                        borderColor: 'rgba(124, 58, 237, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pendapatan (Rp)',
                        data: routeRevenue,
                        backgroundColor: 'rgba(16, 185, 129, 0.5)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        hidden: true
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endsection
@endsection
