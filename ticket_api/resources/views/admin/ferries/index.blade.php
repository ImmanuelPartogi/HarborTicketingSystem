@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen Kapal Ferry</h1>
        <a href="{{ route('admin.ferries.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
            <i class="fas fa-plus mr-2"></i> Tambah Kapal Baru
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
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Nama Kapal</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Kapasitas Penumpang</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Kapasitas Kendaraan</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="py-3 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ferries as $ferry)
                <tr>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $ferry->name }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $ferry->passenger_capacity }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">{{ $ferry->vehicle_capacity }}</td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs {{ $ferry->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $ferry->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </td>
                    <td class="py-3 px-4 border-b border-gray-200 text-sm">
                        <a href="{{ route('admin.ferries.show', $ferry->id) }}" class="text-blue-500 hover:text-blue-700 mr-2">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.ferries.edit', $ferry->id) }}" class="text-yellow-500 hover:text-yellow-700 mr-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.ferries.destroy', $ferry->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus kapal ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-3 px-4 border-b border-gray-200 text-sm text-center">Tidak ada data kapal</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ferries->links() }}
    </div>
</div>
@endsection
