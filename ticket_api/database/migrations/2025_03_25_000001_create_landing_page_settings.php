<?php
// database/migrations/2024_03_25_000001_add_landing_page_settings.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add landing page settings
        $landingPageSettings = [
            // Hero Section
            ['key' => 'hero_title', 'value' => 'Explore the Sea with Our Ferry Service', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero_subtitle', 'value' => 'Book your ferry tickets online for a seamless travel experience. Safe, convenient, and affordable sea transportation to your destination.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero_image', 'value' => 'https://images.unsplash.com/photo-1523292562811-8fa7962a78c8?q=80&w=2070', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_button_text', 'value' => 'Check Available Routes', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'secondary_button_text', 'value' => 'Learn How to Book', 'created_at' => now(), 'updated_at' => now()],

            // Features Section
            ['key' => 'features_title', 'value' => 'Why Choose Our Ferry Service', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'features_subtitle', 'value' => 'Experience the best sea travel with these benefits', 'created_at' => now(), 'updated_at' => now()],

            // Feature 1
            ['key' => 'feature1_icon', 'value' => 'fas fa-anchor', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature1_title', 'value' => 'Reliable Service', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature1_description', 'value' => 'Punctual departures and arrivals with a focus on passenger satisfaction', 'created_at' => now(), 'updated_at' => now()],

            // Feature 2
            ['key' => 'feature2_icon', 'value' => 'fas fa-shield-alt', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature2_title', 'value' => 'Safety First', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature2_description', 'value' => 'We prioritize safety with well-maintained vessels and trained staff', 'created_at' => now(), 'updated_at' => now()],

            // Feature 3
            ['key' => 'feature3_icon', 'value' => 'fas fa-ticket-alt', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature3_title', 'value' => 'Easy Booking', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature3_description', 'value' => 'Simple online booking system for tickets with instant confirmation', 'created_at' => now(), 'updated_at' => now()],

            // Feature 4
            ['key' => 'feature4_icon', 'value' => 'fas fa-wallet', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature4_title', 'value' => 'Affordable Rates', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature4_description', 'value' => 'Competitive pricing with special discounts for regular travelers', 'created_at' => now(), 'updated_at' => now()],

            // Routes Section
            ['key' => 'routes_title', 'value' => 'Popular Routes', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'routes_subtitle', 'value' => 'Explore our most frequently traveled sea routes', 'created_at' => now(), 'updated_at' => now()],

            // How to Book Section
            ['key' => 'howto_title', 'value' => 'How to Book Your Ferry Ticket', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'howto_subtitle', 'value' => 'Follow these simple steps to book your journey', 'created_at' => now(), 'updated_at' => now()],

            // Step 1
            ['key' => 'step1_icon', 'value' => 'fas fa-search', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step1_title', 'value' => 'Search Routes', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step1_description', 'value' => 'Enter your origin, destination, and travel date to find available ferries.', 'created_at' => now(), 'updated_at' => now()],

            // Step 2
            ['key' => 'step2_icon', 'value' => 'fas fa-calendar-alt', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step2_title', 'value' => 'Select Schedule', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step2_description', 'value' => 'Choose from available schedules and ferry types that suit your needs.', 'created_at' => now(), 'updated_at' => now()],

            // Step 3
            ['key' => 'step3_icon', 'value' => 'fas fa-credit-card', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step3_title', 'value' => 'Make Payment', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step3_description', 'value' => 'Secure payment via multiple options including credit card and mobile banking.', 'created_at' => now(), 'updated_at' => now()],

            // Step 4
            ['key' => 'step4_icon', 'value' => 'fas fa-qrcode', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step4_title', 'value' => 'Get E-Ticket', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step4_description', 'value' => 'Receive your e-ticket instantly via email or download from your account.', 'created_at' => now(), 'updated_at' => now()],

            // About Us Section
            ['key' => 'about_title', 'value' => 'About Our Ferry Service', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'about_content', 'value' => 'Founded in 2010, our ferry ticket platform has been connecting islands and facilitating easy sea travel throughout Indonesia. We are dedicated to providing safe, reliable, and affordable transportation for passengers and vehicles.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'about_mission', 'value' => 'Our mission is to simplify sea travel through technology while maintaining the highest standards of safety and customer service. With a wide network of routes connecting major ports across the archipelago, we\'re proud to help connect the islands of Indonesia.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'about_image', 'value' => 'https://images.unsplash.com/photo-1580887742560-b8526e2bbae5?q=80&w=1974', 'created_at' => now(), 'updated_at' => now()],

            // Stats
            ['key' => 'stats_daily_trips', 'value' => '150+', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'stats_ferries', 'value' => '50+', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'stats_routes', 'value' => '25+', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'stats_passengers', 'value' => '1M+', 'created_at' => now(), 'updated_at' => now()],

            // CTA Section
            ['key' => 'cta_title', 'value' => 'Ready to Start Your Journey?', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cta_subtitle', 'value' => 'Book your ferry tickets online for a seamless travel experience. Safe, convenient, and affordable sea transportation to your destination.', 'created_at' => now(), 'updated_at' => now()],

            // Footer
            ['key' => 'footer_description', 'value' => 'Your trusted partner for sea travel in Indonesia. Book your ferry tickets online for a seamless experience.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_address', 'value' => 'Jl. Pelabuhan Raya No. 123, Jakarta Utara, Indonesia', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_phone', 'value' => '+62 21 1234 5678', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_email', 'value' => 'info@ferryticket.com', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_copyright', 'value' => '© 2024 Ferry Ticket System. All rights reserved.', 'created_at' => now(), 'updated_at' => now()],

            // Social Media
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/ferryticket', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/ferryticket', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/ferryticket', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/ferryticket', 'created_at' => now(), 'updated_at' => now()],

            // SEO Settings
            ['key' => 'meta_description', 'value' => 'Book your ferry tickets online for a seamless travel experience across Indonesia. Safe, convenient, and affordable sea transportation.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'meta_keywords', 'value' => 'ferry tickets, sea travel, Indonesia ferry, online booking, boat tickets', 'created_at' => now(), 'updated_at' => now()],
        ];

        // Insert the new settings
        DB::table('settings')->insert($landingPageSettings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove landing page settings
        $landingPageKeys = [
            'hero_title', 'hero_subtitle', 'hero_image', 'primary_button_text', 'secondary_button_text',
            'features_title', 'features_subtitle',
            'feature1_icon', 'feature1_title', 'feature1_description',
            'feature2_icon', 'feature2_title', 'feature2_description',
            'feature3_icon', 'feature3_title', 'feature3_description',
            'feature4_icon', 'feature4_title', 'feature4_description',
            'routes_title', 'routes_subtitle',
            'howto_title', 'howto_subtitle',
            'step1_icon', 'step1_title', 'step1_description',
            'step2_icon', 'step2_title', 'step2_description',
            'step3_icon', 'step3_title', 'step3_description',
            'step4_icon', 'step4_title', 'step4_description',
            'about_title', 'about_content', 'about_mission', 'about_image',
            'stats_daily_trips', 'stats_ferries', 'stats_routes', 'stats_passengers',
            'cta_title', 'cta_subtitle',
            'footer_description', 'footer_address', 'footer_phone', 'footer_email', 'footer_copyright',
            'social_facebook', 'social_twitter', 'social_instagram', 'social_youtube',
            'meta_description', 'meta_keywords'
        ];

        DB::table('settings')->whereIn('key', $landingPageKeys)->delete();
    }
};
