@extends('admin.layouts.app')

@section('title', 'System Settings')
@section('header', 'System Settings')

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
</style>
@endsection

@section('content')
<div x-data="{ activeTab: 'system' }">
    <!-- Tab Navigation -->
    <div class="mb-6 border-b border-gray-200">
        <div class="flex flex-wrap -mb-px">
            <button @click="activeTab = 'system'"
                class="tab-button mr-2 inline-block py-4 px-4 text-sm font-medium text-center border-b-2"
                :class="activeTab === 'system' ? 'active border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                <i class="fas fa-cog mr-2"></i> System Settings
            </button>
            <a href="{{ route('admin.settings.profile') }}"
                class="tab-button mr-2 inline-block py-4 px-4 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-user mr-2"></i> Profile Settings
            </a>
        </div>
    </div>

    <!-- System Settings Tab -->
    <div x-show="activeTab === 'system'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        <div class="settings-card bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">General Settings</h3>
                <p class="mt-1 text-sm text-gray-500">Configure the general settings for your ferry ticket system.</p>
            </div>

            <div class="p-6">
                <form action="{{ route('admin.settings.update-system') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                            <div class="mt-1">
                                <input type="text" name="site_name" id="site_name"
                                    value="{{ $settings['site_name']->value ?? 'Ferry Ticket System' }}"
                                    class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">This will be displayed in browser tabs and email notifications.</p>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                            <div class="mt-1">
                                <input type="email" name="contact_email" id="contact_email"
                                    value="{{ $settings['contact_email']->value ?? 'contact@ferryticket.com' }}"
                                    class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">This email will be used for system notifications and customer support.</p>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="phone_number" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <div class="mt-1">
                                <input type="text" name="phone_number" id="phone_number"
                                    value="{{ $settings['phone_number']->value ?? '' }}"
                                    class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Customer support phone number (optional).</p>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="booking_expiry_hours" class="block text-sm font-medium text-gray-700">Booking Expiry Hours</label>
                            <div class="mt-1">
                                <input type="number" name="booking_expiry_hours" id="booking_expiry_hours"
                                    value="{{ $settings['booking_expiry_hours']->value ?? '24' }}" min="1" max="72"
                                    class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Number of hours before an unpaid booking expires (1-72).</p>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900">Payment Settings</h3>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6 mt-4">
                            <div class="sm:col-span-3">
                                <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                                <div class="mt-1">
                                    <select id="currency" name="currency" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="IDR" {{ ($settings['currency']->value ?? 'IDR') == 'IDR' ? 'selected' : '' }}>Indonesian Rupiah (IDR)</option>
                                        <option value="USD" {{ ($settings['currency']->value ?? '') == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                                        <option value="EUR" {{ ($settings['currency']->value ?? '') == 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                                        <option value="SGD" {{ ($settings['currency']->value ?? '') == 'SGD' ? 'selected' : '' }}>Singapore Dollar (SGD)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="tax_percentage" class="block text-sm font-medium text-gray-700">Tax Percentage (%)</label>
                                <div class="mt-1">
                                    <input type="number" step="0.01" name="tax_percentage" id="tax_percentage"
                                        value="{{ $settings['tax_percentage']->value ?? '10' }}" min="0" max="100"
                                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-gray-50 border rounded-md p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Changes take effect immediately</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>All changes to system settings will be applied immediately after saving.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 text-right">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-save mr-2"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
