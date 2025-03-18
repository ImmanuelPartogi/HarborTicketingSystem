@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6 text-white">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fas fa-plus-circle mr-3"></i> Tambah Rute Baru
            </h1>
        </div>
    </div>

    <div class="p-6">
        <!-- Alerts -->
        @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                </div>
                <div class="ml-3">
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

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
        <form action="{{ route('admin.routes.store') }}" method="POST" novalidate class="space-y-6">
            @csrf

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
                            <input type="text" id="origin" name="origin" value="{{ old('origin') }}" required
                                class="pl-10 bg-white border @error('origin') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                placeholder="Mis. Pelabuhan Merak"
                                aria-describedby="origin-error">
                        </div>
                        @error('origin')
                            <p id="origin-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Pelabuhan Tujuan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-map-marker-alt text-gray-400"></i>
                            </div>
                            <input type="text" id="destination" name="destination" value="{{ old('destination') }}" required
                                class="pl-10 bg-white border @error('destination') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                placeholder="Mis. Pelabuhan Bakauheni"
                                aria-describedby="destination-error">
                        </div>
                        @error('destination')
                            <p id="destination-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="distance" class="block text-sm font-medium text-gray-700 mb-1">Jarak (KM)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-route text-gray-400"></i>
                            </div>
                            <input type="number" id="distance" name="distance" value="{{ old('distance') }}" step="0.01" min="0"
                                class="pl-10 bg-white border @error('distance') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                placeholder="Mis. 42.5"
                                aria-describedby="distance-error">
                        </div>
                        @error('distance')
                            <p id="distance-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Jarak dalam kilometer</p>
                    </div>

                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Durasi (Menit) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                            <input type="number" id="duration" name="duration" value="{{ old('duration') }}" required min="1"
                                class="pl-10 bg-white border @error('duration') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                placeholder="Mis. 90"
                                aria-describedby="duration-error">
                        </div>
                        @error('duration')
                            <p id="duration-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Durasi perjalanan dalam menit</p>
                    </div>

                    <div>
                        <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Dasar <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="base_price" name="base_price" value="{{ old('base_price') }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('base_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                placeholder="Mis. 50000"
                                aria-describedby="base_price-error">
                        </div>
                        @error('base_price')
                            <p id="base_price-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Harga dasar untuk penumpang</p>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-toggle-on text-gray-400"></i>
                            </div>
                            <select id="status" name="status" required
                                class="pl-10 bg-white border @error('status') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                aria-describedby="status-error">
                                <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                                <option value="INACTIVE" {{ old('status') == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                                <option value="WEATHER_ISSUE" {{ old('status') == 'WEATHER_ISSUE' ? 'selected' : '' }}>Masalah Cuaca</option>
                            </select>
                        </div>
                        @error('status')
                            <p id="status-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Status keaktifan rute</p>
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
                            <input type="number" id="motorcycle_price" name="motorcycle_price" value="{{ old('motorcycle_price') }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('motorcycle_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                aria-describedby="motorcycle_price-error">
                        </div>
                        @error('motorcycle_price')
                            <p id="motorcycle_price-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="car_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Mobil <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="car_price" name="car_price" value="{{ old('car_price') }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('car_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                aria-describedby="car_price-error">
                        </div>
                        @error('car_price')
                            <p id="car_price-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bus_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Bus <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="bus_price" name="bus_price" value="{{ old('bus_price') }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('bus_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                aria-describedby="bus_price-error">
                        </div>
                        @error('bus_price')
                            <p id="bus_price-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="truck_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Truk <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="truck_price" name="truck_price" value="{{ old('truck_price') }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('truck_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm"
                                aria-describedby="truck_price-error">
                        </div>
                        @error('truck_price')
                            <p id="truck_price-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
        // Form validation
        const form = document.querySelector('form');
        const requiredInputs = form.querySelectorAll('input[required], select[required]');

        form.addEventListener('submit', function(event) {
            let hasError = false;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    const name = input.getAttribute('name');
                    let errorElement = document.getElementById(`${name}-error`);

                    if (!errorElement) {
                        errorElement = document.createElement('p');
                        errorElement.id = `${name}-error`;
                        errorElement.className = 'mt-1 text-sm text-red-600';
                        input.parentNode.appendChild(errorElement);
                    }

                    errorElement.textContent = 'Bidang ini harus diisi';
                    input.classList.add('border-red-300');
                    input.classList.remove('border-gray-300');
                    hasError = true;
                }
            });

            if (hasError) {
                event.preventDefault();
                // Scroll to the first error
                const firstError = document.querySelector('.text-red-600');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Clear validation errors on input
        requiredInputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('border-red-300');
                    this.classList.add('border-gray-300');

                    const name = this.getAttribute('name');
                    const errorElement = document.getElementById(`${name}-error`);

                    if (errorElement) {
                        errorElement.textContent = '';
                    }
                }
            });
        });

        // Price formatting
        const priceInputs = document.querySelectorAll('input[type="number"][name$="_price"]');
        priceInputs.forEach(input => {
            input.addEventListener('focus', function() {
                if (this.value === '0') {
                    this.value = '';
                }
            });

            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.value = '0';
                }
            });
        });
    });
</script>
@endsection
