@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen Pemesanan</h1>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="mb-6">
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Terkonfirmasi</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Hingga</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="No. Booking / Nama Penumpang" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                    Filter
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="text-gray-500 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 ml-2">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">No. Booking</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Pengguna</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rute</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jadwal</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jml. Penumpang</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Total</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Tgl. Pemesanan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm font-medium">{{ $booking->booking_number }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->user->name }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->schedule->route->origin }} - {{ $booking->schedule->route->destination }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->travel_date->format('d/m/Y') }} {{ $booking->schedule->departure_time }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->passenger_count }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        @if($booking->status == 'pending')
                        <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Menunggu Pembayaran</span>
                        @elseif($booking->status == 'confirmed')
                        <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Terkonfirmasi</span>
                        @elseif($booking->status == 'completed')
                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Selesai</span>
                        @elseif($booking->status == 'cancelled')
                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Dibatalkan</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-500 hover:text-blue-700 mr-2">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.bookings.tickets', $booking->id) }}" class="text-indigo-500 hover:text-indigo-700 mr-2" title="Lihat Tiket">
                            <i class="fas fa-ticket-alt"></i>
                        </a>
                        @if($booking->status == 'pending')
                        <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-500 hover:text-green-700 mr-2" title="Konfirmasi Pembayaran">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?')" title="Batalkan Pemesanan">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </form>
                        @elseif($booking->status == 'confirmed')
                        <form action="{{ route('admin.bookings.complete', $booking->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-500 hover:text-green-700" title="Selesaikan Pemesanan">
                                <i class="fas fa-check-double"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data pemesanan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
