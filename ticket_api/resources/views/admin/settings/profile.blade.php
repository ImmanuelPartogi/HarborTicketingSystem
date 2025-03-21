@extends('admin.layouts.app')

@section('title', 'Profile Settings')
@section('header', 'Profile Settings')

@section('styles')
<style>
    .tab-button {
        transition: all 0.3s ease;
        position: relative;
    }
    .tab-button:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: transparent;
        transition: all 0.3s ease;
    }
    .tab-button.active {
        color: #4f46e5;
        font-weight: 600;
    }
    .tab-button.active:after {
        background-color: #4f46e5;
    }
    .tab-button:hover:not(.active):after {
        background-color: #d1d5db;
    }
    .settings-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(229, 231, 235, 0.7);
    }
    .settings-card:hover {
        box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border-color: rgba(209, 213, 219, 1);
    }
    .profile-image {
        position: relative;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .profile-image:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
    .form-input {
        transition: all 0.2s ease;
    }
    .form-input:hover {
        border-color: #a5b4fc !important;
    }
    .form-input:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2) !important;
    }
    .timeline-dot {
        position: relative;
        z-index: 10;
    }
    .timeline-line {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 5px;
        width: 1px;
        background-color: #e5e7eb;
    }
    .profile-stat {
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    .profile-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .btn {
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    .btn:hover {
        transform: translateY(-1px);
    }
    .btn:active {
        transform: translateY(1px);
    }
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    @media (max-width: 768px) {
        .md\:space-x-6 {
            margin-top: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Tab Navigation - Enhanced -->
    <div class="mb-8 border-b border-gray-200 overflow-x-auto hide-scrollbar">
        <div class="flex -mb-px space-x-8 px-2">
            <a href="{{ route('admin.settings') }}"
                class="tab-button py-4 px-1 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-cog mr-2"></i> System Settings
            </a>
            <button
                class="tab-button active py-4 px-1 text-sm font-medium text-center border-b-2 border-primary-600 text-primary-600 focus:outline-none whitespace-nowrap">
                <i class="fas fa-user mr-2"></i> Profile Settings
            </button>
            <a href="#"
                class="tab-button py-4 px-1 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-bell mr-2"></i> Notification Settings
            </a>
            <a href="#"
                class="tab-button py-4 px-1 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-shield-alt mr-2"></i> Security
            </a>
        </div>
    </div>

    <div class="md:flex md:space-x-6 space-y-6 md:space-y-0">
        <!-- Profile sidebar - Enhanced -->
        <div class="md:w-1/3">
            <div class="settings-card bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="p-6 text-center bg-gradient-to-b from-indigo-50 to-white relative">
                    <div class="absolute top-0 right-0 m-3">
                        <div class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                            <span class="flex h-2 w-2 mr-1">
                                <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500 mt-0.5"></span>
                            </span>
                            Online
                        </div>
                    </div>

                    <div class="profile-image mx-auto h-32 w-32 rounded-full overflow-hidden bg-gray-100 mb-4 border-4 border-white">
                        <img src="{{ asset('images/avatar-placeholder.jpg') }}" alt="Admin Profile" class="h-full w-full object-cover">
                        <div class="profile-image-overlay">
                            <button type="button" class="text-white hover:text-primary-300 transition-colors">
                                <i class="fas fa-camera text-2xl"></i>
                            </button>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mt-3">{{ $admin->name }}</h3>
                    <p class="text-sm text-gray-500 flex items-center justify-center">
                        <i class="fas fa-envelope mr-1.5 text-gray-400"></i>
                        {{ $admin->email }}
                    </p>

                    <div class="grid grid-cols-3 gap-2 mt-6">
                        <div class="profile-stat bg-indigo-50 rounded-lg p-3 text-center">
                            <div class="text-indigo-600 text-xl font-bold">24</div>
                            <div class="text-xs text-gray-600 mt-1">Actions</div>
                        </div>
                        <div class="profile-stat bg-blue-50 rounded-lg p-3 text-center">
                            <div class="text-blue-600 text-xl font-bold">13</div>
                            <div class="text-xs text-gray-600 mt-1">Logins</div>
                        </div>
                        <div class="profile-stat bg-purple-50 rounded-lg p-3 text-center">
                            <div class="text-purple-600 text-xl font-bold">7</div>
                            <div class="text-xs text-gray-600 mt-1">Days</div>
                        </div>
                    </div>

                    <div class="mt-6 text-sm text-gray-600 space-y-3">
                        <div class="flex items-center justify-center p-2 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 mr-2">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <span>Administrator</span>
                        </div>
                        <div class="flex items-center justify-center p-2 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mr-2">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <span>Member since {{ $admin->created_at->format('M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-center p-2 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600 mr-2">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span>Last login: {{ now()->subHours(rand(1, 24))->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4">
                    <div class="flex justify-center space-x-3">
                        <button type="button" class="btn inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </button>
                        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile form - Enhanced -->
        <div class="md:w-2/3 space-y-6">
            <div class="settings-card bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg mr-3">
                            <i class="fas fa-user-edit"></i>
                        </span>
                        Profile Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Update your account's profile information and email address.</p>
                </div>

                <div class="p-6">
                    <form action="{{ route('admin.settings.update-profile') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Full Name
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" name="name" id="name" value="{{ old('name', $admin->name) }}" required
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email Address
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}" required
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Phone Number
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone-alt text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $admin->phone ?? '') }}"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="job_title" class="block text-sm font-medium text-gray-700 mb-1">
                                    Job Title
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-briefcase text-gray-400"></i>
                                    </div>
                                    <input type="text" name="job_title" id="job_title" value="{{ old('job_title', $admin->job_title ?? 'System Administrator') }}"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                @error('job_title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-6">
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                                    Bio
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <textarea name="bio" id="bio" rows="3"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('bio', $admin->bio ?? '') }}</textarea>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Brief description for your profile. URLs are hyperlinked.</p>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-lock text-indigo-600 mr-2"></i>
                                Update Password
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">Ensure your account is using a secure password. Leave blank if you don't want to change it.</p>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6 mt-4">
                                <div class="sm:col-span-6">
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                        Current Password
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-key text-gray-400"></i>
                                        </div>
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                        New Password
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>
                                        <input type="password" name="password" id="password"
                                            class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm New Password
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-5">
                            <div class="flex">
                                <div class="flex-shrink-0 pt-0.5">
                                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
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

                        <div class="pt-6 flex justify-end">
                            <button type="submit" class="btn inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <i class="fas fa-save mr-2"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Log - Enhanced -->
            <div class="settings-card bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <span class="flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 rounded-lg mr-3">
                                <i class="fas fa-history"></i>
                            </span>
                            Recent Activity
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">Your recent login activity and actions.</p>
                    </div>
                    <button type="button" class="btn text-indigo-600 hover:text-indigo-800 text-sm font-medium px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 flex items-center">
                        <i class="fas fa-list-ul mr-1.5"></i>
                        View All
                    </button>
                </div>

                <div class="p-6">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8 relative">
                            <div class="timeline-line"></div>
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex items-start space-x-3">
                                        <div class="timeline-dot">
                                            <div class="h-11 w-11 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-sign-in-alt text-white"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1 py-1.5 bg-indigo-50 rounded-lg px-4 shadow-sm">
                                            <div>
                                                <div class="text-sm font-medium text-indigo-800">
                                                    Login Successful
                                                </div>
                                                <p class="mt-1 text-sm text-indigo-600">
                                                    Logged in from IP: 192.168.1.1
                                                </p>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-600 flex items-center">
                                                <i class="fas fa-clock mr-1.5 text-gray-400"></i>
                                                <p>2 hours ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex items-start space-x-3">
                                        <div class="timeline-dot">
                                            <div class="h-11 w-11 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-user-edit text-white"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1 py-1.5 bg-green-50 rounded-lg px-4 shadow-sm">
                                            <div>
                                                <div class="text-sm font-medium text-green-800">
                                                    Profile Updated
                                                </div>
                                                <p class="mt-1 text-sm text-green-600">
                                                    Updated email address
                                                </p>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-600 flex items-center">
                                                <i class="fas fa-clock mr-1.5 text-gray-400"></i>
                                                <p>Yesterday at 14:32</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative">
                                    <div class="relative flex items-start space-x-3">
                                        <div class="timeline-dot">
                                            <div class="h-11 w-11 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-sign-out-alt text-white"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1 py-1.5 bg-red-50 rounded-lg px-4 shadow-sm">
                                            <div>
                                                <div class="text-sm font-medium text-red-800">
                                                    Logout
                                                </div>
                                                <p class="mt-1 text-sm text-red-600">
                                                    Session ended
                                                </p>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-600 flex items-center">
                                                <i class="fas fa-clock mr-1.5 text-gray-400"></i>
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

            <!-- Two-Factor Authentication Card -->
            <div class="settings-card bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="flex items-center justify-center w-8 h-8 bg-purple-100 text-purple-600 rounded-lg mr-3">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                        Two-Factor Authentication
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Add additional security to your account using two-factor authentication.</p>
                </div>

                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-base font-medium text-gray-900">Add extra security with 2FA</h4>
                            <p class="mt-1 text-sm text-gray-500">
                                When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                            </p>
                        </div>
                        <div class="relative inline-block w-14 h-7 transition duration-200 ease-in-out">
                            <input type="checkbox" id="toggle_2fa" name="toggle_2fa" class="absolute w-7 h-7 opacity-0 z-10 cursor-pointer" />
                            <label for="toggle_2fa" class="toggle-label block overflow-hidden h-7 rounded-full bg-gray-300 cursor-pointer"></label>
                            <span class="toggle-dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition-transform duration-300"></span>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <p class="text-sm text-gray-500">
                            For added security, you can also logout from all your other browser sessions across all of your devices. If you feel your account has been compromised, you should also update your password.
                        </p>
                        <div class="mt-4">
                            <button type="button" class="btn inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                <i class="fas fa-sign-out-alt mr-2"></i> Log Out Other Browser Sessions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .toggle-label {
        transition: background-color 0.3s ease;
    }
    input:checked + .toggle-label {
        background-color: #4f46e5;
    }
    input:checked ~ .toggle-dot {
        transform: translateX(100%);
    }
</style>
@endsection
