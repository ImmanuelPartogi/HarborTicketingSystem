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
                        'glow': '0 0 15px rgba(125, 211, 252, 0.5)',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'slow-spin': 'spin 8s linear infinite',
                        'wave': 'wave 8s ease-in-out infinite',
                        'sway': 'sway 4s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        wave: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-15px)' },
                        },
                        sway: {
                            '0%, 100%': { transform: 'rotate(-3deg)' },
                            '50%': { transform: 'rotate(3deg)' },
                        }
                    }
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

        /* Dynamic Ocean Background */
        .ocean-bg {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #b9e6fe 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated Waves */
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%230ea5e9' fill-opacity='0.2' d='M0,192L48,186.7C96,181,192,171,288,176C384,181,480,203,576,197.3C672,192,768,160,864,165.3C960,171,1056,213,1152,218.7C1248,224,1344,192,1392,176L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
            background-size: 100% 100%;
            background-repeat: no-repeat;
            animation: wave-animation 12s linear infinite;
        }

        .wave:nth-child(2) {
            height: 140px;
            opacity: 0.5;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%230284c7' fill-opacity='0.15' d='M0,64L48,80C96,96,192,128,288,128C384,128,480,96,576,85.3C672,75,768,85,864,122.7C960,160,1056,224,1152,224C1248,224,1344,160,1392,128L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
            animation: wave-animation 8s linear infinite;
            animation-delay: -5s;
        }

        .wave:nth-child(3) {
            height: 180px;
            opacity: 0.3;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%237dd3fc' fill-opacity='0.2' d='M0,256L48,261.3C96,267,192,277,288,266.7C384,256,480,224,576,218.7C672,213,768,235,864,229.3C960,224,1056,192,1152,197.3C1248,203,1344,245,1392,266.7L1440,288L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
            animation: wave-animation 6s linear infinite;
        }

        @keyframes wave-animation {
            0% {
                background-position-x: 0;
            }
            100% {
                background-position-x: 1000px;
            }
        }

        /* Floating bubbles animation */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(125, 211, 252, 0.15);
            animation: float-up 15s ease-in-out infinite;
            z-index: 1;
        }

        @keyframes float-up {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
        }

        /* Glass effect for card */
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        }

        /* Input focus animation */
        .input-focus-effect:focus {
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
        }

        /* Button hover effect */
        .btn-hover-effect:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.4);
        }

        /* Shimmer effect */
        .shimmer {
            position: relative;
            overflow: hidden;
        }

        .shimmer::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            animation: shimmer 6s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) rotate(30deg);
            }
            100% {
                transform: translateX(100%) rotate(30deg);
            }
        }
    </style>
