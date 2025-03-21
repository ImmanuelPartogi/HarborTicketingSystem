@extends('admin.layouts.app')

@section('title', 'Profile Settings')
@section('header', 'Profile Settings')

@section('styles')
<style>
    /* Enhanced Tab Navigation */
    .tab-button {
        transition: all 0.3s ease;
        position: relative;
        padding-top: 1rem;
        padding-bottom: 1rem;
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

    /* Enhanced Card Styling */
    .settings-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(229, 231, 235, 0.5);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .settings-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: rgba(209, 213, 219, 0.8);
    }

    /* Enhanced Profile Image */
    .profile-image {
        position: relative;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .profile-image:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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

    /* Enhanced Form Elements */
    .form-input {
        transition: all 0.2s ease;
    }
    .form-input:hover {
        border-color: #a5b4fc !important;
    }
    .form-input:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2) !important;
    }

    /* Enhanced Timeline */
    .timeline-dot {
        position: relative;
        z-index: 10;
    }
    .timeline-line {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 5px;
        width: 2px;
        background: linear-gradient(to bottom, #e0e7ff, #c7d2fe, #a5b4fc);
        border-radius: 9999px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 2rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-content {
        transition: all 0.3s ease;
    }
    .timeline-content:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* Enhanced Profile Stats */
    .profile-stat {
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    .profile-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Enhanced Buttons */
    .btn {
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    .btn:hover {
        transform: translateY(-2px);
    }
    .btn:active {
        transform: translateY(1px);
    }

    /* Card Header Styling */
    .card-header {
        background: linear-gradient(to right, #f9fafb, #ffffff);
        border-bottom: 1px solid rgba(229, 231, 235, 0.7);
        padding: 1.5rem;
        display: flex;
        align-items: center;
    }
    .header-icon {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        margin-right: 1rem;
        background-color: #eef2ff;
        color: #4f46e5;
        font-size: 1.25rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    .card-title {
        font-weight: 600;
        font-size: 1.125rem;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    .card-subtitle {
        color: #6b7280;
        font-size: 0.875rem;
    }

    /* Enhanced Form Labels */
    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
        display: flex;
        align-items: center;
    }
    .form-label i {
        margin-right: 0.375rem;
        color: #6366f1;
        font-size: 0.875rem;
    }

    /* Enhanced Toggle Switch */
    .toggle-wrapper {
        position: relative;
        display: inline-block;
        width: 3.5rem;
        height: 1.75rem;
    }
    .toggle-input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e5e7eb;
        transition: .4s;
        border-radius: 1.75rem;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 1.25rem;
        width: 1.25rem;
        left: 0.25rem;
        bottom: 0.25rem;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .toggle-input:checked + .toggle-slider {
        background-color: #4f46e5;
    }
    .toggle-input:checked + .toggle-slider:before {
        transform: translateX(1.75rem);
    }
    .toggle-input:focus + .toggle-slider {
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    /* Hide scrollbar but allow scrolling */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Media queries for better responsive design */
    @media (max-width: 768px) {
        .layout-container {
            flex-direction: column;
        }
        .sidebar-container {
            width: 100%;
            margin-bottom: 1.5rem;
        }
        .main-container {
            width: 100%;
        }
    }

    /* Pulse Animation */
    @keyframes pulse {
        0% {
            transform: scale(0.95);
            opacity: 0.7;
        }
        70% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(0.95);
            opacity: 0.7;
        }
    }
    .pulse-animation {
        animation: pulse 2s infinite;
    }

    /* Status Badge */
    .status-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        z-index: 20;
    }

    /* Info Badge */
    .info-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    /* Profile Stats Container */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    /* User Info Items */
    .user-info-item {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        background-color: #f9fafb;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    .user-info-item:hover {
        background-color: #f3f4f6;
        transform: translateX(3px);
    }
    .user-info-icon {
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        margin-right: 0.5rem;
        font-size: 0.875rem;
    }

    /* Section titles */
    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        color: #1f2937;
        display: flex;
        align-items: center;
        position: relative;
        padding-bottom: 0.5rem;
    }
    .section-title:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        height: 2px;
        width: 40px;
        background: linear-gradient(to right, #4f46e5, #818cf8);
        border-radius: 2px;
    }
    .section-title i {
        margin-right: 0.5rem;
        color: #4f46e5;
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Enhanced Tab Navigation -->
    <div class="mb-8 border-b border-gray-200 overflow-x-auto hide-scrollbar">
        <div class="flex -mb-px space-x-6 sm:space-x-8">
            <a href="{{ route('admin.settings') }}"
                class="tab-button py-4 px-2 text-sm font-medium text-center flex items-center text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-cog mr-2 text-lg"></i> <span>System Settings</span>
            </a>
            <button
                class="tab-button active py-4 px-2 text-sm font-medium text-center flex items-center focus:outline-none whitespace-nowrap">
                <i class="fas fa-user mr-2 text-lg text-indigo-600"></i> <span>Profile Settings</span>
            </button>
        </div>
    </div>

    <div class="flex flex-col md:flex-row space-y-6 md:space-y-0 md:space-x-6 layout-container">
        <!-- Enhanced Profile Sidebar -->
        <div class="md:w-1/3 sidebar-container">
            <div class="settings-card bg-white rounded-xl overflow-hidden">
                <!-- Profile Header -->
                <div class="p-6 text-center bg-gradient-to-b from-indigo-50 to-white relative">
                    <!-- Status Badge -->
                    <div class="status-badge">
                        <div class="info-badge bg-green-100 text-green-800">
                            <span class="inline-block h-2 w-2 rounded-full bg-green-500 mr-1.5 pulse-animation"></span>
                            Online
                        </div>
                    </div>

                    <!-- Profile Image -->
                    <div class="profile-image mx-auto h-32 w-32 rounded-full overflow-hidden bg-gray-100 mb-4 border-4 border-white">
                        <img src="{{ asset('images/avatar-placeholder.jpg') }}" alt="{{ $admin->name }}" class="h-full w-full object-cover">
                        <div class="profile-image-overlay">
                            <button type="button" class="text-white hover:text-indigo-200 transition-colors">
                                <i class="fas fa-camera text-2xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Profile Info -->
                    <h3 class="text-xl font-bold text-gray-900 mt-3">{{ $admin->name }}</h3>
                    <p class="text-sm text-gray-500 flex items-center justify-center mt-1">
                        <i class="fas fa-envelope mr-1.5 text-gray-400"></i>
                        {{ $admin->email }}
                    </p>

                    <!-- Profile Stats -->
                    <div class="stats-container">
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

                    <!-- User Info Items -->
                    <div class="mt-6 text-sm text-gray-600 space-y-2">
                        <div class="user-info-item">
                            <div class="user-info-icon bg-indigo-100 text-indigo-600">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <span>Administrator</span>
                        </div>
                        <div class="user-info-item">
                            <div class="user-info-icon bg-blue-100 text-blue-600">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <span>Member since {{ $admin->created_at->format('M Y') }}</span>
                        </div>
                        <div class="user-info-item">
                            <div class="user-info-icon bg-green-100 text-green-600">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span>Last login: {{ now()->subHours(rand(1, 24))->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Profile Actions -->
                <div class="bg-gray-50 px-6 py-4">
                    <div class="flex flex-col sm:flex-row justify-center space-y-2 sm:space-y-0 sm:space-x-3">
                        <button type="button" class="btn inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm w-full sm:w-auto">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </button>
                        <form method="POST" action="{{ route('admin.logout') }}" class="inline w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="btn inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm w-full">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Profile Form and Content -->
        <div class="md:w-2/3 space-y-6 main-container">
            <!-- Profile Information Form -->
            <div class="settings-card bg-white rounded-xl overflow-hidden">
                <div class="card-header">
                    <div class="header-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Profile Information</h3>
                        <p class="card-subtitle">Update your account's profile information and email address.</p>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <form action="{{ route('admin.settings.update-profile') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="name" class="block text-sm font-medium form-label">
                                    <i class="fas fa-user"></i> Full Name
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" name="name" id="name" value="{{ old('name', $admin->name) }}" required
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="email" class="block text-sm font-medium form-label">
                                    <i class="fas fa-envelope"></i> Email Address
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}" required
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="phone" class="block text-sm font-medium form-label">
                                    <i class="fas fa-phone-alt"></i> Phone Number
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone-alt text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $admin->phone ?? '') }}"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label for="job_title" class="block text-sm font-medium form-label">
                                    <i class="fas fa-briefcase"></i> Job Title
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-briefcase text-gray-400"></i>
                                    </div>
                                    <input type="text" name="job_title" id="job_title" value="{{ old('job_title', $admin->job_title ?? 'System Administrator') }}"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                @error('job_title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-6">
                                <label for="bio" class="block text-sm font-medium form-label">
                                    <i class="fas fa-comment-alt"></i> Bio
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <textarea name="bio" id="bio" rows="3"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">{{ old('bio', $admin->bio ?? '') }}</textarea>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 flex items-center">
                                    <i class="fas fa-info-circle mr-1.5 text-gray-400"></i>
                                    Brief description for your profile. URLs are hyperlinked.
                                </p>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="section-title">
                                <i class="fas fa-lock"></i> Update Password
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 mb-6">Ensure your account is using a secure password. Leave blank if you don't want to change it.</p>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                                <div class="sm:col-span-6">
                                    <label for="current_password" class="block text-sm font-medium form-label">
                                        <i class="fas fa-key"></i> Current Password
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-key text-gray-400"></i>
                                        </div>
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                    </div>
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="password" class="block text-sm font-medium form-label">
                                        <i class="fas fa-lock"></i> New Password
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>
                                        <input type="password" name="password" id="password"
                                            class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                    </div>
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="password_confirmation" class="block text-sm font-medium form-label">
                                        <i class="fas fa-lock"></i> Confirm New Password
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Requirements Box -->
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

                        <!-- Form Actions -->
                        <div class="pt-6 flex justify-end">
                            <button type="submit" class="btn inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <i class="fas fa-save mr-2"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enhanced Activity Log -->
            <div class="settings-card bg-white rounded-xl overflow-hidden">
                <div class="card-header justify-between flex-wrap">
                    <div class="flex items-center mb-2 sm:mb-0">
                        <div class="header-icon bg-blue-100 text-blue-600">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h3 class="card-title">Recent Activity</h3>
                            <p class="card-subtitle">Your recent login activity and actions.</p>
                        </div>
                    </div>
                    <button type="button" class="btn text-indigo-600 hover:text-indigo-800 text-sm font-medium px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 flex items-center">
                        <i class="fas fa-list-ul mr-1.5"></i>
                        View All
                    </button>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="flow-root">
                        <ul role="list" class="relative">
                            <div class="timeline-line"></div>

                            <!-- Timeline Item 1 -->
                            <li class="timeline-item">
                                <div class="relative flex space-x-4">
                                    <div class="timeline-dot">
                                        <div class="h-12 w-12 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-sign-in-alt text-white text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-1.5">
                                        <div class="timeline-content bg-indigo-50 rounded-xl p-4 shadow-sm">
                                            <div>
                                                <div class="text-sm font-semibold text-indigo-800">
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

                            <!-- Timeline Item 2 -->
                            <li class="timeline-item">
                                <div class="relative flex space-x-4">
                                    <div class="timeline-dot">
                                        <div class="h-12 w-12 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-user-edit text-white text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-1.5">
                                        <div class="timeline-content bg-green-50 rounded-xl p-4 shadow-sm">
                                            <div>
                                                <div class="text-sm font-semibold text-green-800">
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

                            <!-- Timeline Item 3 -->
                            <li>
                                <div class="relative flex space-x-4">
                                    <div class="timeline-dot">
                                        <div class="h-12 w-12 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-sign-out-alt text-white text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-1.5">
                                        <div class="timeline-content bg-red-50 rounded-xl p-4 shadow-sm">
                                            <div>
                                                <div class="text-sm font-semibold text-red-800">
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
            <div class="settings-card bg-white rounded-xl overflow-hidden">
                <div class="card-header">
                    <div class="header-icon bg-purple-100 text-purple-600">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Two-Factor Authentication</h3>
                        <p class="card-subtitle">Add additional security to your account using two-factor authentication.</p>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between space-y-4 sm:space-y-0">
                        <div class="sm:pr-8">
                            <h4 class="text-base font-medium text-gray-900">Add extra security with 2FA</h4>
                            <p class="mt-1 text-sm text-gray-500">
                                When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                            </p>
                        </div>
                        <div class="toggle-wrapper">
                            <input type="checkbox" id="toggle_2fa" name="toggle_2fa" class="toggle-input" />
                            <label for="toggle_2fa" class="toggle-slider"></label>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200">
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
@endsection
