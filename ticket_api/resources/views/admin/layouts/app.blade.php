<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Ferry Ticket System</title>

    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Poppins', 'sans-serif']
                    },
                    colors: {
                        'primary': {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        'secondary': {
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
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    <!-- Custom styles -->
    <style>
        [x-cloak] { display: none !important; }

        /* Scrollbar Styles */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Animation classes */
        .nav-item {
            transition: all 0.2s ease;
        }
        .nav-item:hover {
            transform: translateX(5px);
        }
        .nav-icon {
            transition: all 0.2s ease;
        }
        .nav-item:hover .nav-icon {
            transform: scale(1.2);
        }
        .alert-fade {
            animation: fadeOut 5s forwards;
        }
        @keyframes fadeOut {
            90% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>

    @yield('styles')
</head>
<body class="bg-gray-50 font-sans">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">
        <!-- Mobile sidebar backdrop -->
        <div x-cloak x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 lg:hidden bg-gray-600 bg-opacity-75"
            @click="sidebarOpen = false"
            aria-hidden="true">
        </div>

        <!-- Mobile sidebar panel -->
        <div x-cloak x-show="sidebarOpen"
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col lg:hidden">

            <!-- Close button -->
            <div class="absolute top-0 right-0 -mr-12 pt-2">
                <button type="button" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Sidebar content -->
            <div class="flex-1 flex flex-col min-h-0 bg-gradient-to-b from-primary-900 to-primary-700 shadow-xl overflow-y-auto">
                <div class="flex-shrink-0 flex items-center px-4 py-5">
                    <img class="h-10 w-auto" src="{{ asset('images/logo.png') }}" alt="Ferry Ticket">
                    <span class="ml-3 text-xl font-semibold text-white">Ferry Admin</span>
                </div>
                <div class="mt-2 flex-1 px-2">
                    @include('admin.layouts.sidebar')
                </div>
                <div class="p-4 border-t border-primary-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-primary-600 flex items-center justify-center text-white">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">{{ Auth::guard('admin')->user()->name }}</p>
                            <form method="POST" action="{{ route('admin.logout') }}" class="mt-1">
                                @csrf
                                <button type="submit" class="text-xs text-primary-200 hover:text-white flex items-center">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex-1 flex flex-col min-h-0 bg-gradient-to-b from-primary-900 to-primary-700 shadow-xl">
                    <div class="flex-shrink-0 flex items-center px-4 py-5">
                        <img class="h-10 w-auto" src="{{ asset('images/logo.png') }}" alt="Ferry Ticket">
                        <span class="ml-3 text-xl font-semibold text-white">Ferry Admin</span>
                    </div>
                    <div class="mt-2 flex-1 flex flex-col overflow-y-auto px-2">
                        @include('admin.layouts.sidebar')
                    </div>
                    <div class="p-4 border-t border-primary-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-primary-600 flex items-center justify-center text-white">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-white">{{ Auth::guard('admin')->user()->name }}</p>
                                <form method="POST" action="{{ route('admin.logout') }}" class="mt-1">
                                    @csrf
                                    <button type="submit" class="text-xs text-primary-200 hover:text-white flex items-center">
                                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content area -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top navigation -->
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow-sm">
                <button @click="sidebarOpen = true" class="lg:hidden px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">@yield('header', 'Dashboard')</h1>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6">
                        <!-- Date display -->
                        <div class="hidden sm:flex items-center bg-gray-100 text-gray-600 rounded-lg py-1 px-3 mr-4">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <span>{{ date('d M Y') }}</span>
                        </div>

                        <!-- Notification bell -->
                        <button class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 mr-3">
                            <span class="sr-only">View notifications</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0h-6" />
                            </svg>
                        </button>

                        <!-- Profile dropdown - visible only on mobile -->
                        <div class="lg:hidden flex items-center">
                            <span class="inline-block text-sm text-gray-700 mr-2">{{ Auth::guard('admin')->user()->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <!-- Alert messages -->
                    @if (session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm alert-fade" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-sm alert-fade" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Page content -->
                    @yield('content')
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center">
                        <div class="text-sm text-gray-500 mb-2 sm:mb-0">
                            &copy; 2023 Ferry Ticket System. All rights reserved.
                        </div>
                        <div class="text-sm text-gray-500">
                            Version 1.0.0
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @yield('scripts')

    <!-- Global App Scripts -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-fade');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
