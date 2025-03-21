@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-xl p-5 sm:p-7 transition-all duration-300 hover:shadow-xl">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-0 text-gray-800 flex items-center">
            <i class="fas fa-calendar-check text-indigo-600 mr-3"></i>
            Manajemen Pemesanan
        </h1>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg shadow-sm animate__animated animate__fadeIn" role="alert">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3 text-lg"></i>
            <p>{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <div class="mb-8 bg-gray-50 rounded-xl p-5 shadow-sm">
        <form action="{{ route('admin.bookings.index') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-1">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="relative">
                        <select id="status" name="status" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-all duration-200 hover:border-indigo-300">
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
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300">
                    </div>
                </div>
                <div class="lg:col-span-1">
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Hingga Tanggal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300">
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="No. Booking / Nama Penumpang" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-3 transition-all duration-200 hover:border-indigo-300">
                    </div>
                </div>
                <div class="lg:col-span-1 flex items-end space-x-2">
                    <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-3 flex items-center justify-center flex-grow transition-all duration-300">
                        <i class="fas fa-filter mr-1.5"></i> Filter
                    </button>
                    <a href="{{ route('admin.bookings.index') }}" class="text-gray-600 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-4 py-3 flex items-center justify-center transition-all duration-300">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Desktop view - Table -->
    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Booking</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jml.</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl. Pemesanan</th>
                    <th class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($bookings as $booking)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="py-3.5 px-4 text-sm font-medium text-indigo-600">{{ $booking->booking_number }}</td>
                    <td class="py-3.5 px-4 text-sm text-gray-900">{{ $booking->user->name }}</td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-red-500 mr-1.5"></i>
                            {{ $booking->schedule->route->origin }}
                            <i class="fas fa-long-arrow-alt-right mx-1.5 text-gray-400"></i>
                            {{ $booking->schedule->route->destination }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="far fa-calendar-alt text-blue-500 mr-1.5"></i>
                            {{ $booking->travel_date->format('d/m/Y') }}
                            <i class="far fa-clock text-green-500 ml-2 mr-1"></i>
                            {{ $booking->schedule->departure_time }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-users text-indigo-500 mr-1.5"></i>
                            {{ $booking->passenger_count }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm font-medium text-gray-900">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                    <td class="py-3.5 px-4 text-sm">
                        @if($booking->status == 'pending')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">Menunggu</span>
                        @elseif($booking->status == 'confirmed')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">Terkonfirmasi</span>
                        @elseif($booking->status == 'completed')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">Selesai</span>
                        @elseif($booking->status == 'cancelled')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">Dibatalkan</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-sm text-gray-600">{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3.5 px-4 text-sm">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded-lg transition-colors duration-200" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.bookings.tickets', $booking->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-100 hover:bg-indigo-200 p-2 rounded-lg transition-colors duration-200" title="Lihat Tiket">
                                <i class="fas fa-ticket-alt"></i>
                            </a>
                            @if($booking->status == 'pending')
                            <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 p-2 rounded-lg transition-colors duration-200" title="Konfirmasi Pembayaran">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded-lg transition-colors duration-200" onclick="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?')" title="Batalkan Pemesanan">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </form>
                            @elseif($booking->status == 'confirmed')
                            <form action="{{ route('admin.bookings.complete', $booking->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 p-2 rounded-lg transition-colors duration-200" title="Selesaikan Pemesanan">
                                    <i class="fas fa-check-double"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-8 px-4 text-center text-gray-500 bg-gray-50 italic">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                            <p>Tidak ada data pemesanan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile view - Cards -->
    <div class="md:hidden space-y-5">
        @forelse($bookings as $booking)
        <div class="bg-white border rounded-xl shadow-sm p-5 transition-all duration-300 hover:shadow-md">
            <div class="flex justify-between items-center mb-4">
                <span class="font-medium text-indigo-600 text-lg">{{ $booking->booking_number }}</span>
                @if($booking->status == 'pending')
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">Menunggu</span>
                @elseif($booking->status == 'confirmed')
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">Terkonfirmasi</span>
                @elseif($booking->status == 'completed')
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">Selesai</span>
                @elseif($booking->status == 'cancelled')
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">Dibatalkan</span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div>
                    <p class="text-gray-500 mb-1">Pengguna</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user text-blue-500 mr-1.5"></i>
                        {{ $booking->user->name }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Tanggal</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="far fa-calendar-alt text-green-500 mr-1.5"></i>
                        {{ $booking->travel_date->format('d/m/Y') }}
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-500 mb-1">Rute</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-route text-purple-500 mr-1.5"></i>
                        {{ $booking->schedule->route->origin }}
                        <i class="fas fa-long-arrow-alt-right mx-1.5 text-gray-400"></i>
                        {{ $booking->schedule->route->destination }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Jadwal</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="far fa-clock text-indigo-500 mr-1.5"></i>
                        {{ $booking->schedule->departure_time }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Penumpang</p>
                    <p class="font-medium text-gray-900 flex items-center">
                        <i class="fas fa-users text-orange-500 mr-1.5"></i>
                        {{ $booking->passenger_count }} orang
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-500 mb-1">Total</p>
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
                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="p-2 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded-lg transition-colors duration-200" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.bookings.tickets', $booking->id) }}" class="p-2 bg-indigo-100 text-indigo-600 hover:bg-indigo-200 rounded-lg transition-colors duration-200" title="Lihat Tiket">
                        <i class="fas fa-ticket-alt"></i>
                    </a>
                    @if($booking->status == 'pending')
                    <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors duration-200" title="Konfirmasi Pembayaran">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors duration-200" onclick="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?')" title="Batalkan Pemesanan">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </form>
                    @elseif($booking->status == 'confirmed')
                    <form action="{{ route('admin.bookings.complete', $booking->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors duration-200" title="Selesaikan Pemesanan">
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
                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                <p>Tidak ada data pemesanan</p>
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
