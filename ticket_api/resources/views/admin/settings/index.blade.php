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

    /* Enhanced Toggle Switches */
    .toggle-wrapper {
        position: relative;
        display: inline-block;
        width: 3rem;
        height: 1.5rem;
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
        border-radius: 1.5rem;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 1.125rem;
        width: 1.125rem;
        left: 0.1875rem;
        bottom: 0.1875rem;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .toggle-input:checked + .toggle-slider {
        background-color: #4f46e5;
    }
    .toggle-input:checked + .toggle-slider:before {
        transform: translateX(1.5rem);
    }
    .toggle-input:focus + .toggle-slider {
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    /* Responsive Styles */
    @media (max-width: 640px) {
        .tab-button {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            font-size: 0.875rem;
        }
        .section-title {
            font-size: 1rem;
        }
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
</style>
@endsection

@section('content')
<div x-data="{ activeTab: 'system' }" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Enhanced Tab Navigation -->
    <div class="mb-8 border-b border-gray-200 overflow-x-auto hide-scrollbar">
        <div class="flex -mb-px space-x-6 sm:space-x-8">
            <button @click="activeTab = 'system'"
                class="tab-button py-4 px-2 text-sm font-medium text-center flex items-center focus:outline-none whitespace-nowrap"
                :class="activeTab === 'system' ? 'active text-indigo-600' : 'text-gray-500 hover:text-gray-700'">
                <i class="fas fa-cog mr-2 text-lg"></i> <span>System Settings</span>
            </button>
            <a href="{{ route('admin.settings.profile') }}"
                class="tab-button py-4 px-2 text-sm font-medium text-center flex items-center text-gray-500 hover:text-gray-700 focus:outline-none whitespace-nowrap">
                <i class="fas fa-user mr-2 text-lg"></i> <span>Profile Settings</span>
            </a>
        </div>
    </div>

    <!-- Enhanced System Settings Tab -->
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

                            <div class="sm:col-span-3">
                                <label for="payment_gateway" class="block text-sm font-medium form-label">
                                    <i class="fas fa-credit-card"></i> Payment Gateway
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-credit-card text-gray-400"></i>
                                    </div>
                                    <select id="payment_gateway" name="payment_gateway"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 appearance-none py-2.5">
                                        <option value="stripe" {{ ($settings['payment_gateway']->value ?? 'stripe') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                                        <option value="paypal" {{ ($settings['payment_gateway']->value ?? '') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                        <option value="midtrans" {{ ($settings['payment_gateway']->value ?? '') == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Default payment processor for transactions.
                                </p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="receipt_prefix" class="block text-sm font-medium form-label">
                                    <i class="fas fa-file-invoice"></i> Receipt Prefix
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-file-invoice text-gray-400"></i>
                                    </div>
                                    <input type="text" name="receipt_prefix" id="receipt_prefix"
                                        value="{{ $settings['receipt_prefix']->value ?? 'FTX-' }}" maxlength="10"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Prefix for receipt numbers (e.g., FTX-00001).
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Email Configuration Section -->
                    <div class="mt-10 pt-6 border-t border-gray-200">
                        <h3 class="section-title">
                            <i class="fas fa-envelope"></i> Email Configuration
                        </h3>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6 mt-6">
                            <div class="sm:col-span-3">
                                <label for="email_sender_name" class="block text-sm font-medium form-label">
                                    <i class="fas fa-user"></i> Sender Name
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" name="email_sender_name" id="email_sender_name"
                                        value="{{ $settings['email_sender_name']->value ?? 'Ferry Ticket System' }}"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Name displayed in the "From" field of emails.
                                </p>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="email_footer_text" class="block text-sm font-medium form-label">
                                    <i class="fas fa-paragraph"></i> Email Footer Text
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-paragraph text-gray-400"></i>
                                    </div>
                                    <input type="text" name="email_footer_text" id="email_footer_text"
                                        value="{{ $settings['email_footer_text']->value ?? '© '.date('Y').' Ferry Ticket System. All rights reserved.' }}"
                                        class="form-input shadow-sm block w-full pl-10 sm:text-sm border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 py-2.5">
                                </div>
                                <p class="form-description">
                                    <i class="fas fa-info-circle"></i> Text displayed at the bottom of all emails.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box Section -->
                    <div class="info-box">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="info-icon">
                                    <i class="fas fa-info"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-blue-800">Changes take effect immediately</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>All changes to system settings will be applied immediately after saving. Make sure your changes won't disrupt ongoing operations.</p>
                                </div>
                                <div class="mt-3">
                                    <a href="#" class="text-blue-700 hover:text-blue-900 font-medium text-sm inline-flex items-center rounded-md px-2.5 py-1.5 hover:bg-blue-100 transition-colors">
                                        <i class="fas fa-book mr-1.5"></i>
                                        Read documentation
                                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                    </a>
                                </div>
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

        <!-- Advanced Settings Card -->
        <div class="settings-card bg-white rounded-xl overflow-hidden">
            <div class="card-header">
                <div class="header-icon bg-purple-100 text-purple-600">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <h3 class="card-title">Advanced Settings</h3>
                    <p class="card-subtitle">Configure advanced system settings and behaviors.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="space-y-6">
                    <!-- Toggle Item 1 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Enable maintenance mode</h4>
                            <p class="text-xs text-gray-500 mt-1">When enabled, the site will be inaccessible to regular users.</p>
                        </div>
                        <div class="toggle-wrapper">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" class="toggle-input" />
                            <label for="maintenance_mode" class="toggle-slider"></label>
                        </div>
                    </div>

                    <!-- Toggle Item 2 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Enable debug mode</h4>
                            <p class="text-xs text-gray-500 mt-1">Show detailed error messages (not recommended for production).</p>
                        </div>
                        <div class="toggle-wrapper">
                            <input type="checkbox" id="debug_mode" name="debug_mode" class="toggle-input" />
                            <label for="debug_mode" class="toggle-slider"></label>
                        </div>
                    </div>

                    <!-- Toggle Item 3 -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Allow user registrations</h4>
                            <p class="text-xs text-gray-500 mt-1">Let visitors create new accounts on your site.</p>
                        </div>
                        <div class="toggle-wrapper">
                            <input type="checkbox" id="allow_registrations" name="allow_registrations" checked class="toggle-input" />
                            <label for="allow_registrations" class="toggle-slider"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
