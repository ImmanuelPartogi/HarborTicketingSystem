@extends('admin.layouts.app')

@section('title', 'Pusat Bantuan')
@section('header', 'Pusat Bantuan')

@section('styles')
    <style>
        .faq-card {
            transition: all 0.3s ease;
        }

        .faq-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .guide-card {
            transition: all 0.3s ease;
        }

        .guide-card:hover {
            transform: translateY(-5px);
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .help-banner {
            background: linear-gradient(to right, #4f46e5, #818cf8);
        }

        .category-icon {
            transition: all 0.3s ease;
        }

        .category-card:hover .category-icon {
            transform: scale(1.1);
        }
    </style>
@endsection

@section('content')
    <div>
        <!-- Help Banner -->
        <div class="help-banner rounded-xl text-white p-6 mb-8">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-3xl font-bold mb-4">Apa yang bisa kami bantu hari ini?</h1>
                <p class="text-lg text-indigo-100 mb-6">Cari di pusat bantuan kami atau telusuri kategori di bawah</p>

                <div class="relative max-w-2xl mx-auto">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-indigo-300"></i>
                    </div>
                    <input type="text"
                        class="search-input w-full py-3 pl-10 pr-4 rounded-lg bg-white bg-opacity-10 border border-indigo-300 placeholder-indigo-200 text-white focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent"
                        placeholder="Cari bantuan...">
                    <button type="button"
                        class="absolute right-2 top-2 px-4 py-1 bg-white text-indigo-700 rounded-md hover:bg-indigo-50 transition">
                        Cari
                    </button>
                </div>
            </div>
        </div>

        <!-- Help Categories -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Telusuri berdasarkan Kategori</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="#"
                    class="category-card bg-white rounded-lg shadow p-6 text-center hover:shadow-lg transition">
                    <div
                        class="h-16 w-16 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="category-icon fas fa-ship text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Manajemen Kapal Ferry</h3>
                    <p class="text-gray-600">Mengelola kapal ferry, kapasitas, dan pemeliharaan</p>
                </a>

                <a href="#"
                    class="category-card bg-white rounded-lg shadow p-6 text-center hover:shadow-lg transition">
                    <div
                        class="h-16 w-16 mx-auto bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                        <i class="category-icon fas fa-route text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rute & Jadwal</h3>
                    <p class="text-gray-600">Menyiapkan rute, waktu, dan harga</p>
                </a>

                <a href="#"
                    class="category-card bg-white rounded-lg shadow p-6 text-center hover:shadow-lg transition">
                    <div
                        class="h-16 w-16 mx-auto bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mb-4">
                        <i class="category-icon fas fa-ticket-alt text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Manajemen Pemesanan</h3>
                    <p class="text-gray-600">Memproses dan mengelola pemesanan</p>
                </a>

                <a href="#"
                    class="category-card bg-white rounded-lg shadow p-6 text-center hover:shadow-lg transition">
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
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Panduan Cepat</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($guides as $guide)
                    <a href="{{ $guide['link'] }}"
                        class="guide-card block bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $guide['title'] }}</h3>
                            <p class="text-gray-600 mb-4">{{ $guide['description'] }}</p>
                            <div class="text-primary-600 font-medium flex items-center">
                                Baca panduan <i class="fas fa-arrow-right ml-2"></i>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Frequently Asked Questions - Alternative solution for older Laravel versions -->
        <div x-data="{ activeTab: null }" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Pertanyaan yang Sering Diajukan</h2>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                @foreach ($faqs as $index => $faq)
                    <div class="faq-card border-b border-gray-200 last:border-b-0">
                        <button
                            x-on:click="activeTab === {{ json_encode($index) }} ? activeTab = null : activeTab = {{ json_encode($index) }}"
                            class="w-full px-6 py-4 text-left focus:outline-none">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">{{ $faq['question'] }}</h3>
                                <span class="ml-6 flex-shrink-0">
                                    <i class="fas"
                                        x-bind:class="activeTab === {{ json_encode($index) }} ? 'fa-chevron-up text-primary-600' :
                                            'fa-chevron-down text-gray-400'"></i>
                                </span>
                            </div>
                        </button>

                        <div x-show="activeTab === {{ json_encode($index) }}" x-collapse>
                            <div class="px-6 pb-4 text-gray-600">
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Contact Support -->
        <div class="bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg p-6">
            <div class="md:flex md:items-center md:justify-between">
                <div class="md:flex-shrink-0 mb-4 md:mb-0">
                    <h2 class="text-xl font-bold text-gray-900">Masih membutuhkan bantuan?</h2>
                    <p class="text-gray-600 mt-1">Tim dukungan kami siap membantu Anda</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('admin.help.contact') }}"
                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-headset mr-2"></i> Hubungi Dukungan
                    </a>
                    <a href="#"
                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-book mr-2"></i> Lihat Dokumentasi
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
