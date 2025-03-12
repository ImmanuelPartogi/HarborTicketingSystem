@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Pengguna</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded mr-2">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="col-span-1">
            <div class="bg-gray-100 p-4 rounded-lg">
                <div class="flex justify-center">
                    <div class="h-32 w-32 rounded-full bg-blue-500 flex items-center justify-center text-white text-3xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                <h2 class="text-xl font-bold mt-4 text-center">{{ $user->name }}</h2>
                <p class="text-gray-600 text-center mt-1">Terdaftar {{ $user->created_at->diffForHumans() }}</p>
                <div class="mt-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-envelope text-gray-500 mr-3"></i>
                        <p>{{ $user->email }}</p>
                    </div>
                    <div class="flex items-center mb-2">
                        <i class="fas fa-phone text-gray-500 mr-3"></i>
                        <p>{{ $user->phone }}</p>
                    </div>
                    @if($user->id_type && $user->id_number)
                    <div class="flex items-center">
                        <i class="fas fa-id-card text-gray-500 mr-3"></i>
                        <p>{{ $user->id_type }}: {{ $user->id_number }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-span-2">
            <div class="bg-gray-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3">Informasi Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Jenis Kelamin</p>
                        <p class="font-medium">{{ $user->gender == 'MALE' ? 'Laki-laki' : ($user->gender == 'FEMALE' ? 'Perempuan' : '-') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Lahir</p>
                        <p class="font-medium">{{ $user->dob ? $user->dob->format('d F Y') : '-' }}</p>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <p class="text-sm text-gray-500">Alamat</p>
                        <p class="font-medium">{{ $user->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-100 p-4 rounded-lg mt-4">
                <h3 class="text-lg font-semibold mb-3">Status Akun</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Status Email</p>
                        <p class="font-medium">
                            @if($user->email_verified_at)
                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Terverifikasi</span>
                            @else
                                <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i> Belum Terverifikasi</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status Akun</p>
                        <p class="font-medium">Aktif</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-100 p-4 rounded-lg mt-4">
                <h3 class="text-lg font-semibold mb-3">Informasi Tambahan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Waktu Pembuatan</p>
                        <p class="font-medium">{{ $user->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Terakhir Diperbarui</p>
                        <p class="font-medium">{{ $user->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-xl font-semibold mb-4">Riwayat Pemesanan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Kode Booking</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rute</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Tanggal Perjalanan</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jumlah Penumpang</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Total</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Tanggal Booking</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->bookings as $booking)
                    <tr>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->booking_code }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->schedule->route->origin }} - {{ $booking->schedule->route->destination }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->booking_date->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->passenger_count }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">
                            @if($booking->status == 'PENDING')
                            <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Menunggu Pembayaran</span>
                            @elseif($booking->status == 'CONFIRMED')
                            <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Terkonfirmasi</span>
                            @elseif($booking->status == 'COMPLETED')
                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Selesai</span>
                            @elseif($booking->status == 'CANCELLED')
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Dibatalkan</span>
                            @elseif($booking->status == 'REFUNDED')
                            <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">Dikembalikan</span>
                            @elseif($booking->status == 'RESCHEDULED')
                            <span class="px-2 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800">Dijadwalkan Ulang</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada riwayat pemesanan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
