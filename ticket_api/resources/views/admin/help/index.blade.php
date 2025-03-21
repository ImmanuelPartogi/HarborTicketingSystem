@extends('admin.layouts.app')

@section('title', 'Pusat Bantuan')
@section('header', 'Pusat Bantuan')

@section('styles')
    <style>
        .faq-card {
            transition: all 0.3s ease;
            border-left: 0px solid #4f46e5;
        }

        .faq-card:hover,
        .faq-card.active {
            border-left: 4px solid #4f46e5;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .guide-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
            overflow: hidden;
        }

        .guide-card:hover {
            transform: translateY(-5px);
            border-color: #e0e7ff;
            box-shadow: 0 15px 30px -10px rgba(79, 70, 229, 0.15);
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .help-banner {
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.3);
        }

        .category-icon {
            transition: all 0.4s ease;
        }

        .category-card:hover .category-icon {
            transform: scale(1.15);
            color: #4f46e5;
        }

        .category-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
            overflow: hidden;
        }

        .category-card:hover {
            transform: translateY(-5px);
            border-color: #e0e7ff;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.1), 0 10px 10px -5px rgba(79, 70, 229, 0.06);
        }

        .section-title {
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, #4f46e5, #818cf8);
            left: 0;
            bottom: 0;
            border-radius: 2px;
        }

        .support-btn {
            transition: all 0.3s ease;
        }

        .support-btn:hover {
            transform: translateY(-2px);
        }

        .search-btn {
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: scale(1.05);
        }
    </style>
@endsection

@section('content')
    <div>
        <!-- Help Banner -->
        <div class="help-banner rounded-xl text-white p-6 sm:p-10 lg:p-12 mb-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-4 tracking-tight">Apa yang bisa kami bantu hari ini?
                </h1>
                <p class="text-lg text-indigo-100 mb-8 opacity-90">Cari di pusat bantuan kami atau telusuri pertanyaan di bawah
                </p>
            </div>
        </div>

        <!-- Frequently Asked Questions - Fixed version -->
        <div x-data="{ activeTab: null }" class="mb-14">
            <h2 class="section-title text-xl sm:text-2xl font-bold text-gray-900 mb-8">
                <span class="flex items-center mb-2">
                    <span
                        class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-question"></i>
                    </span>
                    Pertanyaan yang Sering Diajukan
                </span>
            </h2>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                @foreach ($faqs as $index => $faq)
                    <div class="faq-card border-b border-gray-100 last:border-b-0"
                        x-bind:class="{ 'active': activeTab === {{ $index }} }">
                        <button
                            x-on:click="activeTab === {{ $index }} ? activeTab = null : activeTab = {{ $index }}"
                            class="w-full px-6 py-5 text-left focus:outline-none">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">{{ $faq['question'] }}</h3>
                                <span class="ml-6 flex-shrink-0 transition-transform duration-300"
                                    x-bind:class="{ 'rotate-180 text-indigo-600': activeTab ===
                                        {{ $index }}, 'text-gray-400': activeTab !== {{ $index }} }">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            </div>
                        </button>

                        <div x-show="activeTab === {{ $index }}"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform -translate-y-4"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-4" class="px-6 pb-5">
                            <div class="pl-6 pt-2 text-gray-600 border-l-2 border-indigo-200 text-base">
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- <!-- Contact Support -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-8 shadow-md mb-6">
            <div class="md:flex md:items-center md:justify-between">
                <div class="md:flex-shrink-0 mb-6 md:mb-0">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-headset text-indigo-500 mr-3"></i>
                        Masih membutuhkan bantuan?
                    </h2>
                    <p class="text-gray-600 mt-2">Tim dukungan kami siap membantu Anda</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('admin.help.contact') }}"
                        class="support-btn inline-flex justify-center items-center px-6 py-3 shadow-md text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg font-medium text-sm">
                        <i class="fas fa-headset mr-2"></i> Hubungi Dukungan
                    </a>
                </div>
            </div>
        </div> --}}
    </div>
@endsection
