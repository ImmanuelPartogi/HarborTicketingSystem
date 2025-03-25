{{-- resources/views/admin/settings/seo.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'SEO Settings')
@section('header', 'SEO Settings')

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
    /* Image preview */
    .image-preview-container {
        width: 100%;
        height: 200px;
        overflow: hidden;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
    }
    .image-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    /* Hint text */
    .hint-text {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.375rem;
    }
    /* Meta preview */
    .meta-preview {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1.5rem;
        background-color: #f9fafb;
        margin-top: 1.5rem;
    }
    .meta-title {
        color: #1e40af;
        font-size: 1.125rem;
        text-decoration: underline;
        margin-bottom: 0.25rem;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .meta-url {
        color: #059669;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .meta-description {
        color: #4b5563;
        font-size: 0.875rem;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">SEO Settings</h2>
            <p class="mt-1 text-sm text-gray-500">
                Configure search engine optimization settings for your landing page.
            </p>
        </div>
        <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left -ml-1 mr-2 h-5 w-5 text-gray-500"></i>
            Back to Settings
        </a>
    </div>

    <div class="settings-card bg-white rounded-xl overflow-hidden">
        <div class="card-header">
            <div class="header-icon bg-red-100 text-red-600">
                <i class="fas fa-search"></i>
            </div>
            <div>
                <h3 class="card-title">SEO Settings</h3>
                <p class="card-subtitle">Optimize your landing page for search engines.</p>
            </div>
        </div>
        <div class="p-6 sm:p-8">
            <form action="{{ route('admin.settings.update-seo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        <label for="site_name" class="form-label">
                            <i class="fas fa-globe"></i> Site Name
                        </label>
                        <input type="text" name="site_name" id="site_name"
                               value="{{ $settings['site_name']->value ?? 'Ferry Ticket System' }}"
                               class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="hint-text">
                            <i class="fas fa-info-circle"></i> The name of your website, will be displayed in the title tag and throughout the site.
                        </p>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="meta_description" class="form-label">
                            <i class="fas fa-align-left"></i> Meta Description
                        </label>
                        <textarea name="meta_description" id="meta_description" rows="3"
                                  class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['meta_description']->value ?? 'Book your ferry tickets online for a seamless travel experience across Indonesia. Safe, convenient, and affordable sea transportation.' }}</textarea>
                        <p class="hint-text">
                            <i class="fas fa-info-circle"></i> A brief description of your website (150-160 characters). This appears in search engine results.
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span id="description-counter" class="{{ strlen($settings['meta_description']->value ?? '') > 160 ? 'text-red-500' : 'text-gray-500' }}">
                                {{ strlen($settings['meta_description']->value ?? '') }}/160
                            </span> characters
                        </p>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="meta_keywords" class="form-label">
                            <i class="fas fa-tags"></i> Meta Keywords
                        </label>
                        <input type="text" name="meta_keywords" id="meta_keywords"
                               value="{{ $settings['meta_keywords']->value ?? 'ferry tickets, sea travel, Indonesia ferry, online booking, boat tickets' }}"
                               class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="hint-text">
                            <i class="fas fa-info-circle"></i> Comma-separated keywords relevant to your website.
                        </p>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="og_image" class="form-label">
                            <i class="fas fa-image"></i> Social Media Image
                        </label>
                        <input type="text" name="og_image" id="og_image"
                               value="{{ $settings['og_image']->value ?? asset('images/og-image.jpg') }}"
                               class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                        <div class="mt-2">
                            <span class="text-sm text-gray-500 mr-2">OR</span>
                            <label for="og_image_upload" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                                <i class="fas fa-upload mr-2"></i> Upload New Image
                                <input type="file" name="og_image_upload" id="og_image_upload" accept="image/*" class="sr-only">
                            </label>
                        </div>

                        <div class="image-preview-container mt-3 border border-gray-200">
                            <img id="og_image_preview" src="{{ $settings['og_image']->value ?? asset('images/og-image.jpg') }}"
                                 alt="Social Media Image Preview" class="image-preview">
                        </div>
                        <p class="hint-text">
                            <i class="fas fa-info-circle"></i> Image displayed when your site is shared on social media (recommended size: 1200x630 pixels).
                        </p>
                    </div>
                </div>

                <!-- Google Search Preview -->
                <div class="mt-10 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Google Search Preview</h3>
                    <div class="meta-preview">
                        <a href="#" class="meta-title" id="google-title">{{ $settings['site_name']->value ?? 'Ferry Ticket System' }}</a>
                        <span class="meta-url" id="google-url">{{ url('/') }}</span>
                        <p class="meta-description" id="google-description">{{ $settings['meta_description']->value ?? 'Book your ferry tickets online for a seamless travel experience across Indonesia. Safe, convenient, and affordable sea transportation.' }}</p>
                    </div>
                </div>

                <!-- Facebook/Twitter Preview -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Social Media Preview</h3>
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div id="social-image-container" class="w-full h-64 bg-gray-100 flex items-center justify-center">
                            <img id="social-image" src="{{ $settings['og_image']->value ?? asset('images/og-image.jpg') }}" alt="Social Media Preview" class="max-w-full max-h-full">
                        </div>
                        <div class="p-4">
                            <div id="social-title" class="text-lg font-semibold text-gray-900">{{ $settings['site_name']->value ?? 'Ferry Ticket System' }}</div>
                            <div id="social-description" class="mt-1 text-sm text-gray-600">{{ $settings['meta_description']->value ?? 'Book your ferry tickets online for a seamless travel experience across Indonesia. Safe, convenient, and affordable sea transportation.' }}</div>
                            <div id="social-url" class="mt-2 text-xs text-gray-500">{{ url('/') }}</div>
                        </div>
                    </div>
                </div>

                <div class="pt-8 mt-6 border-t border-gray-200 flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-save mr-2"></i> Save SEO Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update character counter for meta description
        const metaDescription = document.getElementById('meta_description');
        const descriptionCounter = document.getElementById('description-counter');

        metaDescription.addEventListener('input', function() {
            const length = this.value.length;
            descriptionCounter.textContent = length + '/160';

            if (length > 160) {
                descriptionCounter.classList.add('text-red-500');
                descriptionCounter.classList.remove('text-gray-500');
            } else {
                descriptionCounter.classList.remove('text-red-500');
                descriptionCounter.classList.add('text-gray-500');
            }

            // Update previews
            document.getElementById('google-description').textContent = this.value;
            document.getElementById('social-description').textContent = this.value;
        });

        // Update site name in previews
        const siteName = document.getElementById('site_name');
        siteName.addEventListener('input', function() {
            document.getElementById('google-title').textContent = this.value;
            document.getElementById('social-title').textContent = this.value;
        });

        // Image upload preview
        const ogImageUpload = document.getElementById('og_image_upload');
        const ogImagePreview = document.getElementById('og_image_preview');
        const socialImage = document.getElementById('social-image');

        ogImageUpload.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    ogImagePreview.src = e.target.result;
                    socialImage.src = e.target.result;

                    // Clear the URL input since we're uploading a new image
                    document.getElementById('og_image').value = '';
                }

                reader.readAsDataURL(this.files[0]);
            }
        });

        // URL input update for preview
        const ogImageUrl = document.getElementById('og_image');
        ogImageUrl.addEventListener('input', function() {
            if (this.value) {
                ogImagePreview.src = this.value;
                socialImage.src = this.value;
            }
        });
    });
</script>
@endsection
