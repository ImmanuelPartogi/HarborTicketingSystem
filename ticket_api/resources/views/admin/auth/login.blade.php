<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Ferry Ticket System</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ocean': {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#b9e6fe',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                    boxShadow: {
                        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                    },
                },
            },
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .wave-bg {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%230ea5e9' fill-opacity='0.2' d='M0,192L48,186.7C96,181,192,171,288,176C384,181,480,203,576,197.3C672,192,768,160,864,165.3C960,171,1056,213,1152,218.7C1248,224,1344,192,1392,176L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: bottom;
            background-size: 100% 25%;
        }
        .login-container {
            backdrop-filter: blur(3px);
        }
    </style>
</head>
<body class="bg-gradient-to-tr from-sky-50 to-ocean-100 wave-bg min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8">
        <!-- Card container with floating effect -->
        <div class="max-w-md w-full bg-white rounded-2xl shadow-soft border border-ocean-100 p-6 sm:p-8 login-container space-y-6 transform transition-all duration-300 hover:shadow-lg">
            <div class="text-center">
                <!-- Ocean-themed logo container -->
                <div class="flex justify-center relative mb-2">
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-ocean-100 rounded-full z-0 opacity-50"></div>
                    <div class="h-24 w-24 sm:h-28 sm:w-28 flex items-center justify-center bg-ocean-50 rounded-full p-2 relative z-10">
                        <img class="h-20 sm:h-24 w-auto" src="{{ asset('images/logo.png') }}" alt="Ferry Ticket System Logo">
                    </div>
                    <div class="absolute -bottom-2 -left-2 w-12 h-12 bg-ocean-200 rounded-full z-0 opacity-40"></div>
                </div>

                <h2 class="mt-5 text-center text-2xl sm:text-3xl font-bold text-gray-800">
                    Admin Panel
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Enter your credentials to access the dashboard
                </p>
            </div>

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 border-l-4 border-red-500 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                There were errors with your submission
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form class="mt-6 space-y-6" action="{{ route('admin.login') }}" method="POST">
                @csrf
                <input type="hidden" name="remember" value="true">

                <div class="space-y-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="appearance-none block w-full pl-10 px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition duration-200 sm:text-sm"
                            placeholder="Email address"
                            value="{{ old('email') }}">
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="appearance-none block w-full pl-10 px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition duration-200 sm:text-sm"
                            placeholder="Password">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox"
                            class="h-4 w-4 text-ocean-600 focus:ring-ocean-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm md:text-base font-medium rounded-lg text-white bg-gradient-to-r from-ocean-600 to-ocean-500 hover:from-ocean-700 hover:to-ocean-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 transition duration-200 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-ocean-300 group-hover:text-ocean-200 transition-colors duration-200"></i>
                        </span>
                        Sign in
                    </button>
                </div>
            </form>

            <!-- Decorative elements -->
            <div class="absolute -bottom-2 -right-2 w-20 h-20 bg-ocean-100 rounded-full opacity-50 z-0"></div>
            <div class="absolute top-1/4 -left-3 w-6 h-6 bg-ocean-200 rounded-full opacity-40 z-0"></div>
        </div>
    </div>

    <!-- Decorative boats/ships at the bottom -->
    <div class="fixed bottom-0 left-0 w-full pointer-events-none overflow-hidden z-0 opacity-30">
        <div class="absolute bottom-0 left-[10%] animate-bounce" style="animation-duration: 5s;">
            <i class="fas fa-ship text-ocean-700 text-3xl"></i>
        </div>
        <div class="absolute bottom-5 left-[25%] animate-bounce" style="animation-duration: 7s; animation-delay: 1s;">
            <i class="fas fa-sailboat text-ocean-800 text-xl"></i>
        </div>
        <div class="absolute bottom-2 left-[60%] animate-bounce" style="animation-duration: 4s; animation-delay: 0.5s;">
            <i class="fas fa-ship text-ocean-700 text-2xl"></i>
        </div>
        <div class="absolute bottom-6 left-[80%] animate-bounce" style="animation-duration: 6s; animation-delay: 2s;">
            <i class="fas fa-sailboat text-ocean-600 text-lg"></i>
        </div>
    </div>
</body>
</html>
