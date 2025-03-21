@extends('admin.layouts.app')

@section('styles')
<style>
    .input-group {
        transition: all 0.3s ease;
    }
    .input-group:focus-within {
        transform: translateY(-2px);
    }
    .form-section {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .form-section:hover {
        border-color: #bfdbfe;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }
    .header-gradient {
        background: linear-gradient(to right, #1e3a8a, #2563eb);
    }
    .required-badge {
        position: relative;
        top: -1px;
    }
    .route-summary {
        background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
        border-radius: 0.5rem;
        border: 1px solid #bfdbfe;
    }
    .help-tip {
        position: relative;
        display: inline-block;
    }
    .help-tip:hover .tip-content {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .tip-content {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-10px);
        background-color: #333;
        color: white;
        padding: 0.5rem;
        border-radius: 0.25rem;
        width: 200px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 10;
    }
    .tip-content::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
    }
    .price-highlight {
        transition: all 0.3s ease;
    }
    .price-highlight.changed {
        background-color: #eff6ff;
    }
</style>
@endsection

@section('content')
<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="header-gradient p-6 text-white">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-edit mr-3"></i> Edit Rute
                </h1>
                <p class="mt-1 text-blue-100">
                    <i class="fas fa-map-marker-alt mr-1"></i> {{ $route->origin }} <i class="fas fa-arrow-right mx-2"></i> {{ $route->destination }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.routes.show', $route->id) }}" class="bg-white hover:bg-gray-100 text-blue-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors shadow-sm">
                    <i class="fas fa-eye mr-2"></i> Lihat Detail
                </a>
                <a href="{{ route('admin.routes.index') }}" class="bg-white text-blue-700 hover:bg-blue-50 transition duration-200 py-2 px-4 rounded-lg shadow-sm flex items-center text-sm font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Route Summary -->
    <div class="p-4 route-summary mx-6 mt-6 flex items-center">
        <div class="flex-shrink-0 mr-4">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-route text-xl"></i>
            </div>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-blue-800">{{ $route->origin }} - {{ $route->destination }}</h2>
            <div class="flex items-center space-x-4 text-sm">
                <div class="flex items-center">
                    <i class="fas fa-clock text-blue-600 mr-1"></i>
                    <span>{{ $route->duration }} menit</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-tag text-blue-600 mr-1"></i>
                    <span>Rp {{ number_format($route->base_price, 0, ',', '.') }}</span>
                </div>
                @if ($route->status == 'ACTIVE')
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 flex items-center">
                        <i class="fas fa-check-circle mr-1"></i> Aktif
                    </span>
                @elseif($route->status == 'WEATHER_ISSUE')
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 flex items-center">
                        <i class="fas fa-cloud-rain mr-1"></i> Masalah Cuaca
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 flex items-center">
                        <i class="fas fa-ban mr-1"></i> Tidak Aktif
                    </span>
                @endif
            </div>
        </div>
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
            <div class="form-section bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i> Informasi Dasar
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="input-group">
                        <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">
                            Pelabuhan Asal
                            <span class="required-badge text-red-500 font-bold">*</span>
                        </label>
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

                    <div class="input-group">
                        <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">
                            Pelabuhan Tujuan
                            <span class="required-badge text-red-500 font-bold">*</span>
                        </label>
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

                    <div class="input-group">
                        <label for="distance" class="block text-sm font-medium text-gray-700 mb-1">
                            Jarak (KM)
                        </label>
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
                        <p class="text-xs text-gray-500 mt-1">Jarak dalam kilometer</p>
                    </div>

                    <div class="input-group">
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">
                            Durasi (Menit)
                            <span class="required-badge text-red-500 font-bold">*</span>
                        </label>
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

                    <div class="input-group">
                        <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">
                            Harga Dasar
                            <span class="required-badge text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="base_price" name="base_price" value="{{ old('base_price', $route->base_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('base_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm price-highlight">
                        </div>
                        @error('base_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Harga tiket dasar per orang</p>
                    </div>

                    <div class="input-group">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                            <span class="required-badge text-red-500 font-bold">*</span>
                            <div class="help-tip inline-block ml-1">
                                <i class="fas fa-question-circle text-blue-500 text-xs"></i>
                                <div class="tip-content text-xs">
                                    Perubahan status dapat mempengaruhi jadwal terkait. Jadwal akan disesuaikan otomatis dengan status rute.
                                </div>
                            </div>
                        </label>
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
            <div class="form-section bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-car mr-2 text-green-500"></i> Harga Tambahan untuk Kendaraan
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="input-group">
                        <label for="motorcycle_price" class="block text-sm font-medium text-gray-700 mb-1">
                            Harga Motor
                            <span class="required-badge text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="motorcycle_price" name="motorcycle_price" value="{{ old('motorcycle_price', $route->motorcycle_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('motorcycle_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm price-highlight"
                                data-original="{{ $route->motorcycle_price }}">
                        </div>
                        @error('motorcycle_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="car_price" class="block text-sm font-medium text-gray-700 mb-1">
                            Harga Mobil
                            <span class="required-badge text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="car_price" name="car_price" value="{{ old('car_price', $route->car_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('car_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm price-highlight"
                                data-original="{{ $route->car_price }}">
                        </div>
                        @error('car_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="bus_price" class="block text-sm font-medium text-gray-700 mb-1">
                            Harga Bus
                            <span class="required-badge text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="bus_price" name="bus_price" value="{{ old('bus_price', $route->bus_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('bus_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm price-highlight"
                                data-original="{{ $route->bus_price }}">
                        </div>
                        @error('bus_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="truck_price" class="block text-sm font-medium text-gray-700 mb-1">
                            Harga Truk
                            <span class="required-badge text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="number" id="truck_price" name="truck_price" value="{{ old('truck_price', $route->truck_price) }}" required min="0" step="1000"
                                class="pl-10 bg-white border @error('truck_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm price-highlight"
                                data-original="{{ $route->truck_price }}">
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

        // Price highlights for changed values
        const priceInputs = document.querySelectorAll('.price-highlight');
        priceInputs.forEach(input => {
            input.addEventListener('input', function() {
                const originalValue = this.getAttribute('data-original');
                if (this.value != originalValue) {
                    this.classList.add('changed');
                } else {
                    this.classList.remove('changed');
                }
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
        const formatCurrency = (value) => {
            return new Intl.NumberFormat('id-ID').format(value);
        };

        priceInputs.forEach(input => {
            const originalValue = input.getAttribute('data-original');
            if (originalValue) {
                const formattedValue = formatCurrency(originalValue);
                const label = input.closest('.input-group').querySelector('label');
                const priceInfo = document.createElement('span');
                priceInfo.className = 'ml-1 text-xs text-gray-500';
                priceInfo.textContent = `(Rp ${formattedValue})`;
                label.appendChild(priceInfo);
            }

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
