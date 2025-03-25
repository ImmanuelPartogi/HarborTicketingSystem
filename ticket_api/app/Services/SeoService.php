<?php

namespace App\Services;

use Illuminate\Support\Facades\View;
use App\Models\Setting;

class SeoService
{
    /**
     * Set default SEO data
     *
     * @return void
     */
    public function setDefaultSeo()
    {
        $siteName = Setting::getValue('site_name', 'Ferry Ticket System');
        $metaDescription = Setting::getValue('meta_description', 'Book your ferry tickets online for a seamless travel experience across Indonesia. Safe, convenient, and affordable sea transportation.');
        $metaKeywords = Setting::getValue('meta_keywords', 'ferry tickets, sea travel, Indonesia ferry, online booking, boat tickets');

        $seoData = [
            'title' => $siteName,
            'description' => $metaDescription,
            'keywords' => $metaKeywords,
            'og_title' => $siteName,
            'og_description' => $metaDescription,
            'og_type' => 'website',
            'og_image' => Setting::getValue('og_image', asset('images/og-image.jpg')),
            'twitter_card' => 'summary_large_image',
        ];

        View::share('seo', $seoData);
    }

    /**
     * Set custom SEO data for a specific page
     *
     * @param array $data Custom SEO data
     * @return void
     */
    public function set(array $data)
    {
        $siteName = Setting::getValue('site_name', 'Ferry Ticket System');

        // Get default SEO data
        $seoData = View::shared('seo', [
            'title' => $siteName,
            'description' => '',
            'keywords' => '',
            'og_title' => $siteName,
            'og_description' => '',
            'og_type' => 'website',
            'og_image' => asset('images/og-image.jpg'),
            'twitter_card' => 'summary_large_image',
        ]);

        // Merge with custom data
        $seoData = array_merge($seoData, $data);

        // Append site name to title if not already included
        if (!str_contains($seoData['title'], $siteName)) {
            $seoData['title'] = $seoData['title'] . ' | ' . $siteName;
        }

        // Make sure og_title matches title if not set
        if (!isset($data['og_title'])) {
            $seoData['og_title'] = $seoData['title'];
        }

        // Make sure og_description matches description if not set
        if (!isset($data['og_description']) && isset($data['description'])) {
            $seoData['og_description'] = $data['description'];
        }

        View::share('seo', $seoData);
    }

    /**
     * Set title for a specific page
     *
     * @param string $title Page title
     * @return void
     */
    public function setTitle($title)
    {
        $this->set(['title' => $title]);
    }

    /**
     * Set meta description for a specific page
     *
     * @param string $description Meta description
     * @return void
     */
    public function setDescription($description)
    {
        $this->set(['description' => $description]);
    }

    /**
     * Set meta keywords for a specific page
     *
     * @param string $keywords Meta keywords
     * @return void
     */
    public function setKeywords($keywords)
    {
        $this->set(['keywords' => $keywords]);
    }
}
