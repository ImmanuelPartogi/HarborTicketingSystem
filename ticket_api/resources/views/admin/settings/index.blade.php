{{-- resources/views/admin/settings/index.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'System Settings')
@section('header', 'System Settings')
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
    /* Enhanced Section Titles */
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
    /* Enhanced Buttons */
    .save-button {
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    .save-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .save-button:active {
        transform: translateY(1px);
    }
    /* Enhanced Form Descriptions */
    .form-description {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.375rem;
        display: flex;
        align-items: center;
    }
    .form-description i {
        margin-right: 0.375rem;
        color: #9ca3af;
        font-size: 0.75rem;
    }
    /* Hide scrollbar but allow scrolling */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
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
    /* Info Box Styling */
    .info-box {
        background-color: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-top: 2rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    .info-box:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .info-icon {
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background-color: #dbeafe;
        color: #3b82f6;
        font-size: 1rem;
        margin-right: 1rem;
    }
    /* Enhanced Tab Pills */
    .tab-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .tab-pill {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tab-pill i {
        margin-right: 0.5rem;
    }
    .tab-pill.active {
        background-color: #4f46e5;
        color: white;
        font-weight: 500;
    }
    .tab-pill:not(.active) {
        background-color: #f3f4f6;
        color: #6b7280;
    }
    .tab-pill:not(.active):hover {
        background-color: #e5e7eb;
        color: #4b5563;
    }
</style>
@endsection
@section('content')
<div x-data="{ activeTab: 'system', activeSection: 'general' }" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Enhanced Tab Navigation -->
    <div class="mb-8 border-b border-gray-200 overflow-x-auto hide-scrollbar">
        <div class="flex -mb-px space-x-6 sm:space-x-8">
            <button @click="activeTab = 'system'; activeSection = 'general'"
                class="tab-button py-4 px-2 text-sm font-medium text-center flex items-center focus:outline-none whitespace-nowrap"
                :class="activeTab === 'system' ? 'active text-indigo-600' : 'text-gray-500 hover:text-gray-700'">
                <i class="fas fa-cog mr-2 text-lg"></i> <span>System Settings</span>
            </button>
            <button @click="activeTab = 'landing'; activeSection = 'hero'"
                class="tab-button py-4 px-2 text-sm font-medium text-center flex items-center focus:outline-none whitespace-nowrap"
                :class="activeTab === 'landing' ? 'active text-indigo-600' : 'text-gray-500 hover:text-gray-700'">
                <i class="fas fa-home mr-2 text-lg"></i> <span>Landing Page</span>
            </button>
            <a href="{{ route('admin.settings.profile') }}"
                class="tab-button py-4 px-2 text-sm font-medium text-center flex items-center text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-user mr-2 text-lg"></i> <span>Profile Settings</span>
            </a>
        </div>
    </div>

    <!-- System Settings Tab -->
    <div x-show="activeTab === 'system'"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="space-y-8">
        <!-- General Settings Card -->
        <div class="settings-card bg-white rounded-xl overflow-hidden">
            <div class="card-header">
                <div class="header-icon">
                    <i class="fas fa-wrench"></i>
                </div>
                <div>
                    <h3 class="card-title">General Settings</h3>
                    <p class="card-subtitle">Configure the general settings for your ferry ticket system.</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <form action="{{ route('admin.settings.update-system') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="site_name" class="block text-sm font-medium form-label">
                                <i class="fas fa-globe"></i> Site Name
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-globe text-gray-400"></i>
                                </div>
                                <input type="text" name="site_name" id="site_name"
                                    value="{{ $settings['site_name']->value ?? 'Ferry Ticket System' }}"
                                    class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> This will be displayed in browser tabs and email notifications.
                            </p>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="contact_email" class="block text-sm font-medium form-label">
                                <i class="fas fa-envelope"></i> Contact Email
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="contact_email" id="contact_email"
                                    value="{{ $settings['contact_email']->value ?? 'contact@ferryticket.com' }}"
                                    class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> This email will be used for system notifications and customer support.
                            </p>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="phone_number" class="block text-sm font-medium form-label">
                                <i class="fas fa-phone-alt"></i> Contact Phone
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone-alt text-gray-400"></i>
                                </div>
                                <input type="text" name="phone_number" id="phone_number"
                                    value="{{ $settings['phone_number']->value ?? '' }}"
                                    class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> Customer support phone number (optional).
                            </p>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="booking_expiry_hours" class="block text-sm font-medium form-label">
                                <i class="fas fa-hourglass-half"></i> Booking Expiry Hours
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-hourglass-half text-gray-400"></i>
                                </div>
                                <input type="number" name="booking_expiry_hours" id="booking_expiry_hours"
                                    value="{{ $settings['booking_expiry_hours']->value ?? '24' }}" min="1" max="72"
                                    class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <label for="hours" class="sr-only">Hours</label>
                                    <div class="px-3 py-2 rounded-r-md bg-gray-50 text-gray-500 sm:text-sm border-l border-gray-300">
                                        Hours
                                    </div>
                                </div>
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> Number of hours before an unpaid booking expires (1-72).
                            </p>
                        </div>
                    </div>
                    <!-- Payment Settings Section -->
                    <div class="mt-10 pt-6 border-t border-gray-200">
                        <h3 class="section-title">
                            <i class="fas fa-credit-card"></i> Payment Settings
                        </h3>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6 mt-6">
                            <div class="sm:col-span-3">
                                <label for="currency" class="block text-sm font-medium form-label">
                                    <i class="fas fa-money-bill-wave"></i> Currency
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-money-bill-wave text-gray-400"></i>
                                    </div>
                                    <select id="currency" name="currency"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 appearance-none py-2.5">
                                        <option value="IDR" {{ ($settings['currency']->value ?? 'IDR') == 'IDR' ? 'selected' : '' }}>Indonesian Rupiah (IDR)</option>
                                        <option value="USD" {{ ($settings['currency']->value ?? '') == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                                        <option value="EUR" {{ ($settings['currency']->value ?? '') == 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                                        <option value="SGD" {{ ($settings['currency']->value ?? '') == 'SGD' ? 'selected' : '' }}>Singapore Dollar (SGD)</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Primary currency for all transactions.
                                </p>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="tax_percentage" class="block text-sm font-medium form-label">
                                    <i class="fas fa-percentage"></i> Tax Percentage (%)
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-percentage text-gray-400"></i>
                                    </div>
                                    <input type="number" step="0.01" name="tax_percentage" id="tax_percentage"
                                        value="{{ $settings['tax_percentage']->value ?? '10' }}" min="0" max="100"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                    <div class="absolute inset-y-0 right-0 flex items-center">
                                        <label for="percentage" class="sr-only">Percentage</label>
                                        <div class="px-3 py-2 rounded-r-md bg-gray-50 text-gray-500 sm:text-sm border-l border-gray-300">
                                            %
                                        </div>
                                    </div>
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Tax rate applied to all bookings.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-6 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                        <button type="button" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-undo mr-2"></i> Reset to Defaults
                        </button>
                        <button type="submit" class="w-full sm:w-auto save-button inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Landing Page Settings Tab -->
    <div x-show="activeTab === 'landing'"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="space-y-8">

        <!-- Section Tabs -->
        <div class="tab-pills">
            <button @click="activeSection = 'hero'"
                :class="activeSection === 'hero' ? 'active' : ''"
                class="tab-pill">
                <i class="fas fa-image"></i> Hero Section
            </button>
            <button @click="activeSection = 'features'"
                :class="activeSection === 'features' ? 'active' : ''"
                class="tab-pill">
                <i class="fas fa-list-ul"></i> Features
            </button>
            <button @click="activeSection = 'routes'"
                :class="activeSection === 'routes' ? 'active' : ''"
                class="tab-pill">
                <i class="fas fa-route"></i> Routes
            </button>
            <button @click="activeSection = 'howto'"
                :class="activeSection === 'howto' ? 'active' : ''"
                class="tab-pill">
                <i class="fas fa-info-circle"></i> How To Book
            </button>
            <button @click="activeSection = 'about'"
                :class="activeSection === 'about' ? 'active' : ''"
                class="tab-pill">
                <i class="fas fa-building"></i> About Us
            </button>
            <button @click="activeSection = 'footer'"
                :class="activeSection === 'footer' ? 'active' : ''"
                class="tab-pill">
                <i class="fas fa-shoe-prints"></i> Footer
            </button>
            <button @click="activeSection = 'seo'"
                :class="activeSection === 'seo' ? 'active' : ''"
                class="tab-pill">
                <i class="fas fa-search"></i> SEO
            </button>
        </div>

        <!-- Hero Section Settings -->
        <div x-show="activeSection === 'hero'" class="settings-card bg-white rounded-xl overflow-hidden">
            <div class="card-header">
                <div class="header-icon bg-blue-100 text-blue-600">
                    <i class="fas fa-image"></i>
                </div>
                <div>
                    <h3 class="card-title">Hero Section</h3>
                    <p class="card-subtitle">Configure the main banner section at the top of your landing page.</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <form action="{{ route('admin.settings.update-hero') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="hero_title" class="block text-sm font-medium form-label">
                                <i class="fas fa-heading"></i> Hero Title
                            </label>
                            <div class="mt-1">
                                <input type="text" name="hero_title" id="hero_title"
                                    value="{{ $settings['hero_title']->value ?? 'Explore the Sea with Our Ferry Service' }}"
                                    class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> The main headline displayed in the hero section.
                            </p>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="hero_subtitle" class="block text-sm font-medium form-label">
                                <i class="fas fa-align-left"></i> Hero Subtitle
                            </label>
                            <div class="mt-1">
                                <textarea name="hero_subtitle" id="hero_subtitle" rows="3"
                                    class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ $settings['hero_subtitle']->value ?? 'Book your ferry tickets online for a seamless travel experience. Safe, convenient, and affordable sea transportation to your destination.' }}</textarea>
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> Descriptive text displayed below the hero title.
                            </p>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="hero_image" class="block text-sm font-medium form-label">
                                <i class="fas fa-image"></i> Hero Background Image URL
                            </label>
                            <div class="mt-1">
                                <input type="text" name="hero_image" id="hero_image"
                                    value="{{ $settings['hero_image']->value ?? 'https://images.unsplash.com/photo-1523292562811-8fa7962a78c8?q=80&w=2070' }}"
                                    class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> URL of the background image for the hero section.
                            </p>

                            <!-- Image Preview -->
                            <div class="mt-3 rounded-md overflow-hidden h-48 bg-gray-100">
                                <img id="hero_image_preview" src="{{ $settings['hero_image']->value ?? 'https://images.unsplash.com/photo-1523292562811-8fa7962a78c8?q=80&w=2070' }}"
                                     alt="Hero Image Preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="primary_button_text" class="block text-sm font-medium form-label">
                                <i class="fas fa-mouse-pointer"></i> Primary Button Text
                            </label>
                            <div class="mt-1">
                                <input type="text" name="primary_button_text" id="primary_button_text"
                                    value="{{ $settings['primary_button_text']->value ?? 'Check Available Routes' }}"
                                    class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> Text for the primary call-to-action button.
                            </p>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="secondary_button_text" class="block text-sm font-medium form-label">
                                <i class="fas fa-mouse-pointer"></i> Secondary Button Text
                            </label>
                            <div class="mt-1">
                                <input type="text" name="secondary_button_text" id="secondary_button_text"
                                    value="{{ $settings['secondary_button_text']->value ?? 'Learn How to Book' }}"
                                    class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                            <p class="form-description">
                                <i class="fas fa-info-circle"></i> Text for the secondary call-to-action button.
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-6 mt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-end items-center space-y-3 sm:space-y-0">
                        <button type="submit" class="w-full sm:w-auto save-button inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Save Hero Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Features Section Settings -->
        <div x-show="activeSection === 'features'" class="settings-card bg-white rounded-xl overflow-hidden">
            <div class="card-header">
                <div class="header-icon bg-green-100 text-green-600">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h3 class="card-title">Features Section</h3>
                    <p class="card-subtitle">Customize the features and benefits section of your landing page.</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <form action="{{ route('admin.settings.update-features') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="features_title" class="block text-sm font-medium form-label">
                                <i class="fas fa-heading"></i> Section Title
                            </label>
                            <div class="mt-1">
                                <input type="text" name="features_title" id="features_title"
                                    value="{{ $settings['features_title']->value ?? 'Why Choose Our Ferry Service' }}"
                                    class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="features_subtitle" class="block text-sm font-medium form-label">
                                <i class="fas fa-align-left"></i> Section Subtitle
                            </label>
                            <div class="mt-1">
                                <input type="text" name="features_subtitle" id="features_subtitle"
                                    value="{{ $settings['features_subtitle']->value ?? 'Experience the best sea travel with these benefits' }}"
                                    class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                            </div>
                        </div>
                    </div>

                    <!-- Feature 1 -->
                    <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-4">Feature 1</h3>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <label for="feature1_icon" class="block text-sm font-medium form-label">
                                    <i class="fas fa-icons"></i> Icon
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature1_icon" id="feature1_icon"
                                        value="{{ $settings['feature1_icon']->value ?? 'fas fa-anchor' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Font Awesome icon class
                                </p>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="feature1_title" class="block text-sm font-medium form-label">
                                    <i class="fas fa-heading"></i> Title
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature1_title" id="feature1_title"
                                        value="{{ $settings['feature1_title']->value ?? 'Reliable Service' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="feature1_description" class="block text-sm font-medium form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature1_description" id="feature1_description"
                                        value="{{ $settings['feature1_description']->value ?? 'Punctual departures and arrivals with a focus on passenger satisfaction' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-4">Feature 2</h3>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <label for="feature2_icon" class="block text-sm font-medium form-label">
                                    <i class="fas fa-icons"></i> Icon
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature2_icon" id="feature2_icon"
                                        value="{{ $settings['feature2_icon']->value ?? 'fas fa-shield-alt' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Font Awesome icon class
                                </p>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="feature2_title" class="block text-sm font-medium form-label">
                                    <i class="fas fa-heading"></i> Title
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature2_title" id="feature2_title"
                                        value="{{ $settings['feature2_title']->value ?? 'Safety First' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="feature2_description" class="block text-sm font-medium form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature2_description" id="feature2_description"
                                        value="{{ $settings['feature2_description']->value ?? 'We prioritize safety with well-maintained vessels and trained staff' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-4">Feature 3</h3>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <label for="feature3_icon" class="block text-sm font-medium form-label">
                                    <i class="fas fa-icons"></i> Icon
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature3_icon" id="feature3_icon"
                                        value="{{ $settings['feature3_icon']->value ?? 'fas fa-ticket-alt' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Font Awesome icon class
                                </p>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="feature3_title" class="block text-sm font-medium form-label">
                                    <i class="fas fa-heading"></i> Title
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature3_title" id="feature3_title"
                                        value="{{ $settings['feature3_title']->value ?? 'Easy Booking' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="feature3_description" class="block text-sm font-medium form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature3_description" id="feature3_description"
                                        value="{{ $settings['feature3_description']->value ?? 'Simple online booking system for tickets with instant confirmation' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-4">Feature 4</h3>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <label for="feature4_icon" class="block text-sm font-medium form-label">
                                    <i class="fas fa-icons"></i> Icon
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature4_icon" id="feature4_icon"
                                        value="{{ $settings['feature4_icon']->value ?? 'fas fa-wallet' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Font Awesome icon class
                                </p>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="feature4_title" class="block text-sm font-medium form-label">
                                    <i class="fas fa-heading"></i> Title
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature4_title" id="feature4_title"
                                        value="{{ $settings['feature4_title']->value ?? 'Affordable Rates' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="feature4_description" class="block text-sm font-medium form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="feature4_description" id="feature4_description"
                                        value="{{ $settings['feature4_description']->value ?? 'Competitive pricing with special discounts for regular travelers' }}"
                                        class="form-input shadow-sm block w-full sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-6 mt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-end items-center space-y-3 sm:space-y-0">
                        <button type="submit" class="w-full sm:w-auto save-button inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Save Features Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Additional section tabs would be added here -->
        <div x-show="activeSection === 'howto'" class="settings-card bg-white rounded-xl overflow-hidden">
            <!-- How to Book section content -->
            <div class="card-header">
                <div class="header-icon bg-purple-100 text-purple-600">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 class="card-title">How to Book Section</h3>
                    <p class="card-subtitle">Customize the booking instructions section of your landing page.</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <form action="{{ route('admin.settings.update-howto') }}" method="POST">
                    @csrf
                    <!-- Section content form fields -->
                    <!-- Form Actions -->
                    <div class="pt-6 mt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-end items-center space-y-3 sm:space-y-0">
                        <button type="submit" class="w-full sm:w-auto save-button inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Save How to Book Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="activeSection === 'about'" class="settings-card bg-white rounded-xl overflow-hidden">
            <!-- About Us section content -->
            <div class="card-header">
                <div class="header-icon bg-yellow-100 text-yellow-600">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h3 class="card-title">About Us Section</h3>
                    <p class="card-subtitle">Customize the about us section of your landing page.</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <form action="{{ route('admin.settings.update-about') }}" method="POST">
                    @csrf
                    <!-- Form content here -->
                    <!-- Form Actions -->
                    <div class="pt-6 mt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-end items-center space-y-3 sm:space-y-0">
                        <button type="submit" class="w-full sm:w-auto save-button inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Save About Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="activeSection === 'footer'" class="settings-card bg-white rounded-xl overflow-hidden">
            <!-- Footer section content -->
            <div class="card-header">
                <div class="header-icon bg-gray-100 text-gray-600">
                    <i class="fas fa-shoe-prints"></i>
                </div>
                <div>
                    <h3 class="card-title">Footer Section</h3>
                    <p class="card-subtitle">Customize the footer section of your landing page.</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <form action="{{ route('admin.settings.update-footer') }}" method="POST">
                    @csrf
                    <!-- Form content here -->
                    <!-- Form Actions -->
                    <div class="pt-6 mt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-end items-center space-y-3 sm:space-y-0">
                        <button type="submit" class="w-full sm:w-auto save-button inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Save Footer Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="activeSection === 'seo'" class="settings-card bg-white rounded-xl overflow-hidden">
            <!-- SEO settings content -->
            <div class="card-header">
                <div class="header-icon bg-red-100 text-red-600">
                    <i class="fas fa-search"></i>
                </div>
                <div>
                    <h3 class="card-title">SEO Settings</h3>
                    <p class="card-subtitle">Customize the SEO settings for your landing page.</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <form action="{{ route('admin.settings.update-seo') }}" method="POST">
                    @csrf
                    <!-- Form content here -->
                    <!-- Form Actions -->
                    <div class="pt-6 mt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-end items-center space-y-3 sm:space-y-0">
                        <button type="submit" class="w-full sm:w-auto save-button inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Save SEO Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="activeSection === 'routes'" class="settings-card bg-white rounded-xl overflow-hidden">
            <!-- Routes section content -->
            <div class="card-header">
                <div class="header-icon bg-teal-100 text-teal-600">
                    <i class="fas fa-route"></i>
                </div>
                <div>
                    <h3 class="card-title">Routes Section</h3>
                    <p class="card-subtitle">This section is managed through the Routes module.</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <div class="text-center py-8">
                    <i class="fas fa-link text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Routes are managed separately</h3>
                    <p class="text-gray-600 max-w-md mx-auto mb-6">
                        Popular routes displayed on the landing page are managed through the Routes module where you can set which routes should be featured.
                    </p>
                    <a href="{{ route('admin.routes.index') }}" class="inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                        <i class="fas fa-external-link-alt mr-2"></i> Go to Routes Management
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Image Preview -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hero image preview
        const heroImageInput = document.getElementById('hero_image');
        const heroImagePreview = document.getElementById('hero_image_preview');

        if (heroImageInput && heroImagePreview) {
            heroImageInput.addEventListener('change', function() {
                heroImagePreview.src = this.value;
            });
        }
    });
</script>
@endsection
