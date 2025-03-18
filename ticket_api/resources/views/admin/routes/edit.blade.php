@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6 text-white">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fas fa-edit mr-3"></i> Edit Rute
            </h1>
            <a href="{{ route('admin.routes.show', $route->id) }}" class="bg-white hover:bg-gray-100 text-blue-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors shadow-sm">
                <i class="fas fa-eye mr-2"></i> Lihat Detail
            </a>
        </div>
        <p class="mt-1 text-blue-100">
            <i class="fas fa-map-marker-alt mr-1"></i> {{ $route->origin }} <i class="fas fa-arrow-right mx-2"></i> {{ $route->destination }}
        </p>
    </div>

    <div class="p-6">
        <!-- Alerts -->
        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium">Ada beberapa kesalahan:</p>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('admin.routes.update', $route->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info Section -->
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i> Informasi Dasar
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">Pelabuhan Asal <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-map-marker-alt text-gray-400"></i>
                            </div>
                            <input type="text" id="origin" name="origin" value="{{ old('origin', $route->origin) }}" required
                                class="pl-10 bg-white border @error('origin') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                        </div>
                        @error('origin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Pelabuhan Tujuan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-map-marker-alt text-gray-400"></i>
                            </div>
                            <input type="text" id="destination" name="destination" value="{{ old('destination', $route->destination) }}" required
                                class="pl-10 bg-white border @error('destination') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                        </div>
                        @error('destination')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="distance" class="block text-sm font-medium text-gray-700 mb-1">Jarak (KM)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-route text-gray-400"></i>
                            </div>
                            <input type="number" id="distance" name="distance" value="{{ old('distance', $route->distance) }}" step="0.01"
                                class="pl-10 bg-white border @error('distance') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                placeholder="Masukkan jarak dalam kilometer">
                        </div>
                        @error('distance')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Durasi (Menit) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                            <input type="number" id="duration" name="duration" value="{{ old('duration', $route->duration) }}" required min="1"
                                class="pl-10 bg-white border @error('duration') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                        </div>
                        @error('duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Waktu perjalanan dalam menit</p>
                    </div>

                    <div>
                        <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Dasar <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="base_price" name="base_price" value="{{ old('base_price', $route->base_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('base_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                        </div>
                        @error('base_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Harga tiket dasar per orang</p>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-toggle-on text-gray-400"></i>
                            </div>
                            <select id="status" name="status" required
                                class="pl-10 bg-white border @error('status') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                                <option value="ACTIVE" {{ old('status', $route->status) == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                                <option value="INACTIVE" {{ old('status', $route->status) == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                                <option value="WEATHER_ISSUE" {{ old('status', $route->status) == 'WEATHER_ISSUE' ? 'selected' : '' }}>Masalah Cuaca</option>
                            </select>
                        </div>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1 flex items-start">
                            <i class="fas fa-info-circle mr-1 mt-0.5 text-blue-500"></i>
                            <span>Perubahan status di sini hanya mempengaruhi rute ini, bukan jadwal terkait</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Vehicle Prices Section -->
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-car mr-2 text-green-500"></i> Harga Tambahan untuk Kendaraan
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label for="motorcycle_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Motor <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="motorcycle_price" name="motorcycle_price" value="{{ old('motorcycle_price', $route->motorcycle_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('motorcycle_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                        </div>
                        @error('motorcycle_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="car_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Mobil <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="car_price" name="car_price" value="{{ old('car_price', $route->car_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('car_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                        </div>
                        @error('car_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bus_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Bus <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="bus_price" name="bus_price" value="{{ old('bus_price', $route->bus_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('bus_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                        </div>
                        @error('bus_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="truck_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Truk <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="truck_price" name="truck_price" value="{{ old('truck_price', $route->truck_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('truck_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                        </div>
                        @error('truck_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end mt-6 space-x-3">
                <a href="{{ route('admin.routes.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2.5 px-5 rounded-lg transition-colors shadow-sm flex items-center">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-5 rounded-lg transition-colors shadow-sm flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animated highlight for changed fields
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                this.classList.add('bg-blue-50');
                setTimeout(() => {
                    this.classList.remove('bg-blue-50');
                }, 1000);
            });
        });

        // Status change confirmation if needed
        const statusSelect = document.getElementById('status');
        const originalStatus = "{{ $route->status }}";

        statusSelect.addEventListener('change', function() {
            if (this.value !== originalStatus) {
                if (this.value === 'INACTIVE') {
                    const shouldChange = confirm('Mengubah status rute menjadi tidak aktif akan berdampak pada jadwal keberangkatan yang terkait. Apakah Anda yakin?');

                    if (!shouldChange) {
                        this.value = originalStatus;
                    }
                } else if (this.value === 'WEATHER_ISSUE') {
                    const shouldChange = confirm('Mengubah status rute menjadi "Masalah Cuaca" akan menandai jadwal terkait sebagai tertunda. Perbarui status?');

                    if (!shouldChange) {
                        this.value = originalStatus;
                    }
                }
            }
        });

        // Format currency for price inputs
        const priceInputs = document.querySelectorAll('input[type="number"][name$="_price"]');
        priceInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value) {
                    const formattedValue = new Intl.NumberFormat('id-ID').format(this.value);
                    this.setAttribute('data-formatted', `Rp ${formattedValue}`);
                }
            });

            // Trigger initial formatting
            if (input.value) {
                const formattedValue = new Intl.NumberFormat('id-ID').format(input.value);
                input.setAttribute('data-formatted', `Rp ${formattedValue}`);
            }
        });
    });
</script>
@endsection
