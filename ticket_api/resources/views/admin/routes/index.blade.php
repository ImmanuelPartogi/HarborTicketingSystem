@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold">Manajemen Rute</h1>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('admin.routes.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Tambah Rute Baru
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Filter and Search -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
        <form action="{{ route('admin.routes.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-grow">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Rute</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Cari asal atau tujuan..."
                        class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                </div>
            </div>

            <div class="w-full md:w-48">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">Semua Status</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                    <option value="WEATHER_ISSUE" {{ request('status') == 'WEATHER_ISSUE' ? 'selected' : '' }}>Masalah Cuaca</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded h-10">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>

                @if(request('search') || request('status'))
                <a href="{{ route('admin.routes.index') }}" class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded h-10 flex items-center">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th scope="col" class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asal</th>
                    <th scope="col" class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                    <th scope="col" class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jarak (KM)</th>
                    <th scope="col" class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi (Menit)</th>
                    <th scope="col" class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Dasar</th>
                    <th scope="col" class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($routes as $index => $route)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $routes->firstItem() + $index }}</td>
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $route->origin }}</td>
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $route->destination }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $route->distance ?? '-' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $route->duration }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">Rp {{ number_format($route->base_price, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-sm">
                        @if ($route->status == 'ACTIVE')
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Aktif
                            </span>
                        @elseif($route->status == 'WEATHER_ISSUE')
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-cloud-rain mr-1"></i> Masalah Cuaca
                            </span>
                        @else
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-ban mr-1"></i> Tidak Aktif
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-sm">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.routes.show', $route->id) }}"
                                class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded-lg transition"
                                title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.routes.edit', $route->id) }}"
                                class="text-yellow-600 hover:text-yellow-900 bg-yellow-100 hover:bg-yellow-200 p-2 rounded-lg transition"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.routes.destroy', $route->id) }}" method="POST" class="inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded-lg transition delete-btn"
                                    title="Hapus"
                                    data-route-name="{{ $route->origin }} - {{ $route->destination }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-6 px-4 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-route text-gray-300 text-5xl mb-3"></i>
                            <p class="text-lg font-medium">Tidak ada data rute</p>
                            <p class="text-sm text-gray-400">Belum ada rute yang ditambahkan atau sesuai filter yang dipilih</p>
                            <a href="{{ route('admin.routes.create') }}" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i> Tambah Rute Baru
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $routes->links() }}
    </div>
</div>

<!-- Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center h-full w-full p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="mb-4 text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-3"></i>
                <h3 class="text-xl font-bold text-gray-900">Konfirmasi Hapus</h3>
                <p class="text-gray-500 mt-2">Apakah Anda yakin ingin menghapus rute:</p>
                <p id="routeNameToDelete" class="font-semibold text-gray-800 mt-1"></p>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button id="cancelDelete" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Batal
                </button>
                <button id="confirmDelete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete confirmation
        const deleteBtns = document.querySelectorAll('.delete-btn');
        const deleteModal = document.getElementById('deleteModal');
        const confirmDelete = document.getElementById('confirmDelete');
        const cancelDelete = document.getElementById('cancelDelete');
        const routeNameToDelete = document.getElementById('routeNameToDelete');
        let formToSubmit = null;

        function openModal() {
            deleteModal.classList.remove('hidden');
            setTimeout(() => {
                deleteModal.style.opacity = '1';
            }, 10);
        }

        function closeModal() {
            deleteModal.style.opacity = '0';
            setTimeout(() => {
                deleteModal.classList.add('hidden');
            }, 300);
        }

        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                formToSubmit = this.closest('.delete-form');
                const routeName = this.getAttribute('data-route-name');
                routeNameToDelete.textContent = routeName;
                openModal();
            });
        });

        confirmDelete.addEventListener('click', function() {
            if (formToSubmit) {
                formToSubmit.submit();
            }
            closeModal();
        });

        cancelDelete.addEventListener('click', closeModal);

        // Close modal when clicking outside
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeModal();
            }
        });

        // Filter form auto-submit on status change
        const statusSelect = document.getElementById('status');
        statusSelect.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endsection
