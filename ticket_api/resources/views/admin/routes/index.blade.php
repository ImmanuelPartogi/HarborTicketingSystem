@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen Rute</h1>
        <a href="{{ route('admin.routes.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
            <i class="fas fa-plus mr-2"></i> Tambah Rute Baru
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">#</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Asal</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Tujuan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Jarak (KM)</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Durasi (Menit)</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Harga Dasar</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routes as $route)
                <tr>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->origin }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->destination }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->distance }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $route->duration }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">Rp {{ number_format($route->base_price, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        @if ($route->status == 'ACTIVE')
                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @elseif($route->status == 'WEATHER_ISSUE')
                            <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                Masalah Cuaca
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                Tidak Aktif
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        <a href="{{ route('admin.routes.show', $route->id) }}" class="text-blue-500 hover:text-blue-700 mr-2">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.routes.edit', $route->id) }}" class="text-yellow-500 hover:text-yellow-700 mr-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.routes.destroy', $route->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus rute ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data rute</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $routes->links() }}
    </div>
</div>
@endsection
