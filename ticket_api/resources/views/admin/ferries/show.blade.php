@extends('admin.layouts.app')

@section('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .ferry-image {
        transition: all 0.3s ease;
    }
    .ferry-image:hover {
        transform: scale(1.03);
    }
    .action-button {
        transition: all 0.2s ease;
    }
    .action-button:hover {
        transform: translateY(-3px);
    }
    .schedule-row {
        transition: all 0.2s ease;
    }
    .schedule-row:hover {
        background-color: #f9fafb;
    }
    .detail-header {
        background: linear-gradient(to right, #1e3a8a, #2563eb);
    }
    .vehicle-icon {
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        color: white;
        font-size: 1rem;
        margin-right: 0.75rem;
    }
    .capacity-card {
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    .capacity-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border-color: #bfdbfe;
    }
    .img-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .img-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0) 60%, rgba(0,0,0,0.3) 100%);
        z-index: 1;
        pointer-events: none;
    }
    .ferry-data-card {
        transition: all 0.3s ease;
    }
    .ferry-data-card:hover {
        transform: translateY(-3px);
    }
    .calendar-icon {
        font-size: 1.25rem;
        color: #4f46e5;
        transition: all 0.3s ease;
    }
    .schedule-row:hover .calendar-icon {
        transform: rotate(15deg);
    }
    .capacity-section {
        background-image: radial-gradient(circle at top right, rgba(96, 165, 250, 0.1), transparent 600px);
    }
    .mobile-schedule {
        transition: all 0.3s ease;
    }
    .mobile-schedule:hover {
        transform: translateY(-3px);
    }
</style>
@endsection

