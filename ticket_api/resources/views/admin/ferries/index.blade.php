@extends('admin.layouts.app')

@section('styles')
    <style>
        .ferry-card {
            transition: all 0.3s ease;
        }

        .ferry-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .action-button {
            transition: all 0.2s ease;
        }

        .action-button:hover {
            transform: scale(1.1);
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }

        .ferry-avatar {
            transition: all 0.3s ease;
            position: relative;
        }

        .ferry-avatar:hover {
            transform: scale(1.1);
        }

        .animated-badge {
            position: relative;
        }

        .animated-badge.active::before {
            content: '';
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #10b981;
            top: 50%;
            left: 5px;
            transform: translateY(-50%);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                box-shadow: 0 0 0 5px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .table-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .page-header {
            background: linear-gradient(to right, #1e40af, #3b82f6);
        }

        .filter-section {
            background-image: radial-gradient(circle at top right, rgba(59, 130, 246, 0.1), transparent 400px);
        }

        .table-header th {
            position: relative;
            overflow: hidden;
        }

        .table-header th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(to right, #3b82f6, transparent);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .table-header th:hover::after {
            transform: scaleX(1);
        }

        .card-entry {
            transition: all 0.3s ease;
        }

        .card-entry:not(:last-child) {
            border-bottom: 1px solid #e5e7eb;
        }

        .card-entry:hover {
            background-color: #f9fafb;
        }
    </style>
@endsection

@section('content')
    <div class="bg-white shadow-lg rounded-lg overflow-hidden table-shadow">
        <!-- Header -->
        <div class="page-header p-6 text-white">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-ship mr-3 text-blue-200"></i> Manajemen Kapal Ferry
                    </h1>
                    <p class="text-blue-100 mt-1">Kelola semua kapal ferry dalam sistem</p>
                </div>
                <a href="{{ route('admin.ferries.create') }}"
                    class="bg-white text-blue-600 hover:bg-blue-50 py-2 px-4 rounded-lg transition shadow-sm hover:shadow flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Kapal Baru
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <div class="px-6 pt-4">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r shadow-sm"
                    role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r shadow-sm" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Search and Filter Form -->
        <div class="px-6 pb-4">
            <form method="GET" action="{{ route('admin.ferries.index') }}"
                class="filter-section p-5 rounded-lg border border-gray-200 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" name="search" id="search" value="{{ $search ?? '' }}"
                                placeholder="Cari nama kapal..."
                                class="search-input bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 transition-all duration-200">
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-all duration-200">
                            <option value="">Semua Status</option>
                            <option value="ACTIVE" {{ ($status ?? '') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                            <option value="MAINTENANCE" {{ ($status ?? '') == 'MAINTENANCE' ? 'selected' : '' }}>
                                Pemeliharaan</option>
                            <option value="INACTIVE" {{ ($status ?? '') == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition flex items-center shadow-sm hover:shadow">
                            <i class="fas fa-search mr-2"></i> Cari
                        </button>
                        @if ($search || $status)
                            <a href="{{ route('admin.ferries.index') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition flex items-center shadow-sm hover:shadow">
                                <i class="fas fa-times mr-2"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <div class="px-6 pb-2">
            <p class="text-sm text-gray-600">
                Menampilkan <span class="font-medium">{{ $ferries->count() }}</span> dari
                <span class="font-medium">{{ $ferries->total() }}</span> kapal
            </p>
        </div>

        <!-- Table (Desktop Version) -->
        <div class="hidden md:block px-6 pb-6">
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="table-header">
                            <th scope="col"
                                class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                            </th>
                            <th scope="col"
                                class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                                Kapal</th>
                            <th scope="col"
                                class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kapasitas Penumpang</th>
                            <th scope="col"
                                class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kapasitas Kendaraan</th>
                            <th scope="col"
                                class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($ferries as $ferry)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if ($ferry->image)
                                            <div class="ferry-avatar flex-shrink-0 h-10 w-10 mr-3">
                                                <img class="h-10 w-10 rounded-full object-cover shadow-sm border border-gray-200"
                                                    src="{{ asset($ferry->image) }}" alt="{{ $ferry->name }}">
                                            </div>
                                        @else
                                            <div
                                                class="ferry-avatar flex-shrink-0 h-10 w-10 mr-3 bg-blue-100 rounded-full flex items-center justify-center border border-blue-200">
                                                <i class="fas fa-ship text-blue-500"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $ferry->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-users text-blue-400 mr-2"></i>
                                        <span
                                            class="text-sm font-medium text-gray-900">{{ number_format($ferry->capacity_passenger) }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                                        <div class="flex items-center">
                                            <i class="fas fa-motorcycle text-gray-400 mr-2 w-5"></i>
                                            <span>{{ number_format($ferry->capacity_vehicle_motorcycle) }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-car text-gray-400 mr-2 w-5"></i>
                                            <span>{{ number_format($ferry->capacity_vehicle_car) }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-bus text-gray-400 mr-2 w-5"></i>
                                            <span>{{ number_format($ferry->capacity_vehicle_bus) }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-truck text-gray-400 mr-2 w-5"></i>
                                            <span>{{ number_format($ferry->capacity_vehicle_truck) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if ($ferry->status == 'ACTIVE')
                                        <span
                                            class="animated-badge active px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle ml-1 mr-1"></i> Aktif
                                        </span>
                                    @elseif($ferry->status == 'MAINTENANCE')
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-tools mr-1"></i> Pemeliharaan
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.ferries.show', $ferry->id) }}"
                                            class="action-button text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded-lg transition"
                                            title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.ferries.edit', $ferry->id) }}"
                                            class="action-button text-yellow-600 hover:text-yellow-900 bg-yellow-100 hover:bg-yellow-200 p-2 rounded-lg transition"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.ferries.destroy', $ferry->id) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="action-button text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded-lg transition"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus kapal ini? Kapal tidak dapat dihapus jika masih memiliki jadwal terkait.')"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-ship text-gray-300 text-4xl mb-3"></i>
                                        <p class="text-lg font-medium">Tidak ada data kapal</p>
                                        <p class="text-sm text-gray-500 mt-1">Silakan tambahkan kapal baru dengan mengklik
                                            tombol "Tambah Kapal Baru"</p>
                                        <a href="{{ route('admin.ferries.create') }}"
                                            class="mt-4 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition flex items-center shadow-sm hover:shadow">
                                            <i class="fas fa-plus mr-2"></i> Tambah Kapal Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card View (Mobile Version) -->
        <div class="md:hidden px-6 pb-6">
            <div class="space-y-4">
                @forelse($ferries as $ferry)
                    <div class="ferry-card bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                        <div class="p-4 flex items-center border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center flex-1">
                                @if ($ferry->image)
                                    <div class="ferry-avatar flex-shrink-0 h-12 w-12 mr-3">
                                        <img class="h-12 w-12 rounded-full object-cover shadow-sm border border-gray-200"
                                            src="{{ asset($ferry->image) }}" alt="{{ $ferry->name }}">
                                    </div>
                                @else
                                    <div
                                        class="ferry-avatar flex-shrink-0 h-12 w-12 mr-3 bg-blue-100 rounded-full flex items-center justify-center border border-blue-200">
                                        <i class="fas fa-ship text-blue-500 text-xl"></i>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-md font-bold text-gray-900">{{ $ferry->name }}</h3>
                                    @if ($ferry->status == 'ACTIVE')
                                        <span
                                            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Aktif
                                        </span>
                                    @elseif($ferry->status == 'MAINTENANCE')
                                        <span
                                            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-tools mr-1"></i> Pemeliharaan
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="card-entry flex items-center py-2">
                                    <i class="fas fa-users text-blue-400 mr-2 w-5"></i>
                                    <div>
                                        <div class="text-xs text-gray-500">Penumpang</div>
                                        <div class="text-sm font-medium">{{ number_format($ferry->capacity_passenger) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-entry flex items-center py-2">
                                    <i class="fas fa-motorcycle text-gray-400 mr-2 w-5"></i>
                                    <div>
                                        <div class="text-xs text-gray-500">Motor</div>
                                        <div class="text-sm font-medium">
                                            {{ number_format($ferry->capacity_vehicle_motorcycle) }}</div>
                                    </div>
                                </div>
                                <div class="card-entry flex items-center py-2">
                                    <i class="fas fa-car text-gray-400 mr-2 w-5"></i>
                                    <div>
                                        <div class="text-xs text-gray-500">Mobil</div>
                                        <div class="text-sm font-medium">{{ number_format($ferry->capacity_vehicle_car) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-entry flex items-center py-2">
                                    <i class="fas fa-bus text-gray-400 mr-2 w-5"></i>
                                    <div>
                                        <div class="text-xs text-gray-500">Bus</div>
                                        <div class="text-sm font-medium">{{ number_format($ferry->capacity_vehicle_bus) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-3 flex justify-between">
                                <a href="{{ route('admin.ferries.show', $ferry->id) }}"
                                    class="flex-1 text-center text-blue-600 hover:text-blue-800 px-2 py-1 rounded transition"
                                    title="Detail">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
                                <div class="border-r border-gray-200"></div>
                                <a href="{{ route('admin.ferries.edit', $ferry->id) }}"
                                    class="flex-1 text-center text-yellow-600 hover:text-yellow-800 px-2 py-1 rounded transition"
                                    title="Edit">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <div class="border-r border-gray-200"></div>
                                <form action="{{ route('admin.ferries.destroy', $ferry->id) }}" method="POST"
                                    class="flex-1 text-center">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition w-full"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus kapal ini? Kapal tidak dapat dihapus jika masih memiliki jadwal terkait.')"
                                        title="Hapus">
                                        <i class="fas fa-trash mr-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg border border-gray-200 py-8 px-4 text-center text-gray-500 shadow-sm">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-ship text-gray-300 text-4xl mb-3"></i>
                            <p class="text-lg font-medium">Tidak ada data kapal</p>
                            <p class="text-sm text-gray-500 mt-1 mb-4">Silakan tambahkan kapal baru</p>
                            <a href="{{ route('admin.ferries.create') }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition flex items-center shadow-sm hover:shadow">
                                <i class="fas fa-plus mr-2"></i> Tambah Kapal Baru
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-6 pb-6">
            {{ $ferries->appends(['search' => $search, 'status' => $status])->links() }}
        </div>
    </div>
@endsection
