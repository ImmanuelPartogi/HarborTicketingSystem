@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <!-- Header Section with Gradient Background -->
    <div class="bg-gradient-to-r from-yellow-500 to-amber-600 px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0">
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-user-edit mr-3"></i>
                <span>Edit Pengguna</span>
            </h1>
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.show', $user->id) }}" class="bg-white text-amber-700 hover:bg-amber-50 transition duration-200 py-2 px-4 rounded-lg shadow-md flex items-center text-sm font-medium">
                    <i class="fas fa-eye mr-2"></i> Lihat Detail
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-white text-amber-700 hover:bg-amber-50 transition duration-200 py-2 px-4 rounded-lg shadow-md flex items-center text-sm font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
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

    <!-- User Summary Card -->
    <div class="px-6 pt-6">
        <div class="flex items-center p-4 bg-amber-50 rounded-lg border border-amber-200">
            <div class="h-12 w-12 rounded-full bg-amber-500 flex items-center justify-center text-white text-xl font-bold mr-4">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-medium text-gray-800">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i> Terdaftar {{ $user->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="p-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Main Form Grid -->
            <div class="space-y-6">
                <!-- Account Information Section -->
                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-shield mr-2 text-amber-600"></i>
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
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5"
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
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5"
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
                                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5"
                                    placeholder="0812-3456-7890">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password Baru
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" id="password" name="password"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5"
                                    placeholder="Biarkan kosong jika tidak ingin mengubah">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Biarkan kosong jika tidak ingin mengubah password</p>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Konfirmasi Password Baru
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5"
                                    placeholder="Konfirmasi password baru">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-address-card mr-2 text-amber-600"></i>
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
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5">
                                    <option value="">Pilih Jenis Identitas</option>
                                    <option value="KTP" {{ old('id_type', $user->id_type) == 'KTP' ? 'selected' : '' }}>KTP</option>
                                    <option value="SIM" {{ old('id_type', $user->id_type) == 'SIM' ? 'selected' : '' }}>SIM</option>
                                    <option value="PASPOR" {{ old('id_type', $user->id_type) == 'PASPOR' ? 'selected' : '' }}>PASPOR</option>
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
                                <input type="text" id="id_number" name="id_number" value="{{ old('id_number', $user->id_number) }}"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5"
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
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="MALE" {{ old('gender', $user->gender) == 'MALE' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="FEMALE" {{ old('gender', $user->gender) == 'FEMALE' ? 'selected' : '' }}>Perempuan</option>
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
                                <input type="date" id="dob" name="dob" value="{{ old('dob', $user->dob ? $user->dob->format('Y-m-d') : '') }}"
                                    class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5">
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
                                class="pl-10 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5"
                                placeholder="Masukkan alamat lengkap">{{ old('address', $user->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Account Status Section -->
                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt mr-2 text-amber-600"></i>
                        Status Akun
                    </h2>

                    <div class="flex items-start space-x-3 p-3 bg-white rounded-lg border border-gray-100">
                        <div class="flex items-center h-5">
                            <input type="checkbox" id="email_verified" name="email_verified"
                                class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 focus:ring-offset-0"
                                {{ $user->email_verified_at ? 'checked' : '' }}>
                        </div>
                        <div>
                            <label for="email_verified" class="font-medium text-gray-700">Email Terverifikasi</label>
                            <p class="text-xs text-gray-500 mt-1">
                                Jika dicentang, email pengguna akan ditandai sebagai terverifikasi.
                                {{ $user->email_verified_at ? 'Terverifikasi pada ' . $user->email_verified_at->format('d M Y H:i') : 'Belum terverifikasi' }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="flex justify-end mt-6 space-x-3">
                <a href="{{ route('admin.users.show', $user->id) }}"
                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition duration-200 flex items-center shadow-md">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
