@extends('admin.layouts.app')

@section('styles')
    <style>
        .input-group {
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            transform: translateY(-2px);
        }

        .custom-file-input::-webkit-file-upload-button {
            visibility: hidden;
        }

        .custom-file-input::before {
            content: 'Pilih File';
            display: inline-block;
            background: linear-gradient(to right, #f59e0b, #d97706);
            color: white;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            outline: none;
            white-space: nowrap;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .custom-file-input:hover::before {
            background: linear-gradient(to right, #d97706, #b45309);
        }

        .custom-file-input:active::before {
            background: linear-gradient(to right, #b45309, #92400e);
        }

        .form-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .ferry-form label {
            font-weight: 500;
        }

        .form-header {
            background: linear-gradient(to right, #f59e0b, #d97706);
        }

        .required-asterisk {
            color: #ef4444;
            font-weight: bold;
        }

        .thumbnail-preview {
            transition: all 0.3s ease;
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .thumbnail-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .thumbnail-preview::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0) 70%, rgba(0,0,0,0.2) 100%);
            pointer-events: none;
        }

        .section-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .section-card:hover {
            border-color: #fcd34d;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }

        .upload-zone {
            transition: all 0.3s ease;
        }

        .upload-zone:hover {
            background-color: #fffbeb;
        }

        .ferry-summary {
            background: linear-gradient(135deg, #fef3c7 0%, #fff7ed 100%);
            border-radius: 0.5rem;
            border: 1px solid #fcd34d;
        }
    </style>
@endsection

@section('content')
    <div class="bg-white shadow-lg rounded-lg overflow-hidden form-shadow">
        <!-- Header -->
        <div class="form-header p-6 text-white">
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-edit mr-3"></i>
                        Edit Kapal Ferry
                    </h1>
                    <p class="mt-1 text-amber-100">Perbarui informasi kapal "{{ $ferry->name }}"</p>
                </div>
                <a href="{{ route('admin.ferries.index') }}"
                    class="bg-white text-amber-600 hover:bg-amber-50 flex items-center py-2 px-4 rounded-lg transition duration-300 shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Ferry Summary -->
        <div class="p-6 ferry-summary m-6 flex items-center">
            <div class="flex-shrink-0 mr-4">
                @if($ferry->image)
                    <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-amber-300">
                        <img src="{{ asset('storage/' . $ferry->image) }}" alt="{{ $ferry->name }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center border-2 border-amber-300">
                        <i class="fas fa-ship text-amber-600 text-2xl"></i>
                    </div>
                @endif
            </div>
            <div>
                <h2 class="text-lg font-semibold text-amber-800">{{ $ferry->name }}</h2>
                <div class="flex items-center space-x-3 text-sm">
                    <div class="flex items-center">
                        <i class="fas fa-users text-amber-600 mr-1"></i>
                        <span>{{ number_format($ferry->capacity_passenger) }} Penumpang</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-car text-amber-600 mr-1"></i>
                        <span>{{ number_format($ferry->capacity_vehicle_car) }} Mobil</span>
                    </div>
                    @if ($ferry->status == 'ACTIVE')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 flex items-center">
                            <i class="fas fa-check-circle mr-1"></i> Aktif
                        </span>
                    @elseif($ferry->status == 'MAINTENANCE')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 flex items-center">
                            <i class="fas fa-tools mr-1"></i> Pemeliharaan
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 flex items-center">
                            <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-5 mx-6 mb-6 rounded-r-lg shadow-sm" role="alert">
                <div class="flex items-center mb-1">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2 text-lg"></i>
                    <h3 class="text-lg font-medium text-red-800">Terdapat kesalahan pada formulir</h3>
                </div>
                <ul class="list-disc pl-5 space-y-1 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.ferries.update', $ferry->id) }}" method="POST" enctype="multipart/form-data"
            class="p-6 ferry-form">
            @csrf
            @method('PUT')

            <div class="section-card bg-amber-50 p-5 rounded-lg mb-6">
                <h2 class="text-lg font-semibold text-amber-800 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i> Informasi Umum
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="input-group">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Kapal <span class="required-asterisk">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-tag text-gray-400"></i>
                            </div>
                            <input type="text" id="name" name="name" value="{{ old('name', $ferry->name) }}"
                                required
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full pl-10 p-2.5 shadow-sm transition duration-200 hover:border-amber-400">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            Status <span class="required-asterisk">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-toggle-on text-gray-400"></i>
                            </div>
                            <select id="status" name="status" required
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full pl-10 p-2.5 shadow-sm transition duration-200 hover:border-amber-400">
                                <option value="ACTIVE" {{ old('status', $ferry->status) == 'ACTIVE' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="MAINTENANCE"
                                    {{ old('status', $ferry->status) == 'MAINTENANCE' ? 'selected' : '' }}>Pemeliharaan
                                </option>
                                <option value="INACTIVE"
                                    {{ old('status', $ferry->status) == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                <i class="fas fa-align-left text-gray-400"></i>
                            </div>
                            <textarea id="description" name="description" rows="4"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full pl-10 p-2.5 shadow-sm transition duration-200 hover:border-amber-400"
                                placeholder="Masukkan deskripsi kapal ferry...">{{ old('description', $ferry->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card bg-green-50 p-5 rounded-lg mb-6">
                <h2 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                    <i class="fas fa-users mr-2"></i> Kapasitas
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="input-group">
                        <label for="capacity_passenger" class="block text-sm font-medium text-gray-700 mb-1">
                            Kapasitas Penumpang <span class="required-asterisk">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-users text-gray-400"></i>
                            </div>
                            <input type="number" id="capacity_passenger" name="capacity_passenger"
                                value="{{ old('capacity_passenger', $ferry->capacity_passenger) }}" required min="1"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full pl-10 p-2.5 shadow-sm transition duration-200 hover:border-green-400"
                                placeholder="Jumlah penumpang">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Jumlah maksimum penumpang</p>
                    </div>

                    <div class="input-group">
                        <label for="capacity_vehicle_motorcycle" class="block text-sm font-medium text-gray-700 mb-1">
                            Kapasitas Motor <span class="required-asterisk">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-motorcycle text-gray-400"></i>
                            </div>
                            <input type="number" id="capacity_vehicle_motorcycle" name="capacity_vehicle_motorcycle"
                                value="{{ old('capacity_vehicle_motorcycle', $ferry->capacity_vehicle_motorcycle) }}"
                                required min="0"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full pl-10 p-2.5 shadow-sm transition duration-200 hover:border-green-400"
                                placeholder="Jumlah motor">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="capacity_vehicle_car" class="block text-sm font-medium text-gray-700 mb-1">
                            Kapasitas Mobil <span class="required-asterisk">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-car text-gray-400"></i>
                            </div>
                            <input type="number" id="capacity_vehicle_car" name="capacity_vehicle_car"
                                value="{{ old('capacity_vehicle_car', $ferry->capacity_vehicle_car) }}" required
                                min="0"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full pl-10 p-2.5 shadow-sm transition duration-200 hover:border-green-400"
                                placeholder="Jumlah mobil">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="capacity_vehicle_bus" class="block text-sm font-medium text-gray-700 mb-1">
                            Kapasitas Bus <span class="required-asterisk">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-bus text-gray-400"></i>
                            </div>
                            <input type="number" id="capacity_vehicle_bus" name="capacity_vehicle_bus"
                                value="{{ old('capacity_vehicle_bus', $ferry->capacity_vehicle_bus) }}" required
                                min="0"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full pl-10 p-2.5 shadow-sm transition duration-200 hover:border-green-400"
                                placeholder="Jumlah bus">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="capacity_vehicle_truck" class="block text-sm font-medium text-gray-700 mb-1">
                            Kapasitas Truk <span class="required-asterisk">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-truck text-gray-400"></i>
                            </div>
                            <input type="number" id="capacity_vehicle_truck" name="capacity_vehicle_truck"
                                value="{{ old('capacity_vehicle_truck', $ferry->capacity_vehicle_truck) }}" required
                                min="0"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full pl-10 p-2.5 shadow-sm transition duration-200 hover:border-green-400"
                                placeholder="Jumlah truk">
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card bg-purple-50 p-5 rounded-lg mb-6">
                <h2 class="text-lg font-semibold text-purple-800 mb-3 flex items-center">
                    <i class="fas fa-image mr-2"></i> Gambar
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="input-group">
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Foto Kapal</label>
                        <div
                            class="upload-zone mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-purple-400 transition duration-200">
                            <div class="space-y-2 text-center">
                                @if (!$ferry->image)
                                    <div class="mx-auto h-24 w-24 text-purple-500 flex items-center justify-center">
                                        <i class="fas fa-ship text-6xl"></i>
                                    </div>
                                @endif
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="image"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none">
                                        <span>Upload gambar baru</span>
                                        <input id="image" name="image" type="file"
                                            class="custom-file-input sr-only" accept="image/png, image/jpeg, image/jpg">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">
                                    PNG, JPG, JPEG hingga 2MB
                                </p>
                                <div class="mt-1 text-sm text-gray-500" id="file-selected">
                                    {{ $ferry->image ? basename($ferry->image) : 'Tidak ada file yang dipilih' }}
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Biarkan kosong jika tidak ingin mengubah gambar</p>
                            </div>
                        </div>
                    </div>

                    @if ($ferry->image)
                        <div class="input-group flex flex-col items-center justify-center">
                            <label class="block text-sm font-medium text-gray-700 mb-3 self-start">Gambar Saat Ini</label>
                            <div class="thumbnail-preview overflow-hidden rounded-lg shadow-md w-full max-w-xs">
                                <img src="{{ asset('storage/' . $ferry->image) }}" alt="{{ $ferry->name }}"
                                    class="w-full h-auto object-cover">
                            </div>
                            <p class="mt-2 text-xs text-gray-500 self-start">Gambar yang ditampilkan saat ini</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8">
                <a href="{{ route('admin.ferries.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-6 rounded-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit"
                    class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300 flex items-center justify-center shadow-md">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

@section('scripts')
    <script>
        document.getElementById('image').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            document.getElementById('file-selected').textContent = fileName;
        });
    </script>
@endsection
@endsection
