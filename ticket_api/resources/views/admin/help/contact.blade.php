@extends('admin.layouts.app')

@section('title', 'Pusat Bantuan')
@section('header', 'Pusat Bantuan')

@section('styles')
<style>
    .category-card {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .category-card:hover {
        transform: translateY(-5px);
        border-color: #e0e7ff;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.1), 0 4px 6px -2px rgba(79, 70, 229, 0.05);
    }
    .guide-card {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .guide-card:hover {
        transform: translateY(-3px);
        border-color: #e0e7ff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .search-input:focus {
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }
    .header-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
    }
    .category-icon {
        transition: all 0.3s ease;
    }
    .category-card:hover .category-icon {
        transform: scale(1.1);
    }
    .question-button {
        transition: all 0.2s ease;
    }
    .question-button:hover {
        background-color: #f5f5f9;
    }
    .faq-answer {
        transition: all 0.3s ease;
    }
    .contact-card {
        transition: all 0.3s ease;
        border-radius: 0.75rem;
        border: 1px solid transparent;
    }
    .contact-card:hover {
        border-color: #e0e7ff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
</style>
@endsection

@section('content')
<div>
    <!-- Help Banner -->
    <div class="header-gradient rounded-xl text-white p-6 sm:p-8 md:p-10 mb-8 shadow-lg">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-2xl sm:text-3xl font-bold mb-4">Apa yang bisa kami bantu hari ini?</h1>
            <p class="text-base sm:text-lg text-indigo-100 mb-6">Cari di pusat bantuan kami atau telusuri kategori di bawah</p>

            <div class="relative max-w-2xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-indigo-300"></i>
                </div>
                <input type="text"
                    class="search-input w-full py-3 pl-10 pr-16 rounded-lg bg-white bg-opacity-10 border border-indigo-300 placeholder-indigo-200 text-white focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent"
                    placeholder="Cari bantuan...">
                <button type="button"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-1.5 bg-white text-indigo-700 rounded-md hover:bg-indigo-50 transition shadow-sm">
                    Cari
                </button>
            </div>
        </div>
    </div>

    <!-- Help Categories -->
    <div class="mb-12">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-th-large"></i>
            </span>
            Telusuri berdasarkan Kategori
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="#"
                class="category-card bg-white rounded-lg shadow p-5 text-center hover:shadow-lg transition">
                <div
                    class="h-16 w-16 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                    <i class="category-icon fas fa-ship text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Manajemen Kapal Ferry</h3>
                <p class="text-gray-600">Mengelola kapal ferry, kapasitas, dan pemeliharaan</p>
            </a>

            <a href="#"
                class="category-card bg-white rounded-lg shadow p-5 text-center hover:shadow-lg transition">
                <div
                    class="h-16 w-16 mx-auto bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                    <i class="category-icon fas fa-route text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Rute & Jadwal</h3>
                <p class="text-gray-600">Menyiapkan rute, waktu, dan harga</p>
            </a>

            <a href="#"
                class="category-card bg-white rounded-lg shadow p-5 text-center hover:shadow-lg transition">
                <div
                    class="h-16 w-16 mx-auto bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mb-4">
                    <i class="category-icon fas fa-ticket-alt text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Manajemen Pemesanan</h3>
                <p class="text-gray-600">Memproses dan mengelola pemesanan</p>
            </a>

            <a href="#"
                class="category-card bg-white rounded-lg shadow p-5 text-center hover:shadow-lg transition">
                <div
                    class="h-16 w-16 mx-auto bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mb-4">
                    <i class="category-icon fas fa-chart-bar text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Laporan & Analitik</h3>
                <p class="text-gray-600">Memahami data dan menghasilkan laporan</p>
            </a>
        </div>
    </div>

    <!-- Quick Guides -->
    <div class="mb-12">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-book"></i>
            </span>
            Panduan Cepat
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($guides as $guide)
                <a href="{{ $guide['link'] }}"
                    class="guide-card block bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                    <div class="p-5 border-l-4 border-indigo-500">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-start">
                            <i class="fas fa-file-alt text-indigo-500 mt-1 mr-3"></i>
                            <span>{{ $guide['title'] }}</span>
                        </h3>
                        <p class="text-gray-600 mb-4">{{ $guide['description'] }}</p>
                        <div class="text-indigo-600 font-medium flex items-center">
                            Baca panduan <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Frequently Asked Questions -->
    <div x-data="{ activeTab: null }" class="mb-12">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-question"></i>
            </span>
            Pertanyaan yang Sering Diajukan
        </h2>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            @foreach ($faqs as $index => $faq)
                <div class="border-b border-gray-200 last:border-b-0">
                    <button
                        x-on:click="activeTab === {{ json_encode($index) }} ? activeTab = null : activeTab = {{ json_encode($index) }}"
                        class="question-button w-full px-6 py-4 text-left focus:outline-none transition-colors">
                        <div class="flex justify-between items-center">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-question-circle text-indigo-500 mr-3"></i>
                                {{ $faq['question'] }}
                            </h3>
                            <span class="ml-6 flex-shrink-0">
                                <i class="fas"
                                    x-bind:class="activeTab === {{ json_encode($index) }} ? 'fa-chevron-up text-indigo-600' :
                                        'fa-chevron-down text-gray-400'"></i>
                            </span>
                        </div>
                    </button>

                    <div x-show="activeTab === {{ json_encode($index) }}"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="faq-answer px-6 pb-4">
                        <div class="pl-9 text-gray-600 border-l-2 border-indigo-100">
                            {{ $faq['answer'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Contact Support -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6 shadow-sm">
        <div class="md:flex md:items-center md:justify-between">
            <div class="md:flex-shrink-0 mb-4 md:mb-0">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-headset text-indigo-500 mr-3"></i>
                    Masih membutuhkan bantuan?
                </h2>
                <p class="text-gray-600 mt-1">Tim dukungan kami siap membantu Anda</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('admin.help.contact') }}"
                    class="contact-card inline-flex justify-center items-center px-4 py-2 text-sm font-medium shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
                    <i class="fas fa-headset mr-2"></i> Hubungi Dukungan
                </a>
                <a href="#"
                    class="contact-card inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium shadow-sm text-gray-700 bg-white hover:bg-gray-50 transition">
                    <i class="fas fa-book mr-2"></i> Lihat Dokumentasi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
