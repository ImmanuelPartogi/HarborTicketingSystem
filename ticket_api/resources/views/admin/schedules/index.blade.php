@extends('admin.layouts.app')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(to right, #1e40af, #3b82f6);
        }
    </style>
@endsection

@section('content')
    <div class="bg-white shadow-md rounded-xl overflow-hidden">
        <!-- Header -->
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
                        <i class="fas fa-calendar-alt mr-3 text-blue-200"></i> Manajemen Jadwal
                    </h1>
                    <p class="mt-1 text-blue-100">Kelola jadwal penyeberangan kapal ferry</p>
                </div>
                <a href="{{ route('admin.schedules.create') }}"
                    class="bg-white hover:bg-blue-700 hover:text-white text-blue-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors shadow-md">
                    <i class="fas fa-plus mr-2"></i> Tambah Jadwal Baru
                </a>
            </div>
        </div>

        <!-- Alert untuk menampilkan masalah cuaca global -->
        @php
            $weatherIssueRoutes = App\Models\Route::where('status', 'WEATHER_ISSUE')->get();
        @endphp

        @if (count($weatherIssueRoutes) > 0)
            <div class="px-6 pt-4">
                <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded-lg shadow-sm"
                    role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0 text-yellow-500 text-xl">
                            <i class="fas fa-cloud-showers-heavy"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium">Peringatan Cuaca</p>
                            <p class="text-sm mt-1">
                                Terdapat {{ count($weatherIssueRoutes) }} rute yang mengalami masalah cuaca:
                                @foreach ($weatherIssueRoutes as $weatherRoute)
                                    <a href="{{ route('admin.routes.show', $weatherRoute->id) }}"
                                        class="underline text-yellow-800 hover:text-yellow-900 font-medium">
                                        {{ $weatherRoute->origin }} - {{ $weatherRoute->destination }}
                                    </a>{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </p>
                            <p class="text-sm mt-1">
                                Jadwal pada rute tersebut mungkin mengalami penundaan atau perubahan. Silakan cek detail
                                rute untuk informasi lebih lanjut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Alerts -->
        <div class="px-6 pt-4">
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg shadow-sm"
                    role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0 text-green-500 text-xl">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg shadow-sm" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0 text-red-500 text-xl">
                            <i class="fas fa-exclamation-circle"></i>
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
            <form action="{{ route('admin.schedules.index') }}" method="GET"
                class="bg-gray-50 p-5 rounded-xl shadow-sm border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                                placeholder="Cari rute atau kapal..."
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label for="route_id" class="block text-sm font-medium text-gray-700 mb-1">Rute</label>
                        <select id="route_id" name="route_id"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 shadow-sm">
                            <option value="">Semua Rute</option>
                            @foreach ($routes as $route)
                                <option value="{{ $route->id }}"
                                    {{ ($routeId ?? '') == $route->id ? 'selected' : '' }}>
                                    {{ $route->origin }} - {{ $route->destination }}
                                    @if ($route->status == 'WEATHER_ISSUE')
                                        (Masalah Cuaca)
                                    @elseif($route->status == 'INACTIVE')
                                        (Tidak Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-1">Kapal</label>
                        <select id="ferry_id" name="ferry_id"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 shadow-sm">
                            <option value="">Semua Kapal</option>
                            @foreach ($ferries as $ferry)
                                <option value="{{ $ferry->id }}"
                                    {{ ($ferryId ?? '') == $ferry->id ? 'selected' : '' }}>
                                    {{ $ferry->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 shadow-sm">
                            <option value="">Semua Status</option>
                            <option value="ACTIVE" {{ ($status ?? '') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                            <option value="CANCELLED" {{ ($status ?? '') == 'CANCELLED' ? 'selected' : '' }}>Dibatalkan
                            </option>
                            <option value="DELAYED" {{ ($status ?? '') == 'DELAYED' ? 'selected' : '' }}>Tertunda</option>
                            <option value="FULL" {{ ($status ?? '') == 'FULL' ? 'selected' : '' }}>Penuh</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center mt-4 justify-end gap-2">
                    <button type="submit"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition flex items-center shadow-sm">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    @if ($search || $routeId || $ferryId || $status)
                        <a href="{{ route('admin.schedules.index') }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition flex items-center shadow-sm">
                            <i class="fas fa-times mr-2"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <div class="px-6 pb-2">
            <p class="text-sm text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg inline-flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                Menampilkan <span class="font-medium mx-1">{{ $schedules->count() }}</span> dari
                <span class="font-medium mx-1">{{ $schedules->total() }}</span> jadwal
            </p>
        </div>

        <!-- Table -->
        <div class="px-6 pb-6">
            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                            </th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rute
                            </th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kapal
                            </th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hari
                                Operasi</th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu
                            </th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                            <tr
                                class="hover:bg-gray-50 transition {{ $schedule->route->status != 'ACTIVE' ? 'bg-gray-50' : '' }}">
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 {{ $schedule->route->status == 'WEATHER_ISSUE' ? 'bg-yellow-100' : ($schedule->route->status == 'INACTIVE' ? 'bg-red-100' : 'bg-indigo-100') }} rounded-lg flex items-center justify-center mr-3 shadow-sm">
                                            <i
                                                class="fas fa-route {{ $schedule->route->status == 'WEATHER_ISSUE' ? 'text-yellow-500' : ($schedule->route->status == 'INACTIVE' ? 'text-red-500' : 'text-indigo-500') }}"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $schedule->route->origin }}
                                                <i class="fas fa-long-arrow-alt-right text-gray-400 mx-1"></i>
                                                {{ $schedule->route->destination }}
                                            </div>
                                            <div
                                                class="text-xs {{ $schedule->route->status != 'ACTIVE' ? 'text-red-500 font-semibold' : 'text-gray-500' }} flex items-center">
                                                @if ($schedule->route->status == 'WEATHER_ISSUE')
                                                    <i class="fas fa-cloud-showers-heavy mr-1"></i> Masalah Cuaca
                                                @elseif($schedule->route->status == 'INACTIVE')
                                                    <i class="fas fa-ban mr-1"></i> Rute Tidak Aktif
                                                @else
                                                    <i class="fas fa-route mr-1"></i> {{ $schedule->route->distance }} km
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 shadow-sm">
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
                                                '1' => 'Senin',
                                                '2' => 'Selasa',
                                                '3' => 'Rabu',
                                                '4' => 'Kamis',
                                                '5' => 'Jumat',
                                                '6' => 'Sabtu',
                                                '7' => 'Minggu',
                                            ];
                                        @endphp

                                        @foreach ($days as $day)
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-md bg-indigo-50 text-indigo-700 shadow-sm">
                                                {{ $dayNames[$day] ?? $day }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="text-sm space-y-2">
                                        <div class="flex items-center p-1.5 rounded-md bg-green-50 text-green-700 w-fit">
                                            <div
                                                class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mr-2 shadow-sm">
                                                <i class="fas fa-ship text-green-500 text-xs"></i>
                                            </div>
                                            <span>{{ $schedule->departure_time->format('H:i') }}</span>
                                        </div>
                                        <div class="flex items-center p-1.5 rounded-md bg-red-50 text-red-700 w-fit">
                                            <div
                                                class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center mr-2 shadow-sm">
                                                <i class="fas fa-anchor text-red-500 text-xs"></i>
                                            </div>
                                            <span>{{ $schedule->arrival_time->format('H:i') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if ($schedule->status == 'ACTIVE')
                                        <span
                                            class="px-3 py-1.5 inline-flex text-xs font-medium rounded-full bg-green-100 text-green-800 shadow-sm">
                                            <i class="fas fa-check-circle mr-1.5"></i> Aktif
                                        </span>
                                    @elseif($schedule->status == 'DELAYED')
                                        <span
                                            class="px-3 py-1.5 inline-flex text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 shadow-sm">
                                            <i class="fas fa-clock mr-1.5"></i> Tertunda
                                            @if ($schedule->route->status == 'WEATHER_ISSUE')
                                                <span class="ml-1">(Cuaca)</span>
                                            @endif
                                        </span>
                                    @elseif($schedule->status == 'FULL')
                                        <span
                                            class="px-3 py-1.5 inline-flex text-xs font-medium rounded-full bg-blue-100 text-blue-800 shadow-sm">
                                            <i class="fas fa-users mr-1.5"></i> Penuh
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1.5 inline-flex text-xs font-medium rounded-full bg-red-100 text-red-800 shadow-sm">
                                            <i class="fas fa-ban mr-1.5"></i> Dibatalkan
                                            @if ($schedule->route->status == 'INACTIVE')
                                                <span class="ml-1">(Rute)</span>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.schedules.show', $schedule->id) }}"
                                            class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition shadow-sm"
                                            title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                            class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded-lg transition shadow-sm"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if ($schedule->status == 'DELAYED' && $schedule->route->status == 'WEATHER_ISSUE')
                                            <a href="{{ route('admin.routes.show', $schedule->route->id) }}"
                                                class="text-purple-600 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 p-2 rounded-lg transition shadow-sm"
                                                title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.schedules.dates', $schedule->id) }}"
                                                class="text-purple-600 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 p-2 rounded-lg transition shadow-sm"
                                                title="Kelola Tanggal">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                        @endif

                                        <form action="{{ route('admin.schedules.destroy', $schedule->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition shadow-sm"
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
                                <td colspan="7" class="py-10 px-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4 shadow-sm">
                                            <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                                        </div>
                                        <p class="text-lg font-medium text-gray-600">Tidak ada data jadwal</p>
                                        <p class="text-sm text-gray-500 mt-1 max-w-md">Silakan tambahkan jadwal baru dengan
                                            mengklik
                                            tombol "Tambah Jadwal Baru" di atas</p>
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
