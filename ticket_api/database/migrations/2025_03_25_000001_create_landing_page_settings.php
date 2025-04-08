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
            ['key' => 'hero_title', 'value' => 'Jelajahi Danau Toba dengan Layanan Kapal Feri Kami', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero_subtitle', 'value' => 'Pesan tiket feri Anda secara online untuk pengalaman perjalanan yang mulus. Transportasi  yang aman, nyaman, dan terjangkau ke tempat tujuan Anda.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero_image', 'value' => 'https://images.unsplash.com/photo-1523292562811-8fa7962a78c8?q=80&w=2070', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_button_text', 'value' => 'Cek Rute yang Tersedia', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'secondary_button_text', 'value' => 'Pelajari Cara Pemesanan', 'created_at' => now(), 'updated_at' => now()],

            // Features Section
            ['key' => 'features_title', 'value' => 'Mengapa Memilih Layanan Feri Kami', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'features_subtitle', 'value' => 'Rasakan pengalaman perjalanan  terbaik dengan keunggulan ini', 'created_at' => now(), 'updated_at' => now()],

            // Feature 1
            ['key' => 'feature1_icon', 'value' => 'fas fa-anchor', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature1_title', 'value' => 'Layanan Terpercaya', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature1_description', 'value' => 'Keberangkatan dan kedatangan tepat waktu dengan fokus pada kepuasan penumpang', 'created_at' => now(), 'updated_at' => now()],

            // Feature 2
            ['key' => 'feature2_icon', 'value' => 'fas fa-shield-alt', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature2_title', 'value' => 'Utamakan Keselamatan', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature2_description', 'value' => 'Kami memprioritaskan keselamatan dengan kapal yang terawat baik dan staf yang terlatih', 'created_at' => now(), 'updated_at' => now()],

            // Feature 3
            ['key' => 'feature3_icon', 'value' => 'fas fa-ticket-alt', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature3_title', 'value' => 'Pemesanan Mudah', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature3_description', 'value' => 'Sistem pemesanan tiket online yang sederhana dengan konfirmasi instan', 'created_at' => now(), 'updated_at' => now()],

            // Feature 4
            ['key' => 'feature4_icon', 'value' => 'fas fa-wallet', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature4_title', 'value' => 'Harga Terjangkau', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'feature4_description', 'value' => 'Harga kompetitif dengan diskon khusus untuk wisatawan reguler', 'created_at' => now(), 'updated_at' => now()],

            // Routes Section
            ['key' => 'routes_title', 'value' => 'Rute Populer', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'routes_subtitle', 'value' => 'Jelajahi rute yang paling sering dilalui', 'created_at' => now(), 'updated_at' => now()],

            // How to Book Section
            ['key' => 'howto_title', 'value' => 'Cara Memesan Tiket Feri Anda', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'howto_subtitle', 'value' => 'Ikuti langkah-langkah sederhana ini untuk memesan perjalanan Anda', 'created_at' => now(), 'updated_at' => now()],

            // Step 1
            ['key' => 'step1_icon', 'value' => 'fas fa-search', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step1_title', 'value' => 'Cari Rute', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step1_description', 'value' => 'Masukkan asal, tujuan, dan tanggal perjalanan Anda untuk menemukan feri yang tersedia.', 'created_at' => now(), 'updated_at' => now()],

            // Step 2
            ['key' => 'step2_icon', 'value' => 'fas fa-calendar-alt', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step2_title', 'value' => 'Pilih Jadwal', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step2_description', 'value' => 'Pilih dari jadwal yang tersedia dan jenis feri yang sesuai dengan kebutuhan Anda.', 'created_at' => now(), 'updated_at' => now()],

            // Step 3
            ['key' => 'step3_icon', 'value' => 'fas fa-credit-card', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step3_title', 'value' => 'Lakukan Pembayaran', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step3_description', 'value' => 'Pembayaran yang aman melalui berbagai pilihan termasuk kartu kredit dan mobile banking.', 'created_at' => now(), 'updated_at' => now()],

            // Step 4
            ['key' => 'step4_icon', 'value' => 'fas fa-qrcode', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step4_title', 'value' => 'Dapatkan Tiket Elektronik', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'step4_description', 'value' => 'Dapatkan tiket elektronik Anda secara instan melalui akun Anda.', 'created_at' => now(), 'updated_at' => now()],

            // About Us Section
            ['key' => 'about_title', 'value' => 'Tentang Layanan Kapal Feri Kami', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'about_content', 'value' => 'Didirikan pada tahun 2010, platform tiket kapal feri kami telah menghubungkan pulau-pulau dan memfasilitasi perjalanan  yang mudah di seluruh Indonesia. Kami berdedikasi untuk menyediakan transportasi yang aman, andal, dan terjangkau bagi penumpang dan kendaraan.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'about_mission', 'value' => 'Misi kami adalah menyederhanakan perjalanan  melalui teknologi dengan tetap mempertahankan standar keselamatan dan layanan pelanggan yang tertinggi. Dengan jaringan rute yang luas yang menghubungkan pelabuhan-pelabuhan utama di seluruh nusantara, kami bangga dapat membantu menghubungkan pulau-pulau di Indonesia.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'about_image', 'value' => 'https://images.unsplash.com/photo-1523292562811-8fa7962a78c8?q=80&w=2070', 'created_at' => now(), 'updated_at' => now()],

            // Stats
            ['key' => 'stats_daily_trips', 'value' => '150+', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'stats_ferries', 'value' => '50+', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'stats_routes', 'value' => '25+', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'stats_passengers', 'value' => '1M+', 'created_at' => now(), 'updated_at' => now()],

            // CTA Section
            ['key' => 'cta_title', 'value' => 'Siap untuk Memulai Perjalanan Anda?', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cta_subtitle', 'value' => 'Pesan tiket feri Anda secara online untuk pengalaman perjalanan yang mulus. Transportasi  yang aman, nyaman, dan terjangkau ke tempat tujuan Anda.', 'created_at' => now(), 'updated_at' => now()],

            // Footer
            ['key' => 'footer_description', 'value' => 'Mitra terpercaya Anda untuk perjalanan  di Indonesia. Pesan tiket feri Anda secara online untuk pengalaman yang mulus.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_address', 'value' => 'Jln Siliwangi balige; Balige, Sumatera Utara, Indonesia 22315', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_phone', 'value' => '+62 21 1234 5678', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_email', 'value' => 'info@ferryticket.com', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_copyright', 'value' => '© 2024 Ferry Ticket System. All rights reserved.', 'created_at' => now(), 'updated_at' => now()],

            // Social Media
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/ferryticket', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/ferryticket', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/ferryticket', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/ferryticket', 'created_at' => now(), 'updated_at' => now()],

            // SEO Settings
            ['key' => 'meta_description', 'value' => 'Pesan tiket kapal feri Anda secara online untuk pengalaman perjalanan yang mulus di seluruh Indonesia. Transportasi  yang aman, nyaman, dan terjangkau.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'meta_keywords', 'value' => 'tiket feri, perjalanan , feri Indonesia, pemesanan online, tiket kapal', 'created_at' => now(), 'updated_at' => now()],
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
