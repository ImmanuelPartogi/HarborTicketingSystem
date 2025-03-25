{{-- resources/views/admin/settings/about.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'About Us Section Settings')
@section('header', 'About Us Section Settings')

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

        /* Stats card */
        .stats-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1.5rem;
            background-color: #f9fafb;
            transition: all 0.2s ease;
        }

        .stats-card:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
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
    </style>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">About Us Section</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Customize the about us section displayed on your landing page.
                </p>
            </div>
            <a href="{{ route('admin.settings') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-arrow-left -ml-1 mr-2 h-5 w-5 text-gray-500"></i>
                Back to Settings
            </a>
        </div>

        <div class="settings-card bg-white rounded-xl overflow-hidden">
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
                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="about_title" class="form-label">
                                <i class="fas fa-heading"></i> Section Title
                            </label>
                            <input type="text" name="about_title" id="about_title"
                                value="{{ $settings['about_title']->value ?? 'About Our Ferry Service' }}"
                                class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="about_content" class="form-label">
                                <i class="fas fa-align-left"></i> Main Content
                            </label>
                            <textarea name="about_content" id="about_content" rows="4"
                                class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['about_content']->value ?? 'Founded in 2010, our ferry ticket platform has been connecting islands and facilitating easy sea travel throughout Indonesia. We are dedicated to providing safe, reliable, and affordable transportation for passengers and vehicles.' }}</textarea>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="about_mission" class="form-label">
                                <i class="fas fa-bullseye"></i> Mission Statement
                            </label>
                            <textarea name="about_mission" id="about_mission" rows="4"
                                class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['about_mission']->value ?? 'Our mission is to simplify sea travel through technology while maintaining the highest standards of safety and customer service. With a wide network of routes connecting major ports across the archipelago, we\'re proud to help connect the islands of Indonesia.' }}</textarea>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="about_image" class="form-label">
                                <i class="fas fa-image"></i> About Image URL
                            </label>
                            <input type="text" name="about_image" id="about_image"
                                value="{{ $settings['about_image']->value ?? 'https://images.unsplash.com/photo-1580887742560-b8526e2bbae5?q=80&w=1974' }}"
                                class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                            <div class="image-preview-container mt-3 border border-gray-200">
                                <img id="about_image_preview"
                                    src="{{ $settings['about_image']->value ?? 'https://images.unsplash.com/photo-1580887742560-b8526e2bbae5?q=80&w=1974' }}"
                                    alt="About Image Preview" class="image-preview">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Enter the URL of an image to represent your ferry service
                            </p>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="mt-10 pt-6 border-t border-gray-200">
                        <h3 class="section-title">
                            <i class="fas fa-chart-line"></i> Statistics
                        </h3>

                        <div class="grid grid-cols-1 gap-6 mt-6 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="stats-card">
                                <label for="stats_daily_trips" class="form-label">
                                    <i class="fas fa-ship"></i> Daily Trips
                                </label>
                                <input type="text" name="stats_daily_trips" id="stats_daily_trips"
                                    value="{{ $settings['stats_daily_trips']->value ?? '150+' }}"
                                    class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="stats-card">
                                <label for="stats_ferries" class="form-label">
                                    <i class="fas fa-ferry"></i> Ferries
                                </label>
                                <input type="text" name="stats_ferries" id="stats_ferries"
                                    value="{{ $settings['stats_ferries']->value ?? '50+' }}"
                                    class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="stats-card">
                                <label for="stats_routes" class="form-label">
                                    <i class="fas fa-route"></i> Routes
                                </label>
                                <input type="text" name="stats_routes" id="stats_routes"
                                    value="{{ $settings['stats_routes']->value ?? '25+' }}"
                                    class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="stats-card">
                                <label for="stats_passengers" class="form-label">
                                    <i class="fas fa-users"></i> Passengers
                                </label>
                                <input type="text" name="stats_passengers" id="stats_passengers"
                                    value="{{ $settings['stats_passengers']->value ?? '1M+' }}"
                                    class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Live Preview (Statistics)</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-indigo-600" id="preview_daily_trips">
                                    {{ $settings['stats_daily_trips']->value ?? '150+' }}</p>
                                <p class="text-sm text-gray-500">Daily Trips</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-indigo-600" id="preview_ferries">
                                    {{ $settings['stats_ferries']->value ?? '50+' }}</p>
                                <p class="text-sm text-gray-500">Ferries</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-indigo-600" id="preview_routes">
                                    {{ $settings['stats_routes']->value ?? '25+' }}</p>
                                <p class="text-sm text-gray-500">Routes</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-indigo-600" id="preview_passengers">
                                    {{ $settings['stats_passengers']->value ?? '1M+' }}</p>
                                <p class="text-sm text-gray-500">Happy Passengers</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 mt-6 border-t border-gray-200 flex justify-end">
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg
