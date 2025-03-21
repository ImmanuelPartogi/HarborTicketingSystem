@extends('admin.layouts.app')

@section('title', 'Hubungi Dukungan')
@section('header', 'Hubungi Dukungan')

@section('styles')
<style>
    .support-card {
        transition: all 0.3s ease;
    }
    .support-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .help-banner {
        background: linear-gradient(to right, #4f46e5, #818cf8);
    }
</style>
@endsection

@section('content')
<div>
    <div class="mb-6">
        <a href="{{ route('admin.help') }}" class="text-primary-600 hover:text-primary-900 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Pusat Bantuan
        </a>
    </div>

    <div class="lg:flex lg:items-start lg:space-x-8">
        <!-- Contact Form -->
        <div class="lg:w-2/3 mb-8 lg:mb-0">
            <div class="support-card bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Kirim Permintaan Dukungan</h3>
                    <p class="mt-1 text-sm text-gray-500">Isi formulir di bawah ini untuk mendapatkan bantuan dari tim dukungan kami.</p>
                </div>

                <div class="p-6">
                    <form action="{{ route('admin.help.send-support') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label for="subject" class="block text-sm font-medium text-gray-700">Subjek</label>
                                <div class="mt-1">
                                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="priority" class="block text-sm font-medium text-gray-700">Prioritas</label>
                                <div class="mt-1">
                                    <select id="priority" name="priority" required
                                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Mendesak</option>
                                    </select>
                                </div>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="category" class="block text-sm font-medium text-gray-700">Kategori</label>
                                <div class="mt-1">
                                    <select id="category" name="category" required
                                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>Pertanyaan Umum</option>
                                        <option value="booking" {{ old('category') == 'booking' ? 'selected' : '' }}>Manajemen Pemesanan</option>
                                        <option value="ferry" {{ old('category') == 'ferry' ? 'selected' : '' }}>Manajemen Kapal</option>
                                        <option value="route" {{ old('category') == 'route' ? 'selected' : '' }}>Rute & Jadwal</option>
                                        <option value="report" {{ old('category') == 'report' ? 'selected' : '' }}>Laporan & Analitik</option>
                                        <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Masalah Teknis</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>
                                @error('category')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-6">
                                <label for="message" class="block text-sm font-medium text-gray-700">Pesan</label>
                                <div class="mt-1">
                                    <textarea id="message" name="message" rows="6" required
                                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('message') }}</textarea>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Silakan jelaskan masalah Anda secara detail. Sertakan pesan kesalahan yang Anda lihat.</p>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-6">
                                <label for="attachment" class="block text-sm font-medium text-gray-700">Lampiran (opsional)</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                                <span>Unggah file</span>
                                                <input id="file-upload" name="attachment" type="file" class="sr-only">
                                            </label>
                                            <p class="pl-1">atau seret dan lepas</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            PNG, JPG, PDF, DOC, DOCX sampai 10MB
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 bg-gray-50 border rounded-md p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Waktu respon dukungan</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>Tim dukungan kami biasanya merespon dalam waktu 24 jam pada hari kerja. Untuk masalah mendesak, silakan pilih prioritas "Mendesak".</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 text-right">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-paper-plane mr-2"></i> Kirim Permintaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Support Info -->
        <div class="lg:w-1/3">
            <div class="support-card bg-white shadow rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Informasi Kontak</h3>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-envelope text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-900">Email Dukungan</h4>
                                <p class="mt-1 text-sm text-gray-600">support@ferryticket.com</p>
                                <p class="mt-1 text-xs text-gray-500">Respon dalam waktu 24 jam</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-phone-alt text-green-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-900">Dukungan Telepon</h4>
                                <p class="mt-1 text-sm text-gray-600">+62 812 3456 7890</p>
                                <p class="mt-1 text-xs text-gray-500">Senin-Jumat: 9:00-17:00 WIB</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <i class="fas fa-comments text-yellow-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-900">Live Chat</h4>
                                <p class="mt-1 text-sm text-gray-600">Tersedia pada hari kerja</p>
                                <p class="mt-1 text-xs text-gray-500">9:00-17:00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="support-card bg-gradient-to-br from-primary-500 to-primary-700 shadow rounded-lg overflow-hidden text-white">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-3">Tautan Bantuan Cepat</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="flex items-center text-primary-100 hover:text-white transition">
                                <i class="fas fa-book-open mr-3"></i>
                                <span>Basis Pengetahuan</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-primary-100 hover:text-white transition">
                                <i class="fas fa-video mr-3"></i>
                                <span>Video Tutorial</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-primary-100 hover:text-white transition">
                                <i class="fas fa-question-circle mr-3"></i>
                                <span>FAQ</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-primary-100 hover:text-white transition">
                                <i class="fas fa-life-ring mr-3"></i>
                                <span>Status Sistem</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
