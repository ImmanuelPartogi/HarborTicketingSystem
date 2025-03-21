<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ferry Ticket System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            'sans': ['Nunito', 'sans-serif'],
                        },
                        colors: {
                            'primary': {
                                50: '#f0f9ff',
                                100: '#e0f2fe',
                                200: '#bae6fd',
                                300: '#7dd3fc',
                                400: '#38bdf8',
                                500: '#0ea5e9',
                                600: '#0284c7',
                                700: '#0369a1',
                                800: '#075985',
                                900: '#0c4a6e',
                            },
                            'secondary': {
                                50: '#ecfdf5',
                                100: '#d1fae5',
                                200: '#a7f3d0',
                                300: '#6ee7b7',
                                400: '#34d399',
                                500: '#10b981',
                                600: '#059669',
                                700: '#047857',
                                800: '#065f46',
                                900: '#064e3b',
                            }
                        }
                    }
                }
            }
        </script>
        <style>
            /* Base Styles */
            body {
                font-family: 'Nunito', sans-serif;
            }

            /* Smooth Scrolling */
            html {
                scroll-behavior: smooth;
            }

            /* Wave Animation */
            .wave-animation {
                animation: wave 8s ease-in-out infinite;
            }
            @keyframes wave {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-15px);
                }
            }

            /* Boat Animation */
            .boat-animation {
                animation: boat 6s ease-in-out infinite;
            }
            @keyframes boat {
                0%, 100% {
                    transform: translateY(0) rotate(-2deg);
                }
                50% {
                    transform: translateY(-10px) rotate(2deg);
                }
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 10px;
            }
            ::-webkit-scrollbar-track {
                background: #f1f5f9;
            }
            ::-webkit-scrollbar-thumb {
                background: #0ea5e9;
                border-radius: 5px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #0284c7;
            }
        </style>
    @endif
