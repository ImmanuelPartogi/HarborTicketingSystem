@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen Jadwal</h1>
        <a href="{{ route('admin.schedules.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
            <i class="fas fa-plus mr-2"></i> Tambah Jadwal Baru
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="mb-6">
        <form action="{{ route('admin.schedules.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="route_id" class="block text-sm font-medium text-gray-700 mb-1">Rute</label>
                <select id="route_id" name="route_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Semua Rute</option>
                    @foreach($routes as $route)
                    <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                        {{ $route->origin }} - {{ $route->destination }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="ferry_id" class="block text-sm font-medium text-gray-700 mb-1">Kapal</label>
                <select id="ferry_id" name="ferry_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Semua Kapal</option>
                    @foreach($ferries as $ferry)
                    <option value="{{ $ferry->id }}" {{ request('ferry_id') == $ferry->id ? 'selected' : '' }}>
                        {{ $ferry->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                    Filter
                </button>
                <a href="{{ route('admin.schedules.index') }}" class="text-gray-500 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 ml-2">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">#</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Rute</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Kapal</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Hari</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Waktu Keberangkatan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Harga</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                <tr>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $schedule->route->origin }} - {{ $schedule->route->destination }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $schedule->ferry->name }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $schedule->day }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $schedule->departure_time }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($schedule->price, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs {{ $schedule->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $schedule->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        <a href="{{ route('admin.schedules.show', $schedule->id) }}" class="text-blue-500 hover:text-blue-700 mr-2">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}" class="text-yellow-500 hover:text-yellow-700 mr-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('admin.schedules.dates', $schedule->id) }}" class="text-indigo-500 hover:text-indigo-700 mr-2" title="Kelola Tanggal">
                            <i class="fas fa-calendar-day"></i>
                        </a>
                        <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data jadwal</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $schedules->links() }}
    </div>
</div>
@endsection