@section('content')
<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="detail-header p-6 text-white">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fas fa-ship mr-3"></i> Detail Kapal Ferry
            </h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.ferries.edit', $ferry->id) }}" class="action-button bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition duration-300 flex items-center shadow-md">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('admin.ferries.index') }}" class="action-button bg-white text-blue-700 hover:bg-blue-50 py-2 px-4 rounded-lg transition duration-300 flex items-center shadow-md">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="col-span-1">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl overflow-hidden shadow-sm p-5 border border-blue-100">
                    <div class="flex justify-center mb-6">
                        @if($ferry->image)
                            <div class="img-wrapper w-full h-56 rounded-lg overflow-hidden shadow-md">
                                <img src="{{ asset('storage/' . $ferry->image) }}" alt="{{ $ferry->name }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="ferry-image bg-blue-100 w-full h-56 rounded-lg flex items-center justify-center shadow-md">
                                <i class="fas fa-ship text-blue-500 text-6xl"></i>
                            </div>
                        @endif
                    </div>

                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">{{ $ferry->name }}</h2>

                    <div class="flex justify-center mb-4">
                        @if ($ferry->status == 'ACTIVE')
                            <span class="px-4 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 flex items-center">
                                <i class="fas fa-check-circle mr-2"></i> Aktif
                            </span>
                        @elseif($ferry->status == 'MAINTENANCE')
                            <span class="px-4 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 flex items-center">
                                <i class="fas fa-tools mr-2"></i> Pemeliharaan
                            </span>
                        @else
                            <span class="px-4 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800 flex items-center">
                                <i class="fas fa-times-circle mr-2"></i> Tidak Aktif
                            </span>
                        @endif
                    </div>

                    @if($ferry->description)
                    <div class="mt-6 p-4 bg-white rounded-lg shadow-sm ferry-data-card">
                        <h3 class="text-lg font-semibold mb-2 text-gray-800 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-500"></i> Deskripsi
                        </h3>
                        <p class="text-gray-600">{{ $ferry->description }}</p>
                    </div>
                    @endif

                    <div class="mt-6 p-4 bg-white rounded-lg shadow-sm ferry-data-card">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 flex items-center">
                            <i class="fas fa-clock mr-2 text-blue-500"></i> Informasi Tambahan
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <p class="text-sm text-gray-500">Dibuat Pada</p>
                                <p class="font-medium flex items-center">
                                    <i class="fas fa-calendar-plus text-blue-400 mr-2"></i>
                                    {{ $ferry->created_at->format('d M Y H:i') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Terakhir Diperbarui</p>
                                <p class="font-medium flex items-center">
                                    <i class="fas fa-calendar-check text-blue-400 mr-2"></i>
                                    {{ $ferry->updated_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 p-4 bg-white rounded-lg shadow-sm ferry-data-card">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 flex items-center">
                            <i class="fas fa-bolt mr-2 text-blue-500"></i> Aksi Cepat
                        </h3>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('admin.ferries.edit', $ferry->id) }}"
                               class="w-full py-2 px-3 bg-yellow-100 text-yellow-800 rounded-lg flex items-center justify-between hover:bg-yellow-200 transition duration-200">
                                <span class="flex items-center">
                                    <i class="fas fa-edit mr-2"></i> Edit Kapal
                                </span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="{{ route('admin.schedules.create') }}"
                               class="w-full py-2 px-3 bg-green-100 text-green-800 rounded-lg flex items-center justify-between hover:bg-green-200 transition duration-200">
                                <span class="flex items-center">
                                    <i class="fas fa-calendar-plus mr-2"></i> Tambah Jadwal
                                </span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <form action="{{ route('admin.ferries.destroy', $ferry->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus kapal ini? Kapal tidak dapat dihapus jika masih memiliki jadwal terkait.')"
                                        class="w-full py-2 px-3 bg-red-100 text-red-800 rounded-lg flex items-center justify-between hover:bg-red-200 transition duration-200">
                                    <span class="flex items-center">
                                        <i class="fas fa-trash mr-2"></i> Hapus Kapal
                                    </span>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-span-1 lg:col-span-2">
                <!-- Capacity Section -->
                <div class="capacity-card mb-6 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-blue-50">
                        <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                            <i class="fas fa-users mr-2"></i> Informasi Kapasitas
                        </h3>
                    </div>

                    <div class="p-5 capacity-section">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            <div class="stat-card flex items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                                <div class="vehicle-icon bg-blue-500">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Kapasitas Penumpang</p>
                                    <p class="text-xl font-bold text-gray-800">{{ number_format($ferry->capacity_passenger) }}</p>
                                    <p class="text-xs text-gray-500">orang</p>
                                </div>
                            </div>

                            <div class="stat-card flex items-center p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                                <div class="vehicle-icon bg-gray-600">
                                    <i class="fas fa-motorcycle"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Kapasitas Motor</p>
                                    <p class="text-xl font-bold text-gray-800">{{ number_format($ferry->capacity_vehicle_motorcycle) }}</p>
                                    <p class="text-xs text-gray-500">unit</p>
                                </div>
                            </div>

                            <div class="stat-card flex items-center p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg border border-indigo-200">
                                <div class="vehicle-icon bg-indigo-500">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Kapasitas Mobil</p>
                                    <p class="text-xl font-bold text-gray-800">{{ number_format($ferry->capacity_vehicle_car) }}</p>
                                    <p class="text-xs text-gray-500">unit</p>
                                </div>
                            </div>

                            <div class="stat-card flex items-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200">
                                <div class="vehicle-icon bg-green-500">
                                    <i class="fas fa-bus"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Kapasitas Bus</p>
                                    <p class="text-xl font-bold text-gray-800">{{ number_format($ferry->capacity_vehicle_bus) }}</p>
                                    <p class="text-xs text-gray-500">unit</p>
                                </div>
                            </div>

                            <div class="stat-card flex items-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg border border-orange-200">
                                <div class="vehicle-icon bg-orange-500">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Kapasitas Truk</p>
                                    <p class="text-xl font-bold text-gray-800">{{ number_format($ferry->capacity_vehicle_truck) }}</p>
                                    <p class="text-xs text-gray-500">unit</p>
                                </div>
                            </div>

                            <div class="stat-card flex items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200 sm:col-span-2 md:col-span-3">
                                <div class="w-full">
                                    <div class="flex justify-between items-center mb-2">
                                        <p class="text-sm font-medium text-gray-700">Total Kapasitas Kendaraan</p>
                                        <p class="text-xl font-bold text-gray-800">
                                            {{ number_format($ferry->capacity_vehicle_motorcycle + $ferry->capacity_vehicle_car + $ferry->capacity_vehicle_bus + $ferry->capacity_vehicle_truck) }}
                                            <span class="text-xs text-gray-500">unit</span>
                                        </p>
                                    </div>
                                    <div class="h-4 w-full bg-gray-200 rounded-full overflow-hidden">
                                        @php
                                            $totalVehicles = $ferry->capacity_vehicle_motorcycle + $ferry->capacity_vehicle_car + $ferry->capacity_vehicle_bus + $ferry->capacity_vehicle_truck;
                                            $motorcyclePercent = $totalVehicles > 0 ? ($ferry->capacity_vehicle_motorcycle / $totalVehicles) * 100 : 0;
                                            $carPercent = $totalVehicles > 0 ? ($ferry->capacity_vehicle_car / $totalVehicles) * 100 : 0;
                                            $busPercent = $totalVehicles > 0 ? ($ferry->capacity_vehicle_bus / $totalVehicles) * 100 : 0;
                                            $truckPercent = $totalVehicles > 0 ? ($ferry->capacity_vehicle_truck / $totalVehicles) * 100 : 0;
                                        @endphp
                                        <div class="h-full bg-gray-600 float-left" style="width: {{ $motorcyclePercent }}%"></div>
                                        <div class="h-full bg-indigo-500 float-left" style="width: {{ $carPercent }}%"></div>
                                        <div class="h-full bg-green-500 float-left" style="width: {{ $busPercent }}%"></div>
                                        <div class="h-full bg-orange-500 float-left" style="width: {{ $truckPercent }}%"></div>
                                    </div>
                                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                                        <span>Motor</span>
                                        <span>Mobil</span>
                                        <span>Bus</span>
                                        <span>Truk</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Section -->
                <div class="capacity-card">
                    <div class="p-4 border-b border-gray-200 bg-blue-50 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i> Jadwal Keberangkatan
                        </h3>
                        <span class="text-sm text-blue-600 font-medium px-3 py-1 bg-blue-100 rounded-full">
                            Total: {{ $ferry->schedules->count() }} jadwal
                        </span>
                    </div>

                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <!-- Desktop schedule view -->
                            <div class="hidden sm:block">
                                <table class="min-w-full bg-white border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="py-3 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Rute</th>
                                            <th class="py-3 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Hari</th>
                                            <th class="py-3 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Waktu</th>
                                            <th class="py-3 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                            <th class="py-3 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ferry->schedules as $schedule)
                                        <tr class="schedule-row">
                                            <td class="py-3 px-4 border-b border-gray-200 text-sm font-medium">
                                                <div class="flex items-center">
                                                    <i class="fas fa-route text-blue-500 mr-2"></i>
                                                    <span>{{ $schedule->route->origin }} - {{ $schedule->route->destination }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                                {{ $schedule->days }}
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200 text-sm font-medium">
                                                <div class="flex items-center">
                                                    <i class="far fa-clock calendar-icon mr-2"></i>
                                                    <span>{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200 text-sm">
                                                @if ($schedule->status == 'ACTIVE')
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 flex items-center w-fit">
                                                        <i class="fas fa-check-circle mr-1"></i> Aktif
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 flex items-center w-fit">
                                                        <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                                   class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200 transition inline-flex items-center">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="py-6 px-4 border-b border-gray-200 text-sm text-center text-gray-500">
                                                <div class="flex flex-col items-center">
                                                    <i class="fas fa-calendar-times text-gray-300 text-3xl mb-2"></i>
                                                    <p>Tidak ada jadwal keberangkatan</p>
                                                    <a href="{{ route('admin.schedules.create') }}" class="mt-3 px-3 py-1 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center">
                                                        <i class="fas fa-plus mr-1"></i> Tambah Jadwal Baru
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile schedule view -->
                            <div class="sm:hidden space-y-4">
                                @forelse($ferry->schedules as $schedule)
                                <div class="mobile-schedule bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                                    <div class="p-3 bg-gray-50 border-b border-gray-200">
                                        <div class="flex justify-between items-center">
                                            <div class="font-medium text-sm">
                                                <i class="fas fa-route text-blue-500 mr-1"></i>
                                                {{ $schedule->route->origin }} - {{ $schedule->route->destination }}
                                            </div>
                                            @if ($schedule->status == 'ACTIVE')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 flex items-center">
                                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 flex items-center">
                                                    <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <p class="text-xs text-gray-500">Hari</p>
                                                <p class="text-sm font-medium">{{ $schedule->days }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Waktu</p>
                                                <p class="text-sm font-medium flex items-center">
                                                    <i class="far fa-clock text-gray-400 mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-3 pt-2 border-t border-gray-100">
                                            <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                               class="w-full py-1 text-center bg-yellow-50 text-yellow-800 rounded flex items-center justify-center hover:bg-yellow-100 transition">
                                                <i class="fas fa-edit mr-1"></i> Edit Jadwal
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-500">
                                    <i class="fas fa-calendar-times text-gray-300 text-3xl mb-2"></i>
                                    <p>Tidak ada jadwal keberangkatan</p>
                                    <a href="{{ route('admin.schedules.create') }}" class="mt-3 px-3 py-1 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center">
                                        <i class="fas fa-plus mr-1"></i> Tambah Jadwal
                                    </a>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