</head>
<body class="antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md fixed w-full z-50 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-10 w-auto" src="{{ asset('images/logo.png') }}" alt="Ferry Ticket Logo">
                        <span class="ml-2 text-xl font-bold text-primary-600">FerryTicket</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="#home" class="border-primary-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Home
                        </a>
                        <a href="#routes" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Routes
                        </a>
                        <a href="#howto" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            How to Book
                        </a>
                        <a href="#about" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            About Us
                        </a>
                        <a href="#contact" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Contact
                        </a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    @if (Route::has('login'))
                        <div class="space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-primary-600 text-sm font-medium rounded-md text-primary-600 bg-white hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
                <div class="-mr-2 flex items-center sm:hidden">
                    <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu, show/hide based on menu state. -->
        <div class="sm:hidden hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="#home" class="bg-primary-50 border-primary-500 text-primary-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Home
                </a>
                <a href="#routes" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Routes
                </a>
                <a href="#howto" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    How to Book
                </a>
                <a href="#about" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    About Us
                </a>
                <a href="#contact" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Contact
                </a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                @if (Route::has('login'))
                    <div class="mt-3 space-y-1 px-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="block px-4 py-2 text-base font-medium text-primary-600 hover:text-primary-800 hover:bg-gray-100">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-base font-medium text-primary-600 hover:text-primary-800 hover:bg-gray-100">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="block px-4 py-2 text-base font-medium text-primary-600 hover:text-primary-800 hover:bg-gray-100">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="relative pt-16 pb-32 flex content-center items-center justify-center" style="min-height: 100vh;">
        <div class="absolute top-0 w-full h-full bg-center bg-cover" style="background-image: url('https://images.unsplash.com/photo-1523292562811-8fa7962a78c8?q=80&w=2070');">
            <span id="blackOverlay" class="w-full h-full absolute opacity-50 bg-black"></span>
        </div>

        <div class="container relative mx-auto">
            <div class="items-center flex flex-wrap">
                <div class="w-full lg:w-6/12 px-4 ml-auto mr-auto text-center">
                    <div class="mt-12">
                        <h1 class="text-white font-semibold text-5xl mb-6 leading-tight">
                            Explore the Sea with Our Ferry Service
                        </h1>
                        <p class="mt-4 text-lg text-gray-300 mb-8">
                            Book your ferry tickets online for a seamless travel experience.
                            Safe, convenient, and affordable sea transportation to your destination.
                        </p>
                        <a href="#routes" class="bg-primary-600 text-white font-bold px-6 py-3 rounded-lg inline-block transition-all duration-300 hover:bg-primary-700 hover:shadow-lg mr-4">
                            Check Available Routes
                        </a>
                        <a href="#howto" class="bg-transparent border-2 border-white text-white font-bold px-6 py-3 rounded-lg inline-block transition-all duration-300 hover:bg-white hover:text-primary-600">
                            Learn How to Book
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0">
            <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
                <defs>
                    <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
                </defs>
                <g class="parallax">
                    <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(255,255,255,0.7)" />
                    <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(255,255,255,0.5)" />
                    <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(255,255,255,0.3)" />
                    <use xlink:href="#gentle-wave" x="48" y="7" fill="#fff" />
                </g>
            </svg>
        </div>
    </section>

    <!-- Quick Search Box -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-xl p-8 -mt-32 relative z-10 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Find Your Ferry Route</h2>
                <form class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">From</label>
                        <select id="origin" name="origin" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="" selected disabled>Select origin</option>
                            <option value="merak">Merak</option>
                            <option value="bakauheni">Bakauheni</option>
                            <option value="ketapang">Ketapang</option>
                            <option value="gilimanuk">Gilimanuk</option>
                        </select>
                    </div>
                    <div>
                        <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                        <select id="destination" name="destination" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="" selected disabled>Select destination</option>
                            <option value="merak">Merak</option>
                            <option value="bakauheni">Bakauheni</option>
                            <option value="ketapang">Ketapang</option>
                            <option value="gilimanuk">Gilimanuk</option>
                        </select>
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" id="date" name="date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-primary-600 py-3 px-4 rounded-md text-white font-medium hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Popular Routes -->
    <section id="routes" class="py-12 bg-primary-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">Popular Routes</h2>
                <p class="mt-4 text-lg text-gray-600">Explore our most frequently traveled sea routes</p>
            </div>

            <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Route Card 1 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl">
                    <img class="h-48 w-full object-cover" src="https://images.unsplash.com/photo-1597466599360-3b9775841aec?q=80&w=1978" alt="Merak - Bakauheni">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Merak - Bakauheni</h3>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Popular</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-clock text-gray-500 mr-2"></i>
                            <span class="text-gray-600">Duration: ~2 hours</span>
                        </div>
                        <div class="flex items-center mb-4">
                            <i class="fas fa-ship text-gray-500 mr-2"></i>
                            <span class="text-gray-600">Multiple ferries daily</span>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div>
                                <span class="text-gray-500 text-sm">Starting from</span>
                                <p class="text-lg font-bold text-primary-600">Rp 60.000</p>
                            </div>
                            <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Route Card 2 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl">
                    <img class="h-48 w-full object-cover" src="https://images.unsplash.com/photo-1558459654-c3183f992b97?q=80&w=1974" alt="Ketapang - Gilimanuk">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Ketapang - Gilimanuk</h3>
                            <span class="bg-primary-100 text-primary-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Fast</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-clock text-gray-500 mr-2"></i>
                            <span class="text-gray-600">Duration: ~45 minutes</span>
                        </div>
                        <div class="flex items-center mb-4">
                            <i class="fas fa-ship text-gray-500 mr-2"></i>
                            <span class="text-gray-600">Hourly departures</span>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div>
                                <span class="text-gray-500 text-sm">Starting from</span>
                                <p class="text-lg font-bold text-primary-600">Rp 45.000</p>
                            </div>
                            <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Route Card 3 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl">
                    <img class="h-48 w-full object-cover" src="https://images.unsplash.com/photo-1519010470956-6d877008eaa4?q=80&w=2070" alt="Padang Bai - Lembar">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Padang Bai - Lembar</h3>
                            <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Scenic</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-clock text-gray-500 mr-2"></i>
                            <span class="text-gray-600">Duration: ~4 hours</span>
                        </div>
                        <div class="flex items-center mb-4">
                            <i class="fas fa-ship text-gray-500 mr-2"></i>
                            <span class="text-gray-600">3 departures daily</span>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div>
                                <span class="text-gray-500 text-sm">Starting from</span>
                                <p class="text-lg font-bold text-primary-600">Rp 75.000</p>
                            </div>
                            <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="#" class="inline-flex items-center px-6 py-3 border border-primary-600 text-base font-medium rounded-md text-primary-600 bg-white hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    View All Routes
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">Why Choose Our Ferry Service</h2>
                <p class="mt-4 text-lg text-gray-600">Experience the best sea travel with these benefits</p>
            </div>

            <div class="mt-16 grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                <!-- Feature 1 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-anchor text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Reliable Service</h3>
                    <p class="text-gray-600">Punctual departures and arrivals with a focus on passenger satisfaction</p>
                </div>

                <!-- Feature 2 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Safety First</h3>
                    <p class="text-gray-600">We prioritize safety with well-maintained vessels and trained staff</p>
                </div>

                <!-- Feature 3 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-ticket-alt text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Easy Booking</h3>
                    <p class="text-gray-600">Simple online booking system for tickets with instant confirmation</p>
                </div>

                <!-- Feature 4 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-wallet text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Affordable Rates</h3>
                    <p class="text-gray-600">Competitive pricing with special discounts for regular travelers</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How to Book -->
    <section id="howto" class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">How to Book Your Ferry Ticket</h2>
                <p class="mt-4 text-lg text-gray-600">Follow these simple steps to book your journey</p>
            </div>

            <div class="mt-16 relative">
                <!-- Line Connector -->
                <div class="hidden lg:block absolute top-1/2 transform -translate-y-1/2 left-0 right-0 h-0.5 bg-gray-200"></div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Step 1 -->
                    <div class="relative bg-white p-6 rounded-lg shadow-md z-10">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center h-10 w-10 rounded-full bg-primary-600 text-white font-bold">
                            1
                        </div>
                        <div class="text-center pt-6">
                            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                                <i class="fas fa-search text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Search Routes</h3>
                            <p class="text-gray-600">Enter your origin, destination, and travel date to find available ferries.</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative bg-white p-6 rounded-lg shadow-md z-10">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center h-10 w-10 rounded-full bg-primary-600 text-white font-bold">
                            2
                        </div>
                        <div class="text-center pt-6">
                            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                                <i class="fas fa-calendar-alt text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Select Schedule</h3>
                            <p class="text-gray-600">Choose from available schedules and ferry types that suit your needs.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative bg-white p-6 rounded-lg shadow-md z-10">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center h-10 w-10 rounded-full bg-primary-600 text-white font-bold">
                            3
                        </div>
                        <div class="text-center pt-6">
                            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                                <i class="fas fa-credit-card text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Make Payment</h3>
                            <p class="text-gray-600">Secure payment via multiple options including credit card and mobile banking.</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="relative bg-white p-6 rounded-lg shadow-md z-10">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center h-10 w-10 rounded-full bg-primary-600 text-white font-bold">
                            4
                        </div>
                        <div class="text-center pt-6">
                            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                                <i class="fas fa-qrcode text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Get E-Ticket</h3>
                            <p class="text-gray-600">Receive your e-ticket instantly via email or download from your account.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 text-center">
                <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Create an Account to Start Booking
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">What Our Customers Say</h2>
                <p class="mt-4 text-lg text-gray-600">Read testimonials from passengers who have traveled with us</p>
            </div>

            <div class="mt-16 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Testimonial 1 -->
                <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-gray-600">5.0</span>
                    </div>
                    <p class="text-gray-700 mb-6">"The online booking process was incredibly easy. I received my e-ticket instantly and the ferry was clean and comfortable. Will definitely use this service again!"</p>
                    <div class="flex items-center">
                        <img class="h-10 w-10 rounded-full object-cover" src="https://randomuser.me/api/portraits/women/17.jpg" alt="Customer">
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Sarah Johnson</h4>
                            <p class="text-sm text-gray-500">Traveled from Merak to Bakauheni</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="ml-2 text-gray-600">4.5</span>
                    </div>
                    <p class="text-gray-700 mb-6">"Great service and punctual departures. The staff was very helpful with my questions about vehicle transport. Highly recommend for family trips across the islands."</p>
                    <div class="flex items-center">
                        <img class="h-10 w-10 rounded-full object-cover" src="https://randomuser.me/api/portraits/men/32.jpg" alt="Customer">
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Budi Santoso</h4>
                            <p class="text-sm text-gray-500">Traveled from Ketapang to Gilimanuk</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 flex">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-gray-600">5.0</span>
                    </div>
                    <p class="text-gray-700 mb-6">"I was impressed by the safety standards on board. The crew was professional and the journey was smooth. The online system made it easy to book even last minute."</p>
                    <div class="flex items-center">
                        <img class="h-10 w-10 rounded-full object-cover" src="https://randomuser.me/api/portraits/women/62.jpg" alt="Customer">
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Dewi Putri</h4>
                            <p class="text-sm text-gray-500">Traveled from Padang Bai to Lembar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us -->
    <section id="about" class="py-20 bg-primary-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <div>
                    <h2 class="text-3xl font-bold mb-6">About Our Ferry Service</h2>
                    <p class="text-primary-100 mb-6 text-lg">
                        Founded in 2010, our ferry ticket platform has been connecting islands and facilitating easy sea travel throughout Indonesia. We are dedicated to providing safe, reliable, and affordable transportation for passengers and vehicles.
                    </p>
                    <p class="text-primary-100 mb-6 text-lg">
                        Our mission is to simplify sea travel through technology while maintaining the highest standards of safety and customer service. With a wide network of routes connecting major ports across the archipelago, we're proud to help connect the islands of Indonesia.
                    </p>
                    <div class="grid grid-cols-2 gap-6 mt-10">
                        <div>
                            <p class="text-4xl font-bold">150+</p>
                            <p class="text-primary-100">Daily Trips</p>
                        </div>
                        <div>
                            <p class="text-4xl font-bold">50+</p>
                            <p class="text-primary-100">Ferries</p>
                        </div>
                        <div>
                            <p class="text-4xl font-bold">25+</p>
                            <p class="text-primary-100">Routes</p>
                        </div>
                        <div>
                            <p class="text-4xl font-bold">1M+</p>
                            <p class="text-primary-100">Happy Passengers</p>
                        </div>
                    </div>
                </div>
                <div class="mt-10 lg:mt-0 relative">
                    <div class="boat-animation">
                        <img src="https://images.unsplash.com/photo-1580887742560-b8526e2bbae5?q=80&w=1974" alt="Ferry Boat" class="rounded-lg shadow-2xl">
                    </div>
                    <div class="absolute -bottom-10 -right-10 bg-primary-500 rounded-lg p-8 shadow-xl">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-medal text-4xl text-yellow-400"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold">Award-winning Service</h3>
                                <p class="text-primary-100">Best Ferry Operator 2023</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-primary-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-8">Ready to Start Your Journey?</h2>
            <p class="text-xl text-primary-100 mb-12 max-w-3xl mx-auto">
                Book your ferry tickets online for a seamless travel experience. Safe, convenient, and affordable sea transportation to your destination.
            </p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="#routes" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-md text-primary-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                    <i class="fas fa-ship mr-2"></i> Explore Routes
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 border border-white text-base font-medium rounded-md text-white hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                        <i class="fas fa-user-plus mr-2"></i> Sign Up Now
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Contact & Footer -->
    <section id="contact" class="bg-gray-900 text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center mb-6">
                        <img class="h-10 w-auto" src="{{ asset('images/logo.png') }}" alt="Ferry Ticket Logo">
                        <span class="ml-2 text-xl font-bold text-white">FerryTicket</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Your trusted partner for sea travel in Indonesia. Book your ferry tickets online for a seamless experience.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="#home" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="#routes" class="text-gray-400 hover:text-white">Routes</a></li>
                        <li><a href="#howto" class="text-gray-400 hover:text-white">How to Book</a></li>
                        <li><a href="#about" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms & Conditions</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Contact Us</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt text-primary-500 mt-1 mr-3"></i>
                            <span class="text-gray-400">Jl. Pelabuhan Raya No. 123, Jakarta Utara, Indonesia</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone-alt text-primary-500 mt-1 mr-3"></i>
                            <span class="text-gray-400">+62 21 1234 5678</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope text-primary-500 mt-1 mr-3"></i>
                            <span class="text-gray-400">info@ferryticket.com</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-clock text-primary-500 mt-1 mr-3"></i>
                            <span class="text-gray-400">Customer Support: 24/7</span>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Subscribe to Newsletter</h3>
                    <p class="text-gray-400 mb-4">Get updates on new routes and special offers</p>
                    <form class="flex">
                        <input type="email" placeholder="Your email address" class="px-4 py-2 w-full rounded-l-md focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-900">
                        <button type="submit" class="bg-primary-600 px-4 py-2 rounded-r-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">© {{ date('Y') }} Ferry Ticket System. All rights reserved.</p>
                <div class="mt-4 md:mt-0">
                    <img src="https://www.svgrepo.com/show/410289/payment-method.svg" alt="Payment Methods" class="h-8">
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        window.scrollTo({
                            top: target.offsetTop - 70,
                            behavior: 'smooth'
                        });
                        // Close mobile menu if open
                        mobileMenu.classList.add('hidden');
                    }
                });
            });

            // Navbar scroll behavior
            const navbar = document.getElementById('navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('bg-white', 'shadow-md');
                } else {
                    navbar.classList.remove('bg-white', 'shadow-md');
                }
            });
        });
    </script>
</body>
</html>