</head>
<body class="ocean-bg min-h-screen">
    <!-- Animated Waves Background -->
    <div class="wave"></div>
    <div class="wave"></div>
    <div class="wave"></div>

    <!-- Floating Bubbles -->
    <div class="bubble" style="width: 30px; height: 30px; left: 10%; animation-duration: 20s;"></div>
    <div class="bubble" style="width: 15px; height: 15px; left: 20%; animation-duration: 15s; animation-delay: 2s;"></div>
    <div class="bubble" style="width: 25px; height: 25px; left: 40%; animation-duration: 18s; animation-delay: 1s;"></div>
    <div class="bubble" style="width: 10px; height: 10px; left: 60%; animation-duration: 12s; animation-delay: 3s;"></div>
    <div class="bubble" style="width: 20px; height: 20px; left: 80%; animation-duration: 16s; animation-delay: 2s;"></div>
    <div class="bubble" style="width: 35px; height: 35px; left: 90%; animation-duration: 22s;"></div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Card container with floating effect and glass morphism -->
        <div class="max-w-md w-full glass-card rounded-3xl p-8 sm:p-10 space-y-8 transform transition-all duration-500 hover:shadow-glow animate-float relative overflow-hidden shimmer">
            <div class="text-center relative">
                <!-- Ocean-themed animated logo container -->
                <div class="flex justify-center relative mb-4">
                    <!-- Animated background circles -->
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-ocean-200 rounded-full z-0 opacity-50 animate-pulse"></div>
                    <div class="absolute -bottom-3 -left-3 w-16 h-16 bg-ocean-300 rounded-full z-0 opacity-30 animate-slow-spin"></div>

                    <!-- Logo container with pulsing effect -->
                    <div class="h-28 w-28 sm:h-32 sm:w-32 flex items-center justify-center bg-gradient-to-br from-ocean-50 to-ocean-100 rounded-full p-2 relative z-10 shadow-lg animate-pulse" style="animation-duration: 3s;">
                        <div class="absolute inset-0 bg-gradient-to-r from-ocean-200/30 to-ocean-300/30 rounded-full animate-spin" style="animation-duration: 8s;"></div>
                        <div class="h-24 w-24 sm:h-28 sm:w-28 bg-white rounded-full flex items-center justify-center relative z-20">
                            <img class="h-20 sm:h-24 w-auto animate-sway" src="{{ asset('images/logo.png') }}" alt="Ferry Ticket System Logo">
                        </div>
                    </div>
                </div>

                <h2 class="mt-6 text-center text-3xl sm:text-4xl font-bold text-gray-800 text-transparent bg-clip-text bg-gradient-to-r from-ocean-800 to-ocean-600">
                    Admin Panel
                </h2>
                <p class="mt-2 text-center text-sm sm:text-base text-gray-600">
                    Enter your credentials to access the dashboard
                </p>

                <!-- Decorative compass icon -->
                <div class="absolute right-0 top-1/4 opacity-10">
                    <i class="fas fa-compass text-ocean-700 text-5xl animate-slow-spin"></i>
                </div>
            </div>

            @if ($errors->any())
                <div class="rounded-xl bg-red-50 p-4 border-l-4 border-red-500 shadow-md transition-all duration-300 hover:shadow-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-lg animate-pulse"></i>
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

            <form class="mt-8 space-y-6" action="{{ route('admin.login') }}" method="POST">
                @csrf
                <input type="hidden" name="remember" value="true">

                <div class="space-y-5">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-ocean-400 group-hover:text-ocean-500 transition-colors duration-300"></i>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="input-focus-effect appearance-none block w-full pl-10 px-4 py-3.5 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-all duration-300 sm:text-sm bg-white/80 backdrop-blur-sm"
                            placeholder="Email address"
                            value="{{ old('email') }}">

                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <i class="fas fa-life-ring text-ocean-300 text-sm animate-spin"></i>
                        </div>
                    </div>

                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-ocean-400 group-hover:text-ocean-500 transition-colors duration-300"></i>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="input-focus-effect appearance-none block w-full pl-10 px-4 py-3.5 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-all duration-300 sm:text-sm bg-white/80 backdrop-blur-sm"
                            placeholder="Password">

                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <i class="fas fa-anchor text-ocean-300 text-sm animate-bounce" style="animation-duration: 2s;"></i>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 text-ocean-600 focus:ring-ocean-500 border-gray-300 rounded transition-colors duration-200" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="btn-hover-effect group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm md:text-base font-medium rounded-xl text-white bg-gradient-to-r from-ocean-600 to-ocean-500 hover:from-ocean-700 hover:to-ocean-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 transition-all duration-300 shadow-md hover:shadow-xl overflow-hidden">
                        <!-- Ripple effect on button -->
                        <span class="absolute top-0 left-0 w-full h-full bg-white/10 transform -translate-x-full hover:translate-x-0 transition-transform duration-700"></span>

                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-ocean-300 group-hover:text-ocean-200 transition-colors duration-200"></i>
                        </span>
                        Sign in to Dashboard
                    </button>
                </div>
            </form>

            <!-- Decorative water drop elements -->
            <div class="absolute -bottom-3 right-6 text-ocean-300 opacity-40 animate-bounce" style="animation-duration: 3s;">
                <i class="fas fa-water text-2xl"></i>
            </div>
            <div class="absolute top-1/3 -left-3 text-ocean-200 opacity-30 animate-bounce" style="animation-duration: 4s;">
                <i class="fas fa-droplet text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Enhanced Decorative boats/ships with parallax effect -->
    <div class="fixed bottom-0 left-0 w-full pointer-events-none overflow-hidden z-10">
        <div class="absolute bottom-6 left-[5%] animate-float" style="animation-duration: 5s;">
            <div class="relative">
                <i class="fas fa-ship text-ocean-700 text-4xl"></i>
                <div class="absolute -bottom-1 -right-1 w-6 h-2 bg-ocean-200 rounded-full opacity-50"></div>
            </div>
        </div>
        <div class="absolute bottom-12 left-[25%] animate-float" style="animation-duration: 7s; animation-delay: 1s;">
            <div class="relative">
                <i class="fas fa-sailboat text-ocean-600 text-2xl"></i>
                <div class="absolute -bottom-1 -right-1 w-4 h-1 bg-ocean-300 rounded-full opacity-50"></div>
            </div>
        </div>
        <div class="absolute bottom-4 left-[45%] animate-float" style="animation-duration: 6s; animation-delay: 0.5s;">
            <div class="relative">
                <i class="fas fa-ship text-ocean-800 text-3xl"></i>
                <div class="absolute -bottom-1 -right-1 w-5 h-1.5 bg-ocean-200 rounded-full opacity-50"></div>
            </div>
        </div>
        <div class="absolute bottom-10 left-[65%] animate-float" style="animation-duration: 8s; animation-delay: 2s;">
            <div class="relative">
                <i class="fas fa-sailboat text-ocean-700 text-xl"></i>
                <div class="absolute -bottom-1 -right-1 w-3 h-1 bg-ocean-300 rounded-full opacity-50"></div>
            </div>
        </div>
        <div class="absolute bottom-7 left-[85%] animate-float" style="animation-duration: 5.5s; animation-delay: 1.5s;">
            <div class="relative">
                <i class="fas fa-ship text-ocean-600 text-2xl"></i>
                <div class="absolute -bottom-1 -right-1 w-4 h-1 bg-ocean-200 rounded-full opacity-50"></div>
            </div>
        </div>
    </div>

    <!-- Subtle footer text -->
    <div class="fixed bottom-0 w-full text-center py-2 text-xs text-ocean-700/50 pointer-events-none">
        © 2025 Ferry Ticket System • Admin Portal
    </div>

    <!-- Simple loading animation that disappears after page loads -->
    <div id="loading-screen" class="fixed inset-0 bg-ocean-50 flex items-center justify-center z-50">
        <div class="text-center">
            <i class="fas fa-ship text-ocean-500 text-5xl animate-bounce"></i>
            <p class="mt-4 text-ocean-600 font-medium">Loading Admin Portal...</p>
        </div>
    </div>

    <script>
        // Simple loading screen
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loading-screen');
            loadingScreen.style.opacity = '0';
            loadingScreen.style.transition = 'opacity 0.8s ease-out';
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 800);
        });

        // Add ripple effect to button
        document.querySelector('button[type="submit"]').addEventListener('mouseenter', function() {
            const button = this;
            const ripple = button.querySelector('span:first-child');
            ripple.classList.remove('-translate-x-full');
            ripple.classList.add('translate-x-0');

            setTimeout(() => {
                ripple.classList.remove('translate-x-0');
                ripple.classList.add('-translate-x-full');
            }, 700);
        });
    </script>
</body>
</html>
