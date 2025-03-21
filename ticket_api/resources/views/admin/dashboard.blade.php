@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('styles')
<style>
    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(66, 153, 225, 0.5);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(66, 153, 225, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(66, 153, 225, 0);
        }
    }
    .grow-animation {
        transition: all 0.2s ease;
    }
    .grow-animation:hover {
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
    <div class="mt-4">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Users -->
            <div class="bg-gradient-to-br from-white to-blue-50 overflow-hidden shadow-lg rounded-xl border border-blue-100 stats-card">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-3 shadow-md">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Users
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-3xl font-bold text-gray-900">
                                        {{ $totalUsers }}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                        <i class="fas fa-arrow-up"></i>
                                        <span class="sr-only">Increased by</span>
                                        12%
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 px-4 py-4 sm:px-6">
                    <div class="text-sm">
                        <a href="{{ route('admin.users.index') }}" class="font-medium text-blue-600 hover:text-blue-500 inline-flex items-center">
                            View all users
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total Ferries -->
            <div class="bg-gradient-to-br from-white to-green-50 overflow-hidden shadow-lg rounded-xl border border-green-100 stats-card">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-3 shadow-md">
                            <i class="fas fa-ship text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Ferries
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-3xl font-bold text-gray-900">
                                        {{ $totalFerries }}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                        <i class="fas fa-arrow-up"></i>
                                        <span class="sr-only">Increased by</span>
                                        5%
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 px-4 py-4 sm:px-6">
                    <div class="text-sm">
                        <a href="{{ route('admin.ferries.index') }}" class="font-medium text-green-600 hover:text-green-500 inline-flex items-center">
                            View all ferries
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total Routes -->
            <div class="bg-gradient-to-br from-white to-yellow-50 overflow-hidden shadow-lg rounded-xl border border-yellow-100 stats-card">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-3 shadow-md">
                            <i class="fas fa-route text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Routes
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-3xl font-bold text-gray-900">
                                        {{ $totalRoutes }}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                        <i class="fas fa-arrow-up"></i>
                                        <span class="sr-only">Increased by</span>
                                        8%
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 px-4 py-4 sm:px-6">
                    <div class="text-sm">
                        <a href="{{ route('admin.routes.index') }}" class="font-medium text-yellow-600 hover:text-yellow-500 inline-flex items-center">
                            View all routes
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total Schedules -->
            <div class="bg-gradient-to-br from-white to-purple-50 overflow-hidden shadow-lg rounded-xl border border-purple-100 stats-card">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-3 shadow-md">
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Schedules
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-3xl font-bold text-gray-900">
                                        {{ $totalSchedules }}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                        <i class="fas fa-arrow-up"></i>
                                        <span class="sr-only">Increased by</span>
                                        15%
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-50 px-4 py-4 sm:px-6">
                    <div class="text-sm">
                        <a href="{{ route('admin.schedules.index') }}" class="font-medium text-purple-600 hover:text-purple-500 inline-flex items-center">
                            View all schedules
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking and Revenue Stats in Tabs -->
        <div class="mt-8">
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs">
                        <button class="booking-tab w-1/2 py-4 px-1 text-center border-b-2 border-blue-500 font-medium text-sm text-blue-600 bg-blue-50" aria-current="page">
                            <i class="fas fa-ticket-alt mr-1"></i> Booking Statistics
                        </button>
                        <button class="revenue-tab w-1/2 py-4 px-1 text-center border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-money-bill-wave mr-1"></i> Revenue Statistics
                        </button>
                    </nav>
                </div>

                <!-- Booking Stats Panel -->
                <div id="booking-panel" class="p-4">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <!-- Today Bookings -->
                        <div class="bg-gradient-to-br from-white to-blue-50 overflow-hidden shadow-md rounded-lg p-4 border border-blue-100 grow-animation">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-calendar-day text-blue-500"></i>
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Today's Bookings
                                        </dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <div class="text-2xl font-bold text-gray-900">
                                                {{ $todayBookings }}
                                            </div>
                                            <div class="ml-2 flex items-baseline text-xs font-semibold text-green-600">
                                                <i class="fas fa-arrow-up"></i>
                                                <span class="sr-only">Increased by</span>
                                                7%
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Bookings -->
                        <div class="bg-gradient-to-br from-white to-blue-50 overflow-hidden shadow-md rounded-lg p-4 border border-blue-100 grow-animation">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-calendar-week text-blue-500"></i>
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            This Week's Bookings
                                        </dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <div class="text-2xl font-bold text-gray-900">
                                                {{ $weekBookings }}
                                            </div>
                                            <div class="ml-2 flex items-baseline text-xs font-semibold text-green-600">
                                                <i class="fas fa-arrow-up"></i>
                                                <span class="sr-only">Increased by</span>
                                                12%
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Bookings -->
                        <div class="bg-gradient-to-br from-white to-blue-50 overflow-hidden shadow-md rounded-lg p-4 border border-blue-100 grow-animation">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-blue-500"></i>
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            This Month's Bookings
                                        </dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <div class="text-2xl font-bold text-gray-900">
                                                {{ $monthBookings }}
                                            </div>
                                            <div class="ml-2 flex items-baseline text-xs font-semibold text-green-600">
                                                <i class="fas fa-arrow-up"></i>
                                                <span class="sr-only">Increased by</span>
                                                23%
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Stats Panel -->
                <div id="revenue-panel" class="hidden p-4">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <!-- Today Revenue -->
                        <div class="bg-gradient-to-br from-white to-green-50 overflow-hidden shadow-md rounded-lg p-4 border border-green-100 grow-animation">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-money-bill text-green-500"></i>
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Today's Revenue
                                        </dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <div class="text-2xl font-bold text-gray-900">
                                                Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                                            </div>
                                            <div class="ml-2 flex items-baseline text-xs font-semibold text-green-600">
                                                <i class="fas fa-arrow-up"></i>
                                                <span class="sr-only">Increased by</span>
                                                5%
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Revenue -->
                        <div class="bg-gradient-to-br from-white to-green-50 overflow-hidden shadow-md rounded-lg p-4 border border-green-100 grow-animation">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-green-500"></i>
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            This Week's Revenue
                                        </dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <div class="text-2xl font-bold text-gray-900">
                                                Rp {{ number_format($weekRevenue, 0, ',', '.') }}
                                            </div>
                                            <div class="ml-2 flex items-baseline text-xs font-semibold text-green-600">
                                                <i class="fas fa-arrow-up"></i>
                                                <span class="sr-only">Increased by</span>
                                                9%
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Revenue -->
                        <div class="bg-gradient-to-br from-white to-green-50 overflow-hidden shadow-md rounded-lg p-4 border border-green-100 grow-animation">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-hand-holding-usd text-green-500"></i>
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            This Month's Revenue
                                        </dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <div class="text-2xl font-bold text-gray-900">
                                                Rp {{ number_format($monthRevenue, 0, ',', '.') }}
                                            </div>
                                            <div class="ml-2 flex items-baseline text-xs font-semibold text-green-600">
                                                <i class="fas fa-arrow-up"></i>
                                                <span class="sr-only">Increased by</span>
                                                18%
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Occupancy Rate -->
        <div class="mt-8 bg-gradient-to-r from-blue-500 to-indigo-600 shadow-lg rounded-xl overflow-hidden text-white">
            <div class="px-6 py-5 flex flex-col sm:flex-row justify-between items-center">
                <h3 class="text-lg leading-6 font-medium mb-2 sm:mb-0">
                    <i class="fas fa-ship mr-2"></i> Today's Ferry Occupancy Rate
                </h3>
                <span class="inline-flex items-center px-4 py-1 rounded-full text-sm font-bold bg-white {{ $occupancyRate > 70 ? 'text-green-600' : ($occupancyRate > 30 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ number_format($occupancyRate, 1) }}%
                </span>
            </div>
            <div class="px-6 py-4">
                <div class="w-full bg-white bg-opacity-20 rounded-full h-3">
                    <div class="{{ $occupancyRate > 70 ? 'bg-green-400' : ($occupancyRate > 30 ? 'bg-yellow-400' : 'bg-red-400') }} h-3 rounded-full pulse-animation" style="width: {{ min($occupancyRate, 100) }}%"></div>
                </div>
                <div class="mt-2 text-sm text-white text-opacity-90">
                    Average passenger occupancy across all ferries today.
                </div>
            </div>
            <div class="bg-white bg-opacity-10 px-6 py-4">
                <div class="text-sm">
                    <a href="{{ route('admin.reports.occupancy') }}" class="font-medium text-white hover:text-blue-100 inline-flex items-center">
                        <span>View detailed occupancy report</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="mt-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">Recent Bookings</h2>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500 inline-flex items-center">
                    View all bookings
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <!-- Desktop version -->
            <div class="hidden md:block shadow-lg overflow-hidden border border-gray-200 rounded-xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Booking Code
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Route
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentBookings as $booking)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $booking->booking_code }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $booking->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $booking->schedule->route->origin }} - {{ $booking->schedule->route->destination }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $booking->booking_date->format('d M Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->schedule->departure_time->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($booking->status == 'CONFIRMED') bg-green-100 text-green-800
                                        @elseif($booking->status == 'PENDING') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status == 'CANCELLED') bg-red-100 text-red-800
                                        @elseif($booking->status == 'COMPLETED') bg-blue-100 text-blue-800
                                        @elseif($booking->status == 'RESCHEDULED') bg-purple-100 text-purple-800
                                        @endif">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No recent bookings found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile version - Cards -->
            <div class="md:hidden space-y-4">
                @forelse($recentBookings as $booking)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-4 py-4 sm:px-6 flex justify-between items-center bg-gray-50 border-b">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $booking->booking_code }}
                            </p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($booking->status == 'CONFIRMED') bg-green-100 text-green-800
                                @elseif($booking->status == 'PENDING') bg-yellow-100 text-yellow-800
                                @elseif($booking->status == 'CANCELLED') bg-red-100 text-red-800
                                @elseif($booking->status == 'COMPLETED') bg-blue-100 text-blue-800
                                @elseif($booking->status == 'RESCHEDULED') bg-purple-100 text-purple-800
                                @endif">
                                {{ $booking->status }}
                            </span>
                        </div>
                        <div class="px-4 py-4 sm:px-6">
                            <div class="grid grid-cols-2 gap-y-3">
                                <div>
                                    <p class="text-xs text-gray-500">User</p>
                                    <p class="text-sm font-medium">{{ $booking->user->name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Amount</p>
                                    <p class="text-sm font-medium">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Route</p>
                                    <p class="text-sm font-medium">{{ $booking->schedule->route->origin }} - {{ $booking->schedule->route->destination }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Date & Time</p>
                                    <p class="text-sm font-medium">{{ $booking->booking_date->format('d M Y') }}, {{ $booking->schedule->departure_time->format('H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 border-t">
                            <div class="flex justify-between items-center">
                                <div class="text-xs text-gray-500">
                                    {{ $booking->created_at->diffForHumans() }}
                                </div>
                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center text-sm">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                        No recent bookings found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookingTab = document.querySelector('.booking-tab');
        const revenueTab = document.querySelector('.revenue-tab');
        const bookingPanel = document.getElementById('booking-panel');
        const revenuePanel = document.getElementById('revenue-panel');

        bookingTab.addEventListener('click', function() {
            bookingTab.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
            bookingTab.classList.remove('border-transparent', 'text-gray-500');
            revenueTab.classList.add('border-transparent', 'text-gray-500');
            revenueTab.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');

            bookingPanel.classList.remove('hidden');
            revenuePanel.classList.add('hidden');
        });

        revenueTab.addEventListener('click', function() {
            revenueTab.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
            revenueTab.classList.remove('border-transparent', 'text-gray-500');
            bookingTab.classList.add('border-transparent', 'text-gray-500');
            bookingTab.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');

            revenuePanel.classList.remove('hidden');
            bookingPanel.classList.add('hidden');
        });
    });
</script>
@endsection
