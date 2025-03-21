@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <!-- Header Section with Gradient Background -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0">
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-user-plus mr-3"></i>
                <span>Tambah Pengguna Baru</span>
            </h1>
            <a href="{{ route('admin.users.index') }}" class="bg-white text-blue-700 hover:bg-blue-50 transition duration-200 py-2 px-4 rounded-lg shadow-md flex items-center text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
    <div class="m-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md flex items-start">
        <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-1"></i>
        <div>
            <p class="font-medium mb-1">Terdapat kesalahan pada form:</p>
            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Form Section -->
    <div class="p-6">
        <div class="bg-blue-50 rounded-lg p-4 mb-6 flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <p class="text-sm text-blue-700">
                Lengkapi informasi untuk membuat akun pengguna baru. Field yang ditandai dengan <span class="text-red-500">*</span> wajib diisi.
            </p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <!-- Main Form Grid -->
            <div class="space-y-6">
                <!-- Account Information Section -->
                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-shield mr-2 text-blue-600"></i>
                        Informasi Akun
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Masukkan nama lengkap">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="nama@contoh.com">
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="0812-3456-7890">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" id="password" name="password" required
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Masukkan password">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter, gunakan kombinasi huruf dan angka</p>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Konfirmasi password">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-address-card mr-2 text-blue-600"></i>
                        Informasi Pribadi
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="id_type" class="block text-sm font-medium text-gray-700 mb-1">
                                Jenis Identitas
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                                <select id="id_type" name="id_type"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Pilih Jenis Identitas</option>
                                    <option value="KTP" {{ old('id_type') == 'KTP' ? 'selected' : '' }}>KTP</option>
                                    <option value="SIM" {{ old('id_type') == 'SIM' ? 'selected' : '' }}>SIM</option>
                                    <option value="PASPOR" {{ old('id_type') == 'PASPOR' ? 'selected' : '' }}>PASPOR</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="id_number" class="block text-sm font-medium text-gray-700 mb-1">
                                Nomor Identitas
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-fingerprint text-gray-400"></i>
                                </div>
                                <input type="text" id="id_number" name="id_number" value="{{ old('id_number') }}"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Masukkan nomor identitas">
                            </div>
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                                Jenis Kelamin
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-venus-mars text-gray-400"></i>
                                </div>
                                <select id="gender" name="gender"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="MALE" {{ old('gender') == 'MALE' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="FEMALE" {{ old('gender') == 'FEMALE' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="dob" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Lahir
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                                <input type="date" id="dob" name="dob" value="{{ old('dob') }}"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                            Alamat
                        </label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                <i class="fas fa-home text-gray-400"></i>
                            </div>
                            <textarea id="address" name="address" rows="3"
                                class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="flex justify-end mt-6 space-x-3">
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-200 flex items-center shadow-md">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
