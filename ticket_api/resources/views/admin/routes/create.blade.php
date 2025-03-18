@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Tambah Rute Baru</h1>
    </div>

    @if (session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.routes.store') }}" method="POST" novalidate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">Pelabuhan Asal <span class="text-red-500">*</span></label>
                <input type="text" id="origin" name="origin" value="{{ old('origin') }}" required
                    class="bg-gray-50 border @error('origin') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    aria-describedby="origin-error">
                @error('origin')
                    <p id="origin-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Pelabuhan Tujuan <span class="text-red-500">*</span></label>
                <input type="text" id="destination" name="destination" value="{{ old('destination') }}" required
                    class="bg-gray-50 border @error('destination') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    aria-describedby="destination-error">
                @error('destination')
                    <p id="destination-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="distance" class="block text-sm font-medium text-gray-700 mb-1">Jarak (KM)</label>
                <input type="number" id="distance" name="distance" value="{{ old('distance') }}" step="0.01" min="0"
                    class="bg-gray-50 border @error('distance') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    aria-describedby="distance-error">
                @error('distance')
                    <p id="distance-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Durasi (Menit) <span class="text-red-500">*</span></label>
                <input type="number" id="duration" name="duration" value="{{ old('duration') }}" required min="1"
                    class="bg-gray-50 border @error('duration') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    aria-describedby="duration-error">
                @error('duration')
                    <p id="duration-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Dasar <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                    <input type="number" id="base_price" name="base_price" value="{{ old('base_price') }}" required min="0" step="1000"
                        class="pl-10 bg-gray-50 border @error('base_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        aria-describedby="base_price-error">
                </div>
                @error('base_price')
                    <p id="base_price-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" required
                    class="bg-gray-50 border @error('status') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    aria-describedby="status-error">
                    <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="INACTIVE" {{ old('status') == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                    <option value="WEATHER_ISSUE" {{ old('status') == 'WEATHER_ISSUE' ? 'selected' : '' }}>Masalah Cuaca</option>
                </select>
                @error('status')
                    <p id="status-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <h3 class="text-lg font-semibold mt-6 mb-3">Harga Tambahan untuk Kendaraan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="motorcycle_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Motor <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                    <input type="number" id="motorcycle_price" name="motorcycle_price" value="{{ old('motorcycle_price') }}" required min="0" step="1000"
                        class="pl-10 bg-gray-50 border @error('motorcycle_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
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
                        class="pl-10 bg-gray-50 border @error('car_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
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
                        class="pl-10 bg-gray-50 border @error('bus_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
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
                        class="pl-10 bg-gray-50 border @error('truck_price') border-red-300 @else border-gray-300 @enderror text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        aria-describedby="truck_price-error">
                </div>
                @error('truck_price')
                    <p id="truck_price-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('admin.routes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50">
                <i class="fas fa-times mr-1"></i> Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
        </div>
    </form>
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
                    const errorElement = document.getElementById(`${name}-error`);

                    if (!errorElement) {
                        const errorMsg = document.createElement('p');
                        errorMsg.id = `${name}-error`;
                        errorMsg.className = 'mt-1 text-sm text-red-600';
                        errorMsg.textContent = 'Bidang ini harus diisi';
                        input.parentNode.appendChild(errorMsg);
                    }

                    input.classList.add('border-red-300');
                    hasError = true;
                }
            });

            if (hasError) {
                event.preventDefault();
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
    });
</script>
@endsection
