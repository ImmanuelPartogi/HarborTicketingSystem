@extends('admin.layouts.app')

@section('title', 'Profile Settings')
@section('header', 'Profile Settings')

@section('styles')
<style>
    .tab-button {
        transition: all 0.2s ease;
    }
    .tab-button.active {
        border-color: #4f46e5;
        color: #4f46e5;
    }
    .settings-card {
        transition: all 0.3s ease;
    }
    .settings-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .profile-image {
        position: relative;
        transition: all 0.3s ease;
    }
    .profile-image:hover .profile-image-overlay {
        opacity: 1;
    }
    .profile-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        border-radius: 9999px;
    }
</style>
@endsection

@section('content')
<div>
    <!-- Tab Navigation -->
    <div class="mb-6 border-b border-gray-200">
        <div class="flex flex-wrap -mb-px">
            <a href="{{ route('admin.settings') }}"
                class="tab-button mr-2 inline-block py-4 px-4 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-cog mr-2"></i> System Settings
            </a>
            <button
                class="tab-button active mr-2 inline-block py-4 px-4 text-sm font-medium text-center border-b-2 border-primary-600 text-primary-600">
                <i class="fas fa-user mr-2"></i> Profile Settings
            </button>
        </div>
    </div>

    <div class="md:flex md:space-x-6">
        <!-- Profile sidebar -->
        <div class="md:w-1/3 mb-6 md:mb-0">
            <div class="settings-card bg-white shadow rounded-lg overflow-hidden">
                <div class="p-6 text-center">
                    <div class="profile-image mx-auto h-32 w-32 rounded-full overflow-hidden bg-gray-100 mb-4">
                        <img src="{{ asset('images/avatar-placeholder.jpg') }}" alt="Admin Profile" class="h-full w-full object-cover">
                        <div class="profile-image-overlay">
                            <button type="button" class="text-white hover:text-primary-300">
                                <i class="fas fa-camera text-2xl"></i>
                            </button>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $admin->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $admin->email }}</p>

                    <div class="mt-6 text-sm text-gray-500 space-y-2">
                        <div class="flex items-center justify-center">
                            <i class="fas fa-user-shield mr-2"></i>
                            <span>Administrator</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <i class="fas fa-calendar mr-2"></i>
                            <span>Member since {{ $admin->created_at->format('M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <i class="fas fa-clock mr-2"></i>
                            <span>Last login: {{ now()->subHours(rand(1, 24))->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4">
                    <div class="flex justify-center space-x-3">
                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </button>
                        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile form -->
        <div class="md:w-2/3">
            <div class="settings-card bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
                    <p class="mt-1 text-sm text-gray-500">Update your account's profile information and email address.</p>
                </div>

                <div class="p-6">
                    <form action="{{ route('admin.settings.update-profile') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <div class="mt-1">
                                    <input type="text" name="name" id="name" value="{{ old('name', $admin->name) }}" required
                                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <div class="mt-1">
                                    <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}" required
                                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900">Update Password</h3>
                            <p class="mt-1 text-sm text-gray-500">Ensure your account is using a secure password. Leave blank if you don't want to change it.</p>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6 mt-4">
                                <div class="sm:col-span-6">
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                    <div class="mt-1">
                                        <input type="password" name="current_password" id="current_password"
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                    <div class="mt-1">
                                        <input type="password" name="password" id="password"
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                    <div class="mt-1">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Password requirements</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li>Minimum 8 characters long</li>
                                            <li>Include at least one uppercase letter, one lowercase letter, one number, and one special character</li>
                                            <li>Should not be the same as your previous password</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 text-right">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-save mr-2"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="settings-card bg-white shadow rounded-lg overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                        <p class="mt-1 text-sm text-gray-500">Your recent login activity.</p>
                    </div>
                    <button type="button" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                        View All
                    </button>
                </div>

                <div class="p-6">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-10 w-10 rounded-full bg-primary-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-sign-in-alt text-white"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm">
                                                    <a href="#" class="font-medium text-gray-900">Login Successful</a>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    Logged in from IP: 192.168.1.1
                                                </p>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-700">
                                                <p>2 hours ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-user-edit text-white"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm">
                                                    <a href="#" class="font-medium text-gray-900">Profile Updated</a>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    Updated email address
                                                </p>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-700">
                                                <p>Yesterday at 14:32</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative">
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-10 w-10 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-sign-out-alt text-white"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm">
                                                    <a href="#" class="font-medium text-gray-900">Logout</a>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    Session ended
                                                </p>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-700">
                                                <p>Yesterday at 17:45</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
