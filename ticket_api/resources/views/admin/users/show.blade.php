@extends('admin.layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <!-- Header Section with Refined Gradient Background -->
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 px-6 py-5 relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10">
            <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M50 0C22.3858 0 0 22.3858 0 50V150C0 177.614 22.3858 200 50 200H150C177.614 200 200 177.614 200 150V50C200 22.3858 177.614 0 150 0H50Z" fill="white"/>
            </svg>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 relative z-10">
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-user-circle mr-3 text-white/80"></i>
                <span>Detail Pengguna</span>
            </h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white transition duration-200 py-2 px-4 rounded-lg shadow-md flex items-center text-sm font-medium">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-white text-indigo-700 hover:bg-indigo-50 transition duration-200 py-2 px-4 rounded-lg shadow-md flex items-center text-sm font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- User Profile Section -->
    <div class="p-6 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- User Profile Card -->
            <div class="col-span-1">
                <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 transition hover:shadow-md">
                    <!-- User Avatar -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-6 flex justify-center">
                        <div class="h-32 w-32 rounded-full bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg border-4 border-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-center text-gray-800">{{ $user->name }}</h2>
                        <p class="text-gray-500 text-center mt-1 text-sm flex justify-center items-center">
                            <i class="fas fa-clock mr-1 text-indigo-400"></i> Terdaftar {{ $user->created_at->diffForHumans() }}
                        </p>

                        <div class="mt-6 space-y-4">
                            <div class="flex items-center p-3 bg-indigo-50 rounded-lg transition hover:bg-indigo-100">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3 text-indigo-600 shadow-sm">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Email</p>
                                    <p class="font-medium text-gray-800">{{ $user->email }}</p>
                                </div>
                            </div>

                            <div class="flex items-center p-3 bg-indigo-50 rounded-lg transition hover:bg-indigo-100">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3 text-indigo-600 shadow-sm">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Telepon</p>
                                    <p class="font-medium text-gray-800">{{ $user->phone }}</p>
                                </div>
                            </div>

                            @if($user->id_type && $user->id_number)
                            <div class="flex items-center p-3 bg-indigo-50 rounded-lg transition hover:bg-indigo-100">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3 text-indigo-600 shadow-sm">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Identitas</p>
                                    <p class="font-medium text-gray-800">{{ $user->id_type }}: {{ $user->id_number }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Status Card -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 mt-6 transition hover:shadow-md">
                    <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 class="font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-shield-alt mr-2 text-indigo-600"></i> Status Akun
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4 p-2 hover:bg-gray-50 rounded-lg transition">
                            <span class="text-sm text-gray-600 font-medium">Status Email</span>
                            @if($user->email_verified_at)
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 shadow-sm">
                                    <i class="fas fa-check-circle mr-1"></i> Terverifikasi
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 shadow-sm">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Belum Terverifikasi
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition">
                            <span class="text-sm text-gray-600 font-medium">Status Akun</span>
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 shadow-sm">
                                <i class="fas fa-user-check mr-1"></i> Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Information Cards -->
            <div class="col-span-2 space-y-6">
                <!-- Personal Information Card -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 transition hover:shadow-md">
                    <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 class="font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-user-circle mr-2 text-indigo-600"></i> Informasi Pribadi
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-3 bg-gray-50 rounded-lg transition hover:bg-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Jenis Kelamin</p>
                                <p class="font-medium text-gray-800 flex items-center mt-1">
                                    <i class="fas fa-venus-mars text-indigo-400 mr-2"></i>
                                    {{ $user->gender == 'MALE' ? 'Laki-laki' : ($user->gender == 'FEMALE' ? 'Perempuan' : '-') }}
                                </p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg transition hover:bg-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Tanggal Lahir</p>
                                <p class="font-medium text-gray-800 flex items-center mt-1">
                                    <i class="fas fa-birthday-cake text-indigo-400 mr-2"></i>
                                    {{ $user->dob ? $user->dob->format('d F Y') : '-' }}
                                </p>
                            </div>
                            <div class="col-span-1 md:col-span-2 p-3 bg-gray-50 rounded-lg transition hover:bg-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Alamat</p>
                                <p class="font-medium text-gray-800 flex items-start mt-1">
                                    <i class="fas fa-home text-indigo-400 mr-2 mt-0.5"></i>
                                    <span>{{ $user->address ?? '-' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Records Card -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 transition hover:shadow-md">
                    <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 class="font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-history mr-2 text-indigo-600"></i> Informasi Tambahan
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-3 bg-gray-50 rounded-lg transition hover:bg-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Waktu Pembuatan</p>
                                <p class="font-medium text-gray-800 flex items-center mt-1">
                                    <i class="fas fa-calendar-plus text-indigo-400 mr-2"></i>
                                    {{ $user->created_at->format('d M Y H:i') }}
                                </p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg transition hover:bg-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Terakhir Diperbarui</p>
                                <p class="font-medium text-gray-800 flex items-center mt-1">
                                    <i class="fas fa-calendar-check text-indigo-400 mr-2"></i>
                                    {{ $user->updated_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking History Section -->
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-ticket-alt mr-2 text-indigo-600"></i> Riwayat Pemesanan
                </h3>
                <div class="text-sm bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full font-medium shadow-sm">
                    Total: {{ $user->bookings->count() }} pemesanan
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 transition hover:shadow-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Booking</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penumpang</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl. Booking</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($user->bookings as $booking)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center shadow-sm mr-2">
                                            <i class="fas fa-bookmark text-indigo-400"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $booking->booking_code }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-route text-gray-400 mr-1"></i>
                                        {{ $booking->schedule->route->origin }} - {{ $booking->schedule->route->destination }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-calendar-day text-gray-400 mr-1"></i>
                                        {{ $booking->booking_date->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-users text-gray-400 mr-1"></i>
                                        {{ $booking->passenger_count }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($booking->status == 'PENDING')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 shadow-sm">
                                        <i class="fas fa-clock mr-1"></i> Menunggu Pembayaran
                                    </span>
                                    @elseif($booking->status == 'CONFIRMED')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 shadow-sm">
                                        <i class="fas fa-check mr-1"></i> Terkonfirmasi
                                    </span>
                                    @elseif($booking->status == 'COMPLETED')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 shadow-sm">
                                        <i class="fas fa-check-double mr-1"></i> Selesai
                                    </span>
                                    @elseif($booking->status == 'CANCELLED')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 shadow-sm">
                                        <i class="fas fa-times mr-1"></i> Dibatalkan
                                    </span>
                                    @elseif($booking->status == 'REFUNDED')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 shadow-sm">
                                        <i class="fas fa-undo mr-1"></i> Dikembalikan
                                    </span>
                                    @elseif($booking->status == 'RESCHEDULED')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800 shadow-sm">
                                        <i class="fas fa-calendar-alt mr-1"></i> Dijadwalkan Ulang
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $booking->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-ticket-alt text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">Belum ada riwayat pemesanan</p>
                                        <p class="text-gray-400 text-sm mt-1">Pengguna belum melakukan pemesanan apapun</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
