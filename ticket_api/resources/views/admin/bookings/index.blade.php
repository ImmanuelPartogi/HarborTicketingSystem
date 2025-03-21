@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-xl p-5 sm:p-7 transition-all duration-300 hover:shadow-xl">
    <!-- Header Section with Gradient Background -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 -m-5 sm:-m-7 mb-6 p-5 sm:p-7 rounded-t-xl border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
            <h1 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-0 text-gray-800 flex items-center">
                <span class="bg-indigo-600 text-white p-2 rounded-lg shadow-md mr-3">
                    <i class="fas fa-calendar-check"></i>
                </span>
                Manajemen Pemesanan
            </h1>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full font-medium">
                    {{ $bookings->total() }} Total Pemesanan
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm animate__animated animate__fadeIn" role="alert">
        <div class="flex items-center">
            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-check text-green-500"></i>
            </div>
            <div>
                <p class="font-medium">Berhasil!</p>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Section - Enhanced UI -->
    <div class="mb-8 bg-gray-50 rounded-xl p-5 shadow-sm border border-gray-100">
        <form action="{{ route('admin.bookings.index') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-5">
                <div class="lg:col-span-1">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status Pemesanan</label>
                    <div class="relative">
                        <select id="status" name="status" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 pr-10 transition-all duration-200 hover:border-indigo-300 shadow-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Terkonfirmasi</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1">
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-500">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300 shadow-sm">
                    </div>
                </div>
                <div class="lg:col-span-1">
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Hingga Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-500">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300 shadow-sm">
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari Pemesanan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-500">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="No. Booking / Nama Penumpang" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300 shadow-sm">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <i class="fas fa-keyboard text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1 flex items-end space-x-2">
                    <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-3 flex items-center justify-center flex-grow transition-all duration-300 shadow-sm">
                        <i class="fas fa-filter mr-1.5"></i> Filter
                    </button>
                    <a href="{{ route('admin.bookings.index') }}" class="text-gray-600 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-4 py-3 flex items-center justify-center transition-all duration-300 shadow-sm" title="Reset Filter">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Desktop view - Table with Enhanced Design -->
    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">No. Booking</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Pengguna</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Rute</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Jadwal</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Jml.</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Total</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Status</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Tgl. Pemesanan</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($bookings as $booking)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="py-3.5 px-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center mr-2 shadow-sm">
                                <i class="fas fa-ticket-alt text-indigo-600"></i>
                            </div>
                            <span class="font-medium text-indigo-600">{{ $booking->booking_number }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center mr-2 text-gray-500">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="text-gray-900">{{ $booking->user->name }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <div class="bg-red-50 text-red-600 p-1 rounded-md mr-1.5">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            {{ $booking->schedule->route->origin }}
                            <i class="fas fa-long-arrow-alt-right mx-1.5 text-gray-400"></i>
                            {{ $booking->schedule->route->destination }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex flex-col">
                            <div class="flex items-center">
                                <div class="bg-blue-50 text-blue-600 p-1 rounded-md mr-1.5">
                                    <i class="far fa-calendar-alt"></i>
                                </div>
                                {{ $booking->travel_date->format('d/m/Y') }}
                            </div>
                            <div class="flex items-center mt-1">
                                <div class="bg-green-50 text-green-600 p-1 rounded-md mr-1.5">
                                    <i class="far fa-clock"></i>
                                </div>
                                {{ $booking->schedule->departure_time }}
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center bg-indigo-50 px-2 py-1 rounded-lg w-fit">
                            <i class="fas fa-users text-indigo-600 mr-1.5"></i>
                            <span class="font-medium">{{ $booking->passenger_count }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm">
                        <div class="bg-green-50 text-green-700 font-medium px-3 py-1 rounded-lg w-fit">
                            Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm">
                        @if($booking->status == 'pending')
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 flex items-center w-fit shadow-sm">
                            <i class="fas fa-clock mr-1.5"></i> Menunggu
                        </span>
                        @elseif($booking->status == 'confirmed')
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200 flex items-center w-fit shadow-sm">
                            <i class="fas fa-check mr-1.5"></i> Terkonfirmasi
                        </span>
                        @elseif($booking->status == 'completed')
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200 flex items-center w-fit shadow-sm">
                            <i class="fas fa-check-double mr-1.5"></i> Selesai
                        </span>
                        @elseif($booking->status == 'cancelled')
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200 flex items-center w-fit shadow-sm">
                            <i class="fas fa-times mr-1.5"></i> Dibatalkan
                        </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex flex-col">
                            <span>{{ $booking->created_at->format('d/m/Y') }}</span>
                            <span class="text-xs text-gray-500">{{ $booking->created_at->format('H:i') }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded-lg transition-colors duration-200 shadow-sm" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.bookings.tickets', $booking->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-100 hover:bg-indigo-200 p-2 rounded-lg transition-colors duration-200 shadow-sm" title="Lihat Tiket">
                                <i class="fas fa-ticket-alt"></i>
                            </a>
                            @if($booking->status == 'pending')
                            <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 p-2 rounded-lg transition-colors duration-200 shadow-sm" title="Konfirmasi Pembayaran">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded-lg transition-colors duration-200 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?')" title="Batalkan Pemesanan">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </form>
                            @elseif($booking->status == 'confirmed')
                            <form action="{{ route('admin.bookings.complete', $booking->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 p-2 rounded-lg transition-colors duration-200 shadow-sm" title="Selesaikan Pemesanan">
                                    <i class="fas fa-check-double"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-10 px-4 text-center text-gray-500 bg-gray-50 italic">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-inbox text-4xl text-gray-300"></i>
                            </div>
                            <p class="text-gray-600 font-medium">Tidak ada data pemesanan</p>
                            <p class="text-gray-400 text-sm mt-1">Data akan muncul ketika ada pemesanan baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile view - Cards with Enhanced Styling -->
    <div class="md:hidden space-y-5">
        @forelse($bookings as $booking)
        <div class="bg-white border rounded-xl shadow-sm p-5 transition-all duration-300 hover:shadow-md">
            <div class="flex justify-between items-center mb-4">
                <span class="font-medium text-indigo-600 flex items-center">
                    <span class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center mr-2 shadow-sm">
                        <i class="fas fa-ticket-alt text-indigo-600 text-xs"></i>
                    </span>
                    {{ $booking->booking_number }}
                </span>
                @if($booking->status == 'pending')
                <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 flex items-center shadow-sm">
                    <i class="fas fa-clock mr-1.5"></i> Menunggu
                </span>
                @elseif($booking->status == 'confirmed')
                <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200 flex items-center shadow-sm">
                    <i class="fas fa-check mr-1.5"></i> Terkonfirmasi
                </span>
                @elseif($booking->status == 'completed')
                <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200 flex items-center shadow-sm">
                    <i class="fas fa-check-double mr-1.5"></i> Selesai
                </span>
                @elseif($booking->status == 'cancelled')
                <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200 flex items-center shadow-sm">
                    <i class="fas fa-times mr-1.5"></i> Dibatalkan
                </span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Pengguna</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user text-blue-500 mr-1.5"></i>
                        {{ $booking->user->name }}
                    </p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Tanggal</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="far fa-calendar-alt text-green-500 mr-1.5"></i>
                        {{ $booking->travel_date->format('d/m/Y') }}
                    </p>
                </div>
                <div class="col-span-2 bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Rute</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-route text-purple-500 mr-1.5"></i>
                        {{ $booking->schedule->route->origin }}
                        <i class="fas fa-long-arrow-alt-right mx-1.5 text-gray-400"></i>
                        {{ $booking->schedule->route->destination }}
                    </p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Jadwal</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="far fa-clock text-indigo-500 mr-1.5"></i>
                        {{ $booking->schedule->departure_time }}
                    </p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Penumpang</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-users text-orange-500 mr-1.5"></i>
                        {{ $booking->passenger_count }} orang
                    </p>
                </div>
                <div class="col-span-2 bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Total</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-money-bill-wave text-green-500 mr-1.5"></i>
                        Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap justify-between items-center pt-4 border-t border-gray-200">
                <div class="text-xs text-gray-500 flex items-center mb-2 sm:mb-0">
                    <i class="far fa-clock mr-1.5"></i>
                    {{ $booking->created_at->format('d/m/Y H:i') }}
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="p-2 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded-lg transition-colors duration-200 shadow-sm" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.bookings.tickets', $booking->id) }}" class="p-2 bg-indigo-100 text-indigo-600 hover:bg-indigo-200 rounded-lg transition-colors duration-200 shadow-sm" title="Lihat Tiket">
                        <i class="fas fa-ticket-alt"></i>
                    </a>
                    @if($booking->status == 'pending')
                    <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors duration-200 shadow-sm" title="Konfirmasi Pembayaran">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors duration-200 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?')" title="Batalkan Pemesanan">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </form>
                    @elseif($booking->status == 'confirmed')
                    <form action="{{ route('admin.bookings.complete', $booking->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors duration-200 shadow-sm" title="Selesaikan Pemesanan">
                            <i class="fas fa-check-double"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border rounded-xl p-8 text-center text-gray-500 shadow-sm">
            <div class="flex flex-col items-center justify-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-inbox text-4xl text-gray-300"></i>
                </div>
                <p class="text-gray-600 font-medium">Tidak ada data pemesanan</p>
                <p class="text-gray-400 text-sm mt-1">Data akan muncul ketika ada pemesanan baru</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination with Improved Styling -->
    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
