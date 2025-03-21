@extends('admin.layouts.app')

@section('title', 'Hubungi Dukungan')
@section('header', 'Hubungi Dukungan')

@section('styles')
<style>
    .support-card {
        transition: all 0.3s ease;
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .support-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .help-banner {
        background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
    }
    .form-input {
        transition: all 0.2s ease;
    }
    .form-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }
    .contact-icon {
        transition: all 0.3s ease;
    }
    .contact-item:hover .contact-icon {
        transform: scale(1.1);
    }
    .back-link {
        transition: all 0.3s ease;
    }
    .back-link:hover {
        transform: translateX(-3px);
    }
    .file-upload-area {
        transition: all 0.3s ease;
    }
    .file-upload-area:hover {
        border-color: #6366f1;
        background-color: rgba(99, 102, 241, 0.05);
    }
    .section-header {
        position: relative;
    }
    .section-header::after {
        content: '';
        position: absolute;
        width: 40px;
        height: 3px;
        background: linear-gradient(to right, #4f46e5, #818cf8);
        left: 0;
        bottom: -8px;
        border-radius: 2px;
    }
    .send-button {
        transition: all 0.3s ease;
    }
    .send-button:hover {
        transform: translateY(-2px);
    }
    .info-card {
        border-left: 4px solid #3b82f6;
    }
    .quick-link {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
    }
    .quick-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Back Link -->
    <div class="mb-8">
        <a href="{{ route('admin.help') }}" class="back-link text-indigo-600 hover:text-indigo-800 inline-flex items-center font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Pusat Bantuan
        </a>
    </div>

    <div class="lg:flex lg:items-start lg:space-x-8">
        <!-- Contact Form -->
        <div class="lg:w-2/3 mb-8 lg:mb-0">
            <div class="support-card bg-white shadow-lg border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-xl font-bold text-gray-900 section-header">Kirim Permintaan Dukungan</h3>
                    <p class="mt-3 text-sm text-gray-600">Isi formulir di bawah ini untuk mendapatkan bantuan dari tim dukungan kami.</p>
                </div>

                <div class="p-6 lg:p-8">
                    <form action="{{ route('admin.help.send-support') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <!-- Subject -->
                            <div class="sm:col-span-6">
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subjek</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-heading text-gray-400"></i>
                                    </div>
                                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                                        class="form-input pl-10 py-3 shadow-sm block w-full text-base border-gray-300 rounded-lg focus:outline-none"
                                        placeholder="Masukkan subjek permintaan dukungan">
                                </div>
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div class="sm:col-span-3">
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-flag text-gray-400"></i>
                                    </div>
                                    <select id="priority" name="priority" required
                                        class="form-input pl-10 py-3 shadow-sm block w-full text-base border-gray-300 rounded-lg appearance-none focus:outline-none">
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Mendesak</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="sm:col-span-3">
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-folder text-gray-400"></i>
                                    </div>
                                    <select id="category" name="category" required
                                        class="form-input pl-10 py-3 shadow-sm block w-full text-base border-gray-300 rounded-lg appearance-none focus:outline-none">
                                        <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>Pertanyaan Umum</option>
                                        <option value="booking" {{ old('category') == 'booking' ? 'selected' : '' }}>Manajemen Pemesanan</option>
                                        <option value="ferry" {{ old('category') == 'ferry' ? 'selected' : '' }}>Manajemen Kapal</option>
                                        <option value="route" {{ old('category') == 'route' ? 'selected' : '' }}>Rute & Jadwal</option>
                                        <option value="report" {{ old('category') == 'report' ? 'selected' : '' }}>Laporan & Analitik</option>
                                        <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Masalah Teknis</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                @error('category')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div class="sm:col-span-6">
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                                <div class="relative">
                                    <textarea id="message" name="message" rows="6" required
                                        class="form-input py-3 shadow-sm block w-full text-base border-gray-300 rounded-lg focus:outline-none"
                                        placeholder="Jelaskan masalah Anda secara detail. Sertakan pesan kesalahan yang Anda lihat.">{{ old('message') }}</textarea>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 flex items-start">
                                    <i class="fas fa-info-circle text-indigo-400 mr-2 mt-0.5"></i>
                                    <span>Semakin detail pesan Anda, semakin cepat kami dapat membantu Anda.</span>
                                </p>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Attachment -->
                            <div class="sm:col-span-6">
                                <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">Lampiran (opsional)</label>
                                <div class="mt-1 file-upload-area flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                    <div class="space-y-2 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                                <span class="px-2 py-1 bg-indigo-50 rounded-md">Unggah file</span>
                                                <input id="file-upload" name="attachment" type="file" class="sr-only">
                                            </label>
                                            <p class="pl-2 flex items-center">atau seret dan lepas</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            PNG, JPG, PDF, DOC, DOCX sampai 10MB
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="mt-8 info-card bg-blue-50 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 pt-0.5">
                                    <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Waktu respon dukungan</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>Tim dukungan kami biasanya merespon dalam waktu 24 jam pada hari kerja. Untuk masalah mendesak, silakan pilih prioritas "Mendesak".</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-6 flex justify-end">
                            <button type="submit" class="send-button inline-flex justify-center py-3 px-6 border border-transparent shadow-md text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                <i class="fas fa-paper-plane mr-2"></i> Kirim Permintaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Support Info -->
        <div class="lg:w-1/3 space-y-6">
            <!-- Contact Information -->
            <div class="support-card bg-white shadow-lg border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-xl font-bold text-gray-900 section-header">Informasi Kontak</h3>
                </div>

                <div class="p-6">
                    <div class="space-y-5">
                        <!-- Email -->
                        <div class="flex items-start contact-item group p-2 rounded-lg transition-all hover:bg-gray-50">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center contact-icon">
                                    <i class="fas fa-envelope text-blue-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-base font-semibold text-gray-900">Email Dukungan</h4>
                                <p class="mt-1 text-base text-gray-600 font-medium">support@ferryticket.com</p>
                                <p class="mt-1 text-xs text-gray-500">Respon dalam waktu 24 jam</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-start contact-item group p-2 rounded-lg transition-all hover:bg-gray-50">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center contact-icon">
                                    <i class="fas fa-phone-alt text-green-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-base font-semibold text-gray-900">Dukungan Telepon</h4>
                                <p class="mt-1 text-base text-gray-600 font-medium">+62 812 3456 7890</p>
                                <p class="mt-1 text-xs text-gray-500">Senin-Jumat: 9:00-17:00 WIB</p>
                            </div>
                        </div>

                        <!-- Chat -->
                        <div class="flex items-start contact-item group p-2 rounded-lg transition-all hover:bg-gray-50">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center contact-icon">
                                    <i class="fas fa-comments text-yellow-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-base font-semibold text-gray-900">Live Chat</h4>
                                <p class="mt-1 text-base text-gray-600 font-medium">Tersedia pada hari kerja</p>
                                <p class="mt-1 text-xs text-gray-500">9:00-17:00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="support-card bg-gradient-to-br from-indigo-600 to-indigo-800 shadow-lg text-white">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-5 flex items-center">
                        <i class="fas fa-link mr-3 opacity-75"></i>
                        Tautan Bantuan Cepat
                    </h3>
                    <ul class="space-y-3 mt-6">
                        <li>
                            <a href="#" class="quick-link flex items-center text-white py-2 px-3 opacity-85 hover:opacity-100">
                                <i class="fas fa-book-open mr-3 w-5 text-center"></i>
                                <span>Basis Pengetahuan</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="quick-link flex items-center text-white py-2 px-3 opacity-85 hover:opacity-100">
                                <i class="fas fa-video mr-3 w-5 text-center"></i>
                                <span>Video Tutorial</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="quick-link flex items-center text-white py-2 px-3 opacity-85 hover:opacity-100">
                                <i class="fas fa-question-circle mr-3 w-5 text-center"></i>
                                <span>FAQ</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="quick-link flex items-center text-white py-2 px-3 opacity-85 hover:opacity-100">
                                <i class="fas fa-life-ring mr-3 w-5 text-center"></i>
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
