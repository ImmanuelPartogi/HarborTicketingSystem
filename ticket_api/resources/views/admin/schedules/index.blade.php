@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="p-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Jadwal</h1>
                <p class="text-gray-600 mt-1">Kelola jadwal penyeberangan kapal ferry</p>
            </div>
            <a href="{{ route('admin.schedules.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition shadow-sm hover:shadow flex items-center">
                <i class="fas fa-plus mr-2"></i> Tambah Jadwal Baru
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <div class="px-6 pt-4">
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r" role="alert">
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

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r" role="alert">
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
        <form action="{{ route('admin.schedules.index') }}" method="GET" class="bg-gray-50 p-4 rounded-lg shadow-inner">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="search" name="search" value="{{ $search ?? '' }}" placeholder="Cari rute atau kapal..." class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5">
                    </div>
                </div>

                <div>
                    <label for="route_id" class="block text-sm font-medium text-gray-700 mb-1">Rute</label>
                    <select id="route_id" name="route_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        <option value="">Semua Rute</option>
                        @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ ($routeId ?? '') == $route->id ? 'selected' : '' }}>
                            {{ $route->origin }} - {{ $route->destination }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-1">Kapal</label>
                    <select id="ferry_id" name="ferry_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        <option value="">Semua Kapal</option>
                        @foreach($ferries as $ferry)
                        <option value="{{ $ferry->id }}" {{ ($ferryId ?? '') == $ferry->id ? 'selected' : '' }}>
                            {{ $ferry->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE" {{ ($status ?? '') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                        <option value="CANCELLED" {{ ($status ?? '') == 'CANCELLED' ? 'selected' : '' }}>Dibatalkan</option>
                        <option value="DELAYED" {{ ($status ?? '') == 'DELAYED' ? 'selected' : '' }}>Tertunda</option>
                        <option value="FULL" {{ ($status ?? '') == 'FULL' ? 'selected' : '' }}>Penuh</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center mt-4 justify-end gap-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition flex items-center">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                @if($search || $routeId || $ferryId || $status)
                <a href="{{ route('admin.schedules.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition flex items-center">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Results Count -->
    <div class="px-6 pb-2">
        <p class="text-sm text-gray-600">
            Menampilkan <span class="font-medium">{{ $schedules->count() }}</span> dari
            <span class="font-medium">{{ $schedules->total() }}</span> jadwal
        </p>
    </div>

    <!-- Table -->
    <div class="px-6 pb-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapal</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari Operasi</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-route text-indigo-500"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $schedule->route->origin }} → {{ $schedule->route->destination }}</div>
                                    <div class="text-xs text-gray-500">{{ $schedule->route->distance }} km</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-ship text-blue-500"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">{{ $schedule->ferry->name }}</div>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $days = explode(',', $schedule->days);
                                    $dayNames = [
                                        '1' => 'Sen',
                                        '2' => 'Sel',
                                        '3' => 'Rab',
                                        '4' => 'Kam',
                                        '5' => 'Jum',
                                        '6' => 'Sab',
                                        '7' => 'Min'
                                    ];
                                @endphp

                                @foreach($days as $day)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        {{ $dayNames[$day] ?? $day }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            <div class="text-sm">
                                <div class="flex items-center mb-1">
                                    <i class="fas fa-plane-departure text-green-500 mr-2"></i>
                                    <span>{{ $schedule->departure_time }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-plane-arrival text-red-500 mr-2"></i>
                                    <span>{{ $schedule->arrival_time }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            @if($schedule->status == 'ACTIVE')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                </span>
                            @elseif($schedule->status == 'DELAYED')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i> Tertunda
                                </span>
                            @elseif($schedule->status == 'FULL')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <i class="fas fa-users mr-1"></i> Penuh
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-ban mr-1"></i> Dibatalkan
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.schedules.show', $schedule->id) }}"
                                   class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded-lg transition"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                   class="text-yellow-600 hover:text-yellow-900 bg-yellow-100 hover:bg-yellow-200 p-2 rounded-lg transition"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.schedules.dates', $schedule->id) }}"
                                   class="text-purple-600 hover:text-purple-900 bg-purple-100 hover:bg-purple-200 p-2 rounded-lg transition"
                                   title="Kelola Tanggal">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                                <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded-lg transition"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini? Jadwal tidak dapat dihapus jika sudah memiliki pemesanan.')"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-times text-gray-300 text-4xl mb-3"></i>
                                <p class="text-lg font-medium">Tidak ada data jadwal</p>
                                <p class="text-sm text-gray-500 mt-1">Silakan tambahkan jadwal baru dengan mengklik tombol "Tambah Jadwal Baru"</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="px-6 pb-6">
        {{ $schedules->appends(['search' => $search ?? null, 'route_id' => $routeId ?? null, 'ferry_id' => $ferryId ?? null, 'status' => $status ?? null])->links() }}
    </div>
</div>
@endsection
