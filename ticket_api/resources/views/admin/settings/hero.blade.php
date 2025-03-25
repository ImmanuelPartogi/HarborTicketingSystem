{{-- resources/views/admin/settings/hero.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Hero Section Settings')
@section('header', 'Hero Section Settings')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Hero Section</h2>
            <p class="mt-1 text-sm text-gray-500">
                Customize the main banner section at the top of your landing page.
            </p>
        </div>
        <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left -ml-1 mr-2 h-5 w-5 text-gray-500"></i>
            Back to Settings
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">
                    {{ session('error') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Hero Section Settings
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Edit the content of the main banner section.
            </p>
        </div>

        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('admin.settings.update-hero') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="hero_title" class="block text-sm font-medium text-gray-700">
                            Hero Title
                        </label>
                        <div class="mt-1">
                            <input type="text" name="hero_title" id="hero_title"
                                value="{{ $settings['hero_title']->value ?? 'Explore the Sea with Our Ferry Service' }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            The main heading displayed in the hero section.
                        </p>
                        @error('hero_title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="hero_subtitle" class="block text-sm font-medium text-gray-700">
                            Hero Subtitle
                        </label>
                        <div class="mt-1">
                            <textarea name="hero_subtitle" id="hero_subtitle" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ $settings['hero_subtitle']->value ?? 'Book your ferry tickets online for a seamless travel experience. Safe, convenient, and affordable sea transportation to your destination.' }}</textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Descriptive text displayed below the hero title.
                        </p>
                        @error('hero_subtitle')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Hero Background Image
                        </label>

                        <!-- Current image preview -->
                        <div class="mt-2 relative rounded-lg overflow-hidden h-48 bg-gray-100">
                            <img id="hero_image_preview" src="{{ $settings['hero_image']->value ?? 'https://via.placeholder.com/1920x1080' }}"
                                 alt="Hero Preview" class="w-full h-full object-cover">
                        </div>

                        <!-- Image upload options -->
                        <div>
                            <label for="hero_image" class="block text-sm font-medium text-gray-700 mb-1">
                                Image URL
                            </label>
                            <input type="text" name="hero_image" id="hero_image"
                                value="{{ $settings['hero_image']->value ?? '' }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-sm text-gray-500">
                                Enter a URL or upload an image below
                            </p>
                        </div>

                        <div class="mt-1 flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700">OR</span>
                            <div class="relative">
                                <label for="hero_image_upload" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/>
                                    </svg>
                                    Upload New Image
                                </label>
                                <input id="hero_image_upload" name="hero_image_upload" type="file" class="sr-only" accept="image/*">
                            </div>
                            <span id="file_name" class="text-sm text-gray-500"></span>
                        </div>
                        @error('hero_image')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('hero_image_upload')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="primary_button_text" class="block text-sm font-medium text-gray-700">
                                Primary Button Text
                            </label>
                            <div class="mt-1">
                                <input type="text" name="primary_button_text" id="primary_button_text"
                                    value="{{ $settings['primary_button_text']->value ?? 'Check Available Routes' }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Text for the primary call-to-action button.
                            </p>
                            @error('primary_button_text')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="secondary_button_text" class="block text-sm font-medium text-gray-700">
                                Secondary Button Text
                            </label>
                            <div class="mt-1">
                                <input type="text" name="secondary_button_text" id="secondary_button_text"
                                    value="{{ $settings['secondary_button_text']->value ?? 'Learn How to Book' }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Text for the secondary call-to-action button.
                            </p>
                            @error('secondary_button_text')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-6 mt-6 border-t border-gray-200 flex justify-end">
                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image upload preview
    const imageUpload = document.getElementById('hero_image_upload');
    const imagePreview = document.getElementById('hero_image_preview');
    const fileNameSpan = document.getElementById('file_name');
    const imageUrlInput = document.getElementById('hero_image');

    imageUpload.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                fileNameSpan.textContent = file.name;

                // Clear the URL input when uploading a file
                imageUrlInput.value = '';
            }

            reader.readAsDataURL(file);
        }
    });

    // URL input preview
    imageUrlInput.addEventListener('input', function() {
        if (this.value) {
            imagePreview.src = this.value;
            fileNameSpan.textContent = '';

            // Clear the file input when entering a URL
            imageUpload.value = '';
        }
    });
});
</script>
@endsection
