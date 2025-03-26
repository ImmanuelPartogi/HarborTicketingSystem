@extends('admin.layouts.app')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(to right, #1e40af, #3b82f6);
        }
    </style>
@endsection

@section('content')
    <div class="bg-white shadow-lg rounded-lg overflow-hidden table-shadow">
        <!-- Header Section with Gradient Background -->
        <div class="page-header p-6 text-white relative">
            <div class="absolute right-0 bottom-0 opacity-30 pointer-events-none">
                <svg width="150" height="150" viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M30.5,-45.6C40.1,-42.3,49.1,-35.8,55.9,-26.5C62.8,-17.3,67.4,-5.4,64.2,4.5C61,14.3,50.1,22.2,40.7,28.4C31.3,34.6,23.5,39.2,14.9,43.3C6.2,47.4,-3.2,51,-13.3,50.1C-23.4,49.3,-34.2,44,-43.5,35.7C-52.8,27.4,-60.6,16.1,-61.5,4.5C-62.4,-7.2,-56.4,-19.1,-48.2,-28.2C-40,-37.4,-29.6,-43.7,-19.4,-46.5C-9.2,-49.3,0.8,-48.5,10.9,-46.9C20.9,-45.3,30.9,-42.8,40.9,-39.9C40.9,-39.9,30.5,-45.6,30.5,-45.6Z"
                        transform="translate(75 75)" fill="#FFFFFF" />
                </svg>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
                <div>
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-users mr-3 text-blue-200"></i>Manajemen Pengguna
                    </h1>
                    <p class="text-blue-100 mt-1">Kelola semua akun pengguna dalam sistem</p>
                </div>
                <a href="{{ route('admin.users.create') }}"
                    class="bg-white hover:bg-blue-700 hover:text-white text-blue-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors shadow-md">
                    <i class="fas fa-plus mr-2"></i> Tambah Pengguna Baru
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="m-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md flex items-start">
                <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- Search & Filter Section -->
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center space-y-3 md:space-y-0 md:space-x-4">
                <div class="relative flex-1">
                    <input type="text" placeholder="Cari pengguna..."
                        class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-200 text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <select
                        class="border border-gray-300 rounded-lg py-2 pl-3 pr-8 text-sm text-gray-700 focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                        <option value="">Semua Status</option>
                        <option value="verified">Terverifikasi</option>
                        <option value="unverified">Belum Terverifikasi</option>
                    </select>
                    <button
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg transition duration-200 text-sm font-medium">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="p-6">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.
                                Telp</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Terdaftar</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                        <span>{{ $user->email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-phone text-gray-400 mr-2"></i>
                                        <span>{{ $user->phone ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->email_verified_at)
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Terverifikasi
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> Belum Verifikasi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                        <span>{{ $user->created_at->format('d M Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.users.show', $user->id) }}"
                                            class="text-blue-600 hover:text-blue-900 transition duration-150"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                            class="text-yellow-600 hover:text-yellow-900 transition duration-150"
                                            title="Edit Pengguna">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 transition duration-150"
                                                title="Hapus Pengguna"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500">Tidak ada data pengguna ditemukan</p>
                                        <a href="{{ route('admin.users.create') }}"
                                            class="mt-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-plus mr-2"></i> Tambah Pengguna Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Section -->
        <div class="px-6 py-4 border-t border-gray-100">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari
                    {{ $users->total() ?? 0 }} pengguna
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
