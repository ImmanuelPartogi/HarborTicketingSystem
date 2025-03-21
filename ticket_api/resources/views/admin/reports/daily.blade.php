<!-- Improved Daily Report Template -->
@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-xl rounded-2xl p-6 sm:p-8 transition-all duration-300">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 pb-6 border-b border-gray-100">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center group">
                <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-4 transition-all duration-300 group-hover:bg-indigo-200">
                    <i class="fas fa-chart-line text-xl"></i>
                </span>
                Laporan Harian
            </h1>
            <p class="text-gray-500 mt-2 ml-1">Ringkasan aktivitas dan pendapatan harian</p>
        </div>
        <a href="{{ route('admin.reports.export.daily') }}?date={{ request('date', date('Y-m-d')) }}" class="flex items-center justify-center px-5 py-3 bg-emerald-600 text-white rounded-xl transition-all duration-300 hover:bg-emerald-700 hover:shadow-md focus:ring-4 focus:ring-emerald-300 font-medium text-sm">
            <i class="fas fa-file-excel mr-2.5"></i> Export Excel
        </a>
    </div>

    <!-- Filter Section -->
    <div class="mb-10 bg-gray-50 rounded-xl p-6 shadow-sm border border-gray-100">
        <form action="{{ route('admin.reports.daily') }}" method="GET">
            <div class="flex flex-wrap items-end gap-5">
                <div class="flex-grow max-w-xs">
                    <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-indigo-600">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3.5 transition-all duration-200 hover:border-indigo-300 shadow-sm">
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full sm:w-auto text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-xl text-sm px-6 py-3.5 flex items-center justify-center transition-all duration-300 hover:shadow-md">
                        <i class="fas fa-search mr-2.5"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100 transition-all duration-300 hover:shadow-lg hover:translate-y-[-2px] group">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 p-3.5 rounded-xl mr-4 group-hover:bg-blue-200 transition-all duration-300">
                    <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-blue-800">Total Pemesanan</h3>
            </div>
            <p class="text-3xl font-bold text-blue-800">{{ $totalBookings }}</p>
            <div class="mt-2 text-sm text-blue-600">
                <i class="fas fa-chart-line mr-1.5"></i> {{ $totalBookings > 0 ? '+' . $totalBookings : $totalBookings }} hari ini
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-xl border border-green-100 transition-all duration-300 hover:shadow-lg hover:translate-y-[-2px] group">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 p-3.5 rounded-xl mr-4 group-hover:bg-green-200 transition-all duration-300">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-green-800">Total Pendapatan</h3>
            </div>
            <p class="text-3xl font-bold text-green-800">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <div class="mt-2 text-sm text-green-600">
                <i class="fas fa-chart-line mr-1.5"></i> Hari ini
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-violet-50 p-6 rounded-xl border border-purple-100 transition-all duration-300 hover:shadow-lg hover:translate-y-[-2px] group">
            <div class="flex items-center mb-4">
                <div class="bg-purple-100 p-3.5 rounded-xl mr-4 group-hover:bg-purple-200 transition-all duration-300">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-purple-800">Total Penumpang</h3>
            </div>
            <p class="text-3xl font-bold text-purple-800">{{ $totalPassengers }}</p>
            <div class="text-sm text-purple-600 mt-2 flex items-center">
                <i class="fas fa-car mr-1.5"></i> {{ $totalVehicles }} kendaraan
            </div>
        </div>
    </div>

    <!-- Hourly Bookings Chart -->
    <div class="mb-10">
        <h2 class="text-xl font-semibold mb-5 flex items-center">
            <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg mr-3">
                <i class="fas fa-chart-bar"></i>
            </span>
            Aktivitas Pemesanan per Jam
        </h2>
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
            <canvas id="hourlyBookingsChart" height="110"></canvas>
        </div>
    </div>

    <!-- Two Column Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
        <!-- Hourly Stats -->
        <div>
            <h2 class="text-xl font-semibold mb-5 flex items-center">
                <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg mr-3">
                    <i class="fas fa-clock"></i>
                </span>
                Detail Pemesanan per Jam
            </h2>

            <!-- Desktop view - Table -->
            <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemesanan</th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penumpang</th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($hourlyStats as $hour => $stats)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-4 px-5 text-sm font-medium text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-blue-600">
                                        <i class="far fa-clock"></i>
                                    </div>
                                    {{ $hour }}:00 - {{ $hour }}:59
                                </div>
                            </td>
                            <td class="py-4 px-5 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center mr-3 text-indigo-600">
                                        <i class="fas fa-ticket-alt text-xs"></i>
                                    </div>
                                    {{ $stats['bookings'] }}
                                </div>
                            </td>
                            <td class="py-4 px-5 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <div class="w-7 h-7 bg-purple-100 rounded-full flex items-center justify-center mr-3 text-purple-600">
                                        <i class="fas fa-users text-xs"></i>
                                    </div>
                                    {{ $stats['passengers'] }}
                                </div>
                            </td>
                            <td class="py-4 px-5 text-sm font-medium text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center mr-3 text-green-600">
                                        <i class="fas fa-money-bill-wave text-xs"></i>
                                    </div>
                                    Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-10 px-5 text-center text-gray-500 bg-gray-50 italic">
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
                @forelse($hourlyStats as $hour => $stats)
                <div class="bg-white border rounded-xl shadow-sm p-5 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-medium text-gray-800 text-lg flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-blue-600">
                                <i class="far fa-clock"></i>
                            </div>
                            {{ $hour }}:00 - {{ $hour }}:59
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 text-sm">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-500 mb-1 text-xs font-medium">Pemesanan</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-ticket-alt text-indigo-500 mr-1.5"></i>
                                {{ $stats['bookings'] }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-500 mb-1 text-xs font-medium">Penumpang</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-users text-purple-500 mr-1.5"></i>
                                {{ $stats['passengers'] }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-500 mb-1 text-xs font-medium">Pendapatan</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-money-bill-wave text-green-500 mr-1.5"></i>
                                Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
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

        <!-- Payment Methods -->
        <div>
            <h2 class="text-xl font-semibold mb-5 flex items-center">
                <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg mr-3">
                    <i class="fas fa-credit-card"></i>
                </span>
                Metode Pembayaran
            </h2>
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 mb-5 h-64">
                <canvas id="paymentMethodChart"></canvas>
            </div>

            <!-- Desktop view - Table -->
            <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($paymentMethods as $method)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-4 px-5 text-sm font-medium text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3
                                    @if(strpos(strtolower($method->payment_method), 'bank') !== false)
                                        bg-blue-100 text-blue-600
                                    @elseif(strpos(strtolower($method->payment_method), 'kartu') !== false || strpos(strtolower($method->payment_method), 'card') !== false)
                                        bg-indigo-100 text-indigo-600
                                    @elseif(strpos(strtolower($method->payment_method), 'tunai') !== false || strpos(strtolower($method->payment_method), 'cash') !== false)
                                        bg-green-100 text-green-600
                                    @elseif(strpos(strtolower($method->payment_method), 'ewallet') !== false || strpos(strtolower($method->payment_method), 'e-wallet') !== false)
                                        bg-purple-100 text-purple-600
                                    @else
                                        bg-gray-100 text-gray-600
                                    @endif">
                                    @if(strpos(strtolower($method->payment_method), 'bank') !== false)
                                        <i class="fas fa-university"></i>
                                    @elseif(strpos(strtolower($method->payment_method), 'kartu') !== false || strpos(strtolower($method->payment_method), 'card') !== false)
                                        <i class="fas fa-credit-card"></i>
                                    @elseif(strpos(strtolower($method->payment_method), 'tunai') !== false || strpos(strtolower($method->payment_method), 'cash') !== false)
                                        <i class="fas fa-money-bill-alt"></i>
                                    @elseif(strpos(strtolower($method->payment_method), 'ewallet') !== false || strpos(strtolower($method->payment_method), 'e-wallet') !== false)
                                        <i class="fas fa-wallet"></i>
                                    @else
                                        <i class="fas fa-money-check"></i>
                                    @endif
                                    </div>
                                    {{ $method->payment_method }}
                                </div>
                            </td>
                            <td class="py-4 px-5 text-sm text-gray-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $method->count }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-sm font-medium text-gray-900">Rp {{ number_format($method->total, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-10 px-5 text-center text-gray-500 bg-gray-50 italic">
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
                @forelse($paymentMethods as $method)
                <div class="bg-white border rounded-xl shadow-sm p-5 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-medium text-gray-800 flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3
                            @if(strpos(strtolower($method->payment_method), 'bank') !== false)
                                bg-blue-100 text-blue-600
                            @elseif(strpos(strtolower($method->payment_method), 'kartu') !== false || strpos(strtolower($method->payment_method), 'card') !== false)
                                bg-indigo-100 text-indigo-600
                            @elseif(strpos(strtolower($method->payment_method), 'tunai') !== false || strpos(strtolower($method->payment_method), 'cash') !== false)
                                bg-green-100 text-green-600
                            @elseif(strpos(strtolower($method->payment_method), 'ewallet') !== false || strpos(strtolower($method->payment_method), 'e-wallet') !== false)
                                bg-purple-100 text-purple-600
                            @else
                                bg-gray-100 text-gray-600
                            @endif">
                            @if(strpos(strtolower($method->payment_method), 'bank') !== false)
                                <i class="fas fa-university"></i>
                            @elseif(strpos(strtolower($method->payment_method), 'kartu') !== false || strpos(strtolower($method->payment_method), 'card') !== false)
                                <i class="fas fa-credit-card"></i>
                            @elseif(strpos(strtolower($method->payment_method), 'tunai') !== false || strpos(strtolower($method->payment_method), 'cash') !== false)
                                <i class="fas fa-money-bill-alt"></i>
                            @elseif(strpos(strtolower($method->payment_method), 'ewallet') !== false || strpos(strtolower($method->payment_method), 'e-wallet') !== false)
                                <i class="fas fa-wallet"></i>
                            @else
                                <i class="fas fa-money-check"></i>
                            @endif
                            </div>
                            {{ $method->payment_method }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $method->count }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 text-sm">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-500 mb-1 text-xs font-medium">Total Pendapatan</p>
                            <p class="font-medium text-gray-900">Rp {{ number_format($method->total, 0, ',', '.') }}</p>
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

    <!-- Route Performance -->
    <div>
        <h2 class="text-xl font-semibold mb-5 flex items-center">
            <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg mr-3">
                <i class="fas fa-route"></i>
            </span>
            Performa Rute
        </h2>
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 mb-6">
            <canvas id="routePerformanceChart" height="90"></canvas>
        </div>

        <!-- Desktop view - Table -->
        <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
            <table class="min-w-full bg-white divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                        <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Pemesanan</th>
                        <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Penumpang</th>
                        <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                        <th class="py-4 px-5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata per Booking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($routeStats as $route => $stats)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="py-4 px-5 text-sm font-medium text-gray-900">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3 text-red-600">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                {{ $route }}
                            </div>
                        </td>
                        <td class="py-4 px-5 text-sm text-gray-600">
                            <div class="flex items-center">
                                <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-blue-600">
                                    <i class="fas fa-ticket-alt text-xs"></i>
                                </div>
                                {{ $stats['bookings'] }}
                            </div>
                        </td>
                        <td class="py-4 px-5 text-sm text-gray-600">
                            <div class="flex items-center">
                                <div class="w-7 h-7 bg-purple-100 rounded-full flex items-center justify-center mr-3 text-purple-600">
                                    <i class="fas fa-users text-xs"></i>
                                </div>
                                {{ $stats['passengers'] }}
                            </div>
                        </td>
                        <td class="py-4 px-5 text-sm font-medium text-gray-900">
                            <div class="flex items-center">
                                <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center mr-3 text-green-600">
                                    <i class="fas fa-money-bill-wave text-xs"></i>
                                </div>
                                Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="py-4 px-5 text-sm text-gray-600">
                            <div class="flex items-center">
                                <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center mr-3 text-indigo-600">
                                    <i class="fas fa-calculator text-xs"></i>
                                </div>
                                Rp {{ number_format($stats['bookings'] > 0 ? $stats['revenue'] / $stats['bookings'] : 0, 0, ',', '.') }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 px-5 text-center text-gray-500 bg-gray-50 italic">
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
            @forelse($routeStats as $route => $stats)
            <div class="bg-white border rounded-xl shadow-sm p-5 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
                <div class="mb-4">
                    <span class="font-medium text-gray-800 text-lg flex items-center">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3 text-red-600">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        {{ $route }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm mb-2">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-gray-500 mb-1 text-xs font-medium">Jumlah Pemesanan</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-ticket-alt text-blue-500 mr-1.5"></i>
                            {{ $stats['bookings'] }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-gray-500 mb-1 text-xs font-medium">Jumlah Penumpang</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-users text-purple-500 mr-1.5"></i>
                            {{ $stats['passengers'] }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-gray-500 mb-1 text-xs font-medium">Pendapatan</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-money-bill-wave text-green-500 mr-1.5"></i>
                            Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-gray-500 mb-1 text-xs font-medium">Rata-rata per Booking</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-calculator text-indigo-500 mr-1.5"></i>
                            Rp {{ number_format($stats['bookings'] > 0 ? $stats['revenue'] / $stats['bookings'] : 0, 0, ',', '.') }}
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
            },
            red: {
                primary: 'rgba(239, 68, 68, 1)',
                light: 'rgba(239, 68, 68, 0.5)',
                veryLight: 'rgba(239, 68, 68, 0.1)'
            },
            amber: {
                primary: 'rgba(245, 158, 11, 1)',
                light: 'rgba(245, 158, 11, 0.5)',
                veryLight: 'rgba(245, 158, 11, 0.1)'
            }
        };

        // Common chart options
        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: true,
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
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    displayColors: true,
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1
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

        // Create gradient for bars
        const barGradient = hourlyCtx.createLinearGradient(0, 0, 0, 400);
        barGradient.addColorStop(0, colors.indigo.primary);
        barGradient.addColorStop(1, colors.blue.primary);

        // Create gradient for line area
        const lineGradient = hourlyCtx.createLinearGradient(0, 0, 0, 400);
        lineGradient.addColorStop(0, colors.green.light);
        lineGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlyLabels,
                datasets: [
                    {
                        label: 'Jumlah Pemesanan',
                        data: hourlyBookings,
                        backgroundColor: barGradient,
                        borderWidth: 0,
                        yAxisID: 'y',
                        borderRadius: 8,
                        hoverBackgroundColor: colors.indigo.primary
                    },
                    {
                        label: 'Pendapatan (Rp)',
                        data: hourlyRevenue,
                        type: 'line',
                        backgroundColor: lineGradient,
                        borderColor: colors.green.primary,
                        borderWidth: 3,
                        yAxisID: 'y1',
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: colors.green.primary,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: colors.green.primary,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3
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
                                size: 13,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(156, 163, 175, 0.15)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
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
                                size: 13,
                                weight: 'bold'
                            }
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
                            font: {
                                size: 11
                            }
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
            colors.indigo.primary,
            colors.green.primary,
            colors.amber.primary,
            colors.red.primary,
            colors.purple.primary
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
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                    hoverBorderWidth: 0,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                ...defaultOptions,
                cutout: '65%',
                radius: '90%',
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

        // Create gradients for bars
        const passengerGradient = routeCtx.createLinearGradient(0, 0, 0, 400);
        passengerGradient.addColorStop(0, colors.purple.primary);
        passengerGradient.addColorStop(1, colors.indigo.primary);

        const revenueGradient = routeCtx.createLinearGradient(0, 0, 0, 400);
        revenueGradient.addColorStop(0, colors.green.primary);
        revenueGradient.addColorStop(1, colors.blue.primary);

        new Chart(routeCtx, {
            type: 'bar',
            data: {
                labels: routeLabels,
                datasets: [
                    {
                        label: 'Jumlah Penumpang',
                        data: routePassengers,
                        backgroundColor: passengerGradient,
                        borderWidth: 0,
                        borderRadius: 8,
                        hoverBackgroundColor: colors.purple.primary
                    },
                    {
                        label: 'Pendapatan (Juta Rp)',
                        data: routeRevenue.map(val => val / 1000000), // Convert to millions
                        backgroundColor: revenueGradient,
                        borderWidth: 0,
                        hidden: true,
                        borderRadius: 8,
                        hoverBackgroundColor: colors.green.primary
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
                        },
                        ticks: {
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
                            maxRotation: 45,
                            minRotation: 45,
                            font: {
                                size: 11
                            }
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
