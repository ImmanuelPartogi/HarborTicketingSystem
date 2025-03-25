{{-- resources/views/admin/settings/howto.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'How to Book Section Settings')
@section('header', 'How to Book Section Settings')

@section('styles')
<style>
    /* Card styling */
    .settings-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(229, 231, 235, 0.5);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .settings-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: rgba(209, 213, 219, 0.8);
    }
    /* Form elements */
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
    /* Card header */
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
    /* Step card */
    .step-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background-color: #f9fafb;
        transition: all 0.2s ease;
    }
    .step-card:hover {
        background-color: #f3f4f6;
        border-color: #d1d5db;
    }
    .step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        background-color: #4338ca;
        color: white;
        font-weight: 600;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">How to Book Section</h2>
            <p class="mt-1 text-sm text-gray-500">
                Customize the booking process instructions displayed on your landing page.
            </p>
        </div>
        <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left -ml-1 mr-2 h-5 w-5 text-gray-500"></i>
            Back to Settings
        </a>
    </div>

    <div class="settings-card bg-white rounded-xl overflow-hidden">
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
                <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="howto_title" class="form-label">
                            <i class="fas fa-heading"></i> Section Title
                        </label>
                        <input type="text" name="howto_title" id="howto_title"
                               value="{{ $settings['howto_title']->value ?? 'How to Book Your Ferry Ticket' }}"
                               class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="howto_subtitle" class="form-label">
                            <i class="fas fa-align-left"></i> Section Subtitle
                        </label>
                        <input type="text" name="howto_subtitle" id="howto_subtitle"
                               value="{{ $settings['howto_subtitle']->value ?? 'Follow these simple steps to book your journey' }}"
                               class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Step 1 -->
                <div class="step-card mt-8">
                    <div class="step-number">1</div>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="step1_icon" class="form-label">
                                <i class="fas fa-icons"></i> Step 1 Icon
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    <i class="fas fa-fw"></i>
                                </span>
                                <input type="text" name="step1_icon" id="step1_icon"
                                       value="{{ $settings['step1_icon']->value ?? 'fas fa-search' }}"
                                       class="form-input flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="sm:col-span-4">
                            <label for="step1_title" class="form-label">
                                <i class="fas fa-heading"></i> Step 1 Title
                            </label>
                            <input type="text" name="step1_title" id="step1_title"
                                   value="{{ $settings['step1_title']->value ?? 'Search Routes' }}"
                                   class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-6">
                            <label for="step1_description" class="form-label">
                                <i class="fas fa-align-left"></i> Step 1 Description
                            </label>
                            <textarea name="step1_description" id="step1_description" rows="2"
                                      class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['step1_description']->value ?? 'Enter your origin, destination, and travel date to find available ferries.' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="step2_icon" class="form-label">
                                <i class="fas fa-icons"></i> Step 2 Icon
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    <i class="fas fa-fw"></i>
                                </span>
                                <input type="text" name="step2_icon" id="step2_icon"
                                       value="{{ $settings['step2_icon']->value ?? 'fas fa-calendar-alt' }}"
                                       class="form-input flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="sm:col-span-4">
                            <label for="step2_title" class="form-label">
                                <i class="fas fa-heading"></i> Step 2 Title
                            </label>
                            <input type="text" name="step2_title" id="step2_title"
                                   value="{{ $settings['step2_title']->value ?? 'Select Schedule' }}"
                                   class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-6">
                            <label for="step2_description" class="form-label">
                                <i class="fas fa-align-left"></i> Step 2 Description
                            </label>
                            <textarea name="step2_description" id="step2_description" rows="2"
                                      class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['step2_description']->value ?? 'Choose from available schedules and ferry types that suit your needs.' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="step3_icon" class="form-label">
                                <i class="fas fa-icons"></i> Step 3 Icon
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    <i class="fas fa-fw"></i>
                                </span>
                                <input type="text" name="step3_icon" id="step3_icon"
                                       value="{{ $settings['step3_icon']->value ?? 'fas fa-credit-card' }}"
                                       class="form-input flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="sm:col-span-4">
                            <label for="step3_title" class="form-label">
                                <i class="fas fa-heading"></i> Step 3 Title
                            </label>
                            <input type="text" name="step3_title" id="step3_title"
                                   value="{{ $settings['step3_title']->value ?? 'Make Payment' }}"
                                   class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-6">
                            <label for="step3_description" class="form-label">
                                <i class="fas fa-align-left"></i> Step 3 Description
                            </label>
                            <textarea name="step3_description" id="step3_description" rows="2"
                                      class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['step3_description']->value ?? 'Secure payment via multiple options including credit card and mobile banking.' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="step4_icon" class="form-label">
                                <i class="fas fa-icons"></i> Step 4 Icon
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    <i class="fas fa-fw"></i>
                                </span>
                                <input type="text" name="step4_icon" id="step4_icon"
                                       value="{{ $settings['step4_icon']->value ?? 'fas fa-qrcode' }}"
                                       class="form-input flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="sm:col-span-4">
                            <label for="step4_title" class="form-label">
                                <i class="fas fa-heading"></i> Step 4 Title
                            </label>
                            <input type="text" name="step4_title" id="step4_title"
                                   value="{{ $settings['step4_title']->value ?? 'Get E-Ticket' }}"
                                   class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-6">
                            <label for="step4_description" class="form-label">
                                <i class="fas fa-align-left"></i> Step 4 Description
                            </label>
                            <textarea name="step4_description" id="step4_description" rows="2"
                                      class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['step4_description']->value ?? 'Receive your e-ticket instantly via email or download from your account.' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Live Preview</h3>
                    <div class="flex items-center justify-center space-x-4">
                        @for ($i = 1; $i <= 4; $i++)
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 text-indigo-600 mb-2">
                                    <i class="{{ $settings['step'.$i.'_icon']->value ?? 'fas fa-check' }} text-lg"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-900">{{ $settings['step'.$i.'_title']->value ?? 'Step '.$i }}</p>
                            </div>
                            @if ($i < 4)
                                <div class="h-0.5 w-8 bg-gray-300"></div>
                            @endif
                        @endfor
                    </div>
                </div>

                <div class="pt-8 mt-6 border-t border-gray-200 flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Live preview of icons
    document.addEventListener('DOMContentLoaded', function() {
        const iconInputs = document.querySelectorAll('input[id^="step"][id$="_icon"]');

        iconInputs.forEach((input, index) => {
            const previewIcon = document.querySelectorAll('.preview-icon')[index];
            const step = index + 1;

            input.addEventListener('input', function() {
                const iconSpan = input.previousElementSibling;
                const icon = input.value;

                // Update the icon in the input field's prefix
                iconSpan.innerHTML = `<i class="${icon}"></i>`;

                // Update the preview section
                const previewIcons = document.querySelectorAll('.bg-indigo-100 i');
                if (previewIcons && previewIcons[index]) {
                    previewIcons[index].className = icon + ' text-lg';
                }
            });
        });
    });
</script>
@endsection
