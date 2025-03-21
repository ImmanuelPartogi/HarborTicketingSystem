@extends('admin.layouts.app')

@section('title', 'System Settings')
@section('header', 'System Settings')

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
    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #1f2937;
        display: flex;
        align-items: center;
    }
    .section-title i {
        margin-right: 0.5rem;
        color: #4f46e5;
    }
    .save-button {
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    .save-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .save-button:active {
        transform: translateY(1px);
    }
    .form-description {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    @media (max-width: 640px) {
        .tab-button {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }
</style>
@endsection

@section('content')
<div x-data="{ activeTab: 'system' }" class="max-w-6xl mx-auto">
    <!-- Tab Navigation - Enhanced -->
    <div class="mb-8 border-b border-gray-200 overflow-x-auto hide-scrollbar">
        <div class="flex -mb-px space-x-8 px-2">
            <button @click="activeTab = 'system'"
                class="tab-button py-4 px-1 text-sm font-medium text-center border-b-2 border-transparent focus:outline-none whitespace-nowrap"
                :class="activeTab === 'system' ? 'active text-primary-600' : 'text-gray-500 hover:text-gray-700'">
                <i class="fas fa-cog mr-2"></i> System Settings
            </button>
            <a href="{{ route('admin.settings.profile') }}"
                class="tab-button py-4 px-1 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-user mr-2"></i> Profile Settings
            </a>
            <a href="#"
                class="tab-button py-4 px-1 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-bell mr-2"></i> Notification Settings
            </a>
            <a href="#"
                class="tab-button py-4 px-1 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-database mr-2"></i> Backup & Logs
            </a>
        </div>
    </div>

    <!-- System Settings Tab - Enhanced -->
    <div x-show="activeTab === 'system'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-8">
        <div class="settings-card bg-white shadow-sm rounded-xl overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg mr-3">
                        <i class="fas fa-wrench"></i>
                    </span>
                    General Settings
                </h3>
                <p class="mt-1 text-sm text-gray-500">Configure the general settings for your ferry ticket system.</p>
            </div>

            <div class="p-6">
                <form action="{{ route('admin.settings.update-system') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="site_name" class="block text-sm font-medium text-gray-700 form-label">
                                Site Name
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-globe text-gray-400"></i>
                                </div>
                                <input type="text" name="site_name" id="site_name"
                                    value="{{ $settings['site_name']->value ?? 'Ferry Ticket System' }}"
                                    class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <p class="form-description">This will be displayed in browser tabs and email notifications.</p>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 form-label">
                                Contact Email
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="contact_email" id="contact_email"
                                    value="{{ $settings['contact_email']->value ?? 'contact@ferryticket.com' }}"
                                    class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <p class="form-description">This email will be used for system notifications and customer support.</p>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 form-label">
                                Contact Phone
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone-alt text-gray-400"></i>
                                </div>
                                <input type="text" name="phone_number" id="phone_number"
                                    value="{{ $settings['phone_number']->value ?? '' }}"
                                    class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <p class="form-description">Customer support phone number (optional).</p>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="booking_expiry_hours" class="block text-sm font-medium text-gray-700 form-label">
                                Booking Expiry Hours
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-hourglass-half text-gray-400"></i>
                                </div>
                                <input type="number" name="booking_expiry_hours" id="booking_expiry_hours"
                                    value="{{ $settings['booking_expiry_hours']->value ?? '24' }}" min="1" max="72"
                                    class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <label for="hours" class="sr-only">Hours</label>
                                    <div class="px-3 py-2 rounded-r-md bg-gray-50 text-gray-500 sm:text-sm border-l border-gray-300">
                                        Hours
                                    </div>
                                </div>
                            </div>
                            <p class="form-description">Number of hours before an unpaid booking expires (1-72).</p>
                        </div>
                    </div>

                    <div class="mt-8 pt-5 border-t border-gray-200">
                        <h3 class="section-title">
                            <i class="fas fa-credit-card"></i> Payment Settings
                        </h3>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6 mt-4">
                            <div class="sm:col-span-3">
                                <label for="currency" class="block text-sm font-medium text-gray-700 form-label">
                                    Currency
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-money-bill-wave text-gray-400"></i>
                                    </div>
                                    <select id="currency" name="currency"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500 appearance-none">
                                        <option value="IDR" {{ ($settings['currency']->value ?? 'IDR') == 'IDR' ? 'selected' : '' }}>Indonesian Rupiah (IDR)</option>
                                        <option value="USD" {{ ($settings['currency']->value ?? '') == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                                        <option value="EUR" {{ ($settings['currency']->value ?? '') == 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                                        <option value="SGD" {{ ($settings['currency']->value ?? '') == 'SGD' ? 'selected' : '' }}>Singapore Dollar (SGD)</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="form-description">Primary currency for all transactions.</p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="tax_percentage" class="block text-sm font-medium text-gray-700 form-label">
                                    Tax Percentage (%)
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-percentage text-gray-400"></i>
                                    </div>
                                    <input type="number" step="0.01" name="tax_percentage" id="tax_percentage"
                                        value="{{ $settings['tax_percentage']->value ?? '10' }}" min="0" max="100"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                                    <div class="absolute inset-y-0 right-0 flex items-center">
                                        <label for="percentage" class="sr-only">Percentage</label>
                                        <div class="px-3 py-2 rounded-r-md bg-gray-50 text-gray-500 sm:text-sm border-l border-gray-300">
                                            %
                                        </div>
                                    </div>
                                </div>
                                <p class="form-description">Tax rate applied to all bookings.</p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="payment_gateway" class="block text-sm font-medium text-gray-700 form-label">
                                    Payment Gateway
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-credit-card text-gray-400"></i>
                                    </div>
                                    <select id="payment_gateway" name="payment_gateway"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500 appearance-none">
                                        <option value="stripe" {{ ($settings['payment_gateway']->value ?? 'stripe') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                                        <option value="paypal" {{ ($settings['payment_gateway']->value ?? '') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                        <option value="midtrans" {{ ($settings['payment_gateway']->value ?? '') == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="form-description">Default payment processor for transactions.</p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="receipt_prefix" class="block text-sm font-medium text-gray-700 form-label">
                                    Receipt Prefix
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-file-invoice text-gray-400"></i>
                                    </div>
                                    <input type="text" name="receipt_prefix" id="receipt_prefix"
                                        value="{{ $settings['receipt_prefix']->value ?? 'FTX-' }}" maxlength="10"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <p class="form-description">Prefix for receipt numbers (e.g., FTX-00001).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Email Configuration Section -->
                    <div class="mt-8 pt-5 border-t border-gray-200">
                        <h3 class="section-title">
                            <i class="fas fa-envelope"></i> Email Configuration
                        </h3>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6 mt-4">
                            <div class="sm:col-span-3">
                                <label for="email_sender_name" class="block text-sm font-medium text-gray-700 form-label">
                                    Sender Name
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" name="email_sender_name" id="email_sender_name"
                                        value="{{ $settings['email_sender_name']->value ?? 'Ferry Ticket System' }}"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <p class="form-description">Name displayed in the "From" field of emails.</p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="email_footer_text" class="block text-sm font-medium text-gray-700 form-label">
                                    Email Footer Text
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-paragraph text-gray-400"></i>
                                    </div>
                                    <input type="text" name="email_footer_text" id="email_footer_text"
                                        value="{{ $settings['email_footer_text']->value ?? '© '.date('Y').' Ferry Ticket System. All rights reserved.' }}"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <p class="form-description">Text displayed at the bottom of all emails.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-5">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-0.5">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-info"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-blue-800">Changes take effect immediately</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>All changes to system settings will be applied immediately after saving. Make sure your changes won't disrupt ongoing operations.</p>
                                </div>
                                <div class="mt-3 flex items-center">
                                    <a href="#" class="text-blue-700 hover:text-blue-900 font-medium text-sm inline-flex items-center">
                                        <i class="fas fa-book mr-1.5"></i>
                                        Read documentation
                                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-between items-center">
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                            <i class="fas fa-undo mr-2"></i> Reset to Defaults
                        </button>

                        <button type="submit" class="save-button inline-flex justify-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Advanced Settings Card -->
        <div class="settings-card bg-white shadow-sm rounded-xl overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 bg-purple-100 text-purple-600 rounded-lg mr-3">
                        <i class="fas fa-sliders-h"></i>
                    </span>
                    Advanced Settings
                </h3>
                <p class="mt-1 text-sm text-gray-500">Configure advanced system settings and behaviors.</p>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Enable maintenance mode</h4>
                            <p class="text-xs text-gray-500 mt-1">When enabled, the site will be inaccessible to regular users.</p>
                        </div>
                        <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" class="absolute w-6 h-6 opacity-0 z-10 cursor-pointer" />
                            <label for="maintenance_mode" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            <span class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Enable debug mode</h4>
                            <p class="text-xs text-gray-500 mt-1">Show detailed error messages (not recommended for production).</p>
                        </div>
                        <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out">
                            <input type="checkbox" id="debug_mode" name="debug_mode" class="absolute w-6 h-6 opacity-0 z-10 cursor-pointer" />
                            <label for="debug_mode" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            <span class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Allow user registrations</h4>
                            <p class="text-xs text-gray-500 mt-1">Let visitors create new accounts on your site.</p>
                        </div>
                        <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out">
                            <input type="checkbox" id="allow_registrations" name="allow_registrations" checked class="absolute w-6 h-6 opacity-0 z-10 cursor-pointer" />
                            <label for="allow_registrations" class="toggle-label block overflow-hidden h-6 rounded-full bg-primary-500 cursor-pointer"></label>
                            <span class="toggle-dot absolute left-7 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300"></span>
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
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection
