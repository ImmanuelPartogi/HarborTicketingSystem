<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Admin;
use App\Models\Setting;

use App\Services\FileUploadService;

class SettingsController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display the settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Get system settings
        $settings = DB::table('settings')->get()->keyBy('key');

        // If settings table doesn't exist yet, create a default empty collection
        if (!$settings) {
            $settings = collect();
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSystem(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'booking_expiry_hours' => 'required|integer|min:1|max:72',
        ]);

        // Update or create settings
        foreach ($request->except('_token') as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );

            // Clear cache for this setting
            Cache::forget(Setting::CACHE_PREFIX . $key);
        }

        return redirect()->route('admin.settings')->with('success', 'System settings updated successfully.');
    }

    /**
     * Display the hero section settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function heroSection()
    {
        // Get hero section settings
        $settings = DB::table('settings')
            ->whereIn('key', [
                'hero_title',
                'hero_subtitle',
                'hero_image',
                'primary_button_text',
                'secondary_button_text'
            ])
            ->get()
            ->keyBy('key');

        return view('admin.settings.hero', compact('settings'));
    }

    /**
     * Update hero section settings with image upload.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateHeroSection(Request $request)
    {
        $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string',
            'hero_image' => 'nullable|string',
            'hero_image_upload' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'primary_button_text' => 'required|string|max:100',
            'secondary_button_text' => 'required|string|max:100',
        ]);

        try {
            // Handle image upload if present
            if ($request->hasFile('hero_image_upload')) {
                $imageUrl = $this->fileUploadService->uploadImage(
                    $request->file('hero_image_upload'),
                    'landing/hero'
                );

                // Store the image URL in settings
                $this->updateSetting('hero_image', $imageUrl);
            } elseif ($request->filled('hero_image')) {
                // If no upload but URL is provided, update as normal
                $this->updateSetting('hero_image', $request->hero_image);
            }

            // Update other settings
            $this->updateSetting('hero_title', $request->hero_title);
            $this->updateSetting('hero_subtitle', $request->hero_subtitle);
            $this->updateSetting('primary_button_text', $request->primary_button_text);
            $this->updateSetting('secondary_button_text', $request->secondary_button_text);

            // Clear the cache
            $this->clearCache();

            return redirect()->route('admin.settings.hero')
                ->with('success', 'Hero section updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating hero section: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating hero section: ' . $e->getMessage());
        }
    }

    /**
     * Update a setting in the database
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    private function updateSetting($key, $value)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );

        // Clear the individual cache key
        Cache::forget('setting_' . $key);
    }

    /**
     * Clear relevant caches
     *
     * @return void
     */
    private function clearCache()
    {
        Cache::forget('landing_page_settings');
        Cache::forget('all_settings');
    }

    /**
     * Upload an image via AJAX for the rich text editor
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadEditorImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $imageUrl = $this->fileUploadService->uploadImage(
                $request->file('image'),
                'editor',
                null,   // Full width
                null,   // Full height
                true    // Optimize
            );

            return response()->json([
                'success' => true,
                'url' => $imageUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the features section settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function featuresSection()
    {
        // Get features section settings
        $settings = DB::table('settings')
            ->where(function ($query) {
                $query->where('key', 'like', 'feature%')
                    ->orWhere('key', 'features_title')
                    ->orWhere('key', 'features_subtitle');
            })
            ->get()
            ->keyBy('key');

        return view('admin.settings.features', compact('settings'));
    }

    /**
     * Update features section settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFeaturesSection(Request $request)
    {
        $request->validate([
            'features_title' => 'required|string|max:255',
            'features_subtitle' => 'required|string',
            'feature1_icon' => 'required|string|max:50',
            'feature1_title' => 'required|string|max:100',
            'feature1_description' => 'required|string',
            'feature2_icon' => 'required|string|max:50',
            'feature2_title' => 'required|string|max:100',
            'feature2_description' => 'required|string',
            'feature3_icon' => 'required|string|max:50',
            'feature3_title' => 'required|string|max:100',
            'feature3_description' => 'required|string',
            'feature4_icon' => 'required|string|max:50',
            'feature4_title' => 'required|string|max:100',
            'feature4_description' => 'required|string',
        ]);

        // Update or create settings
        foreach ($request->except('_token') as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );

            // Clear cache for this setting
            Cache::forget(Setting::CACHE_PREFIX . $key);
        }

        // Clear the landing page cache if you have one
        Cache::forget('landing_page_content');

        return redirect()->route('admin.settings.features')->with('success', 'Features section updated successfully.');
    }

    /**
     * Display the how-to book section settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function howToBookSection()
    {
        // Get how-to section settings
        $settings = DB::table('settings')
            ->where(function ($query) {
                $query->where('key', 'like', 'howto%')
                    ->orWhere('key', 'like', 'step%');
            })
            ->get()
            ->keyBy('key');

        return view('admin.settings.howto', compact('settings'));
    }

    /**
     * Update how-to section settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateHowToBookSection(Request $request)
    {
        $request->validate([
            'howto_title' => 'required|string|max:255',
            'howto_subtitle' => 'required|string',
            'step1_icon' => 'required|string|max:50',
            'step1_title' => 'required|string|max:100',
            'step1_description' => 'required|string',
            'step2_icon' => 'required|string|max:50',
            'step2_title' => 'required|string|max:100',
            'step2_description' => 'required|string',
            'step3_icon' => 'required|string|max:50',
            'step3_title' => 'required|string|max:100',
            'step3_description' => 'required|string',
            'step4_icon' => 'required|string|max:50',
            'step4_title' => 'required|string|max:100',
            'step4_description' => 'required|string',
        ]);

        // Update or create settings
        foreach ($request->except('_token') as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );

            // Clear cache for this setting
            Cache::forget(Setting::CACHE_PREFIX . $key);
        }

        // Clear the landing page cache if you have one
        Cache::forget('landing_page_content');

        return redirect()->route('admin.settings.howto')->with('success', 'How to Book section updated successfully.');
    }

    /**
     * Display the about section settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function aboutSection()
    {
        // Get about section settings
        $settings = DB::table('settings')
            ->where(function ($query) {
                $query->where('key', 'like', 'about%')
                    ->orWhere('key', 'like', 'stats%');
            })
            ->get()
            ->keyBy('key');

        return view('admin.settings.about', compact('settings'));
    }

    /**
     * Update about section settings with image upload.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAboutSection(Request $request)
    {
        $request->validate([
            'about_title' => 'required|string|max:255',
            'about_content' => 'required|string',
            'about_mission' => 'required|string',
            'about_image' => 'nullable|string',
            'about_image_upload' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'stats_daily_trips' => 'required|string|max:50',
            'stats_ferries' => 'required|string|max:50',
            'stats_routes' => 'required|string|max:50',
            'stats_passengers' => 'required|string|max:50',
        ]);

        try {
            // Handle image upload if present
            if ($request->hasFile('about_image_upload')) {
                $imageUrl = $this->fileUploadService->uploadImage(
                    $request->file('about_image_upload'),
                    'landing/about'
                );

                // Store the image URL in settings
                $this->updateSetting('about_image', $imageUrl);
            } elseif ($request->filled('about_image')) {
                // If no upload but URL is provided, update as normal
                $this->updateSetting('about_image', $request->about_image);
            }

            // Update other settings
            $this->updateSetting('about_title', $request->about_title);
            $this->updateSetting('about_content', $request->about_content);
            $this->updateSetting('about_mission', $request->about_mission);
            $this->updateSetting('stats_daily_trips', $request->stats_daily_trips);
            $this->updateSetting('stats_ferries', $request->stats_ferries);
            $this->updateSetting('stats_routes', $request->stats_routes);
            $this->updateSetting('stats_passengers', $request->stats_passengers);

            // Clear the cache
            $this->clearCache();

            return redirect()->route('admin.settings.about')
                ->with('success', 'About section updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating about section: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating about section: ' . $e->getMessage());
        }
    }

    /**
     * Display the footer settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function footerSection()
    {
        // Get footer settings
        $settings = DB::table('settings')
            ->where(function ($query) {
                $query->where('key', 'like', 'footer%')
                    ->orWhere('key', 'like', 'social%');
            })
            ->get()
            ->keyBy('key');

        return view('admin.settings.footer', compact('settings'));
    }

    /**
     * Update footer settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFooterSection(Request $request)
    {
        $request->validate([
            'footer_description' => 'required|string',
            'footer_address' => 'required|string',
            'footer_phone' => 'required|string|max:50',
            'footer_email' => 'required|email|max:255',
            'footer_copyright' => 'required|string|max:255',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
        ]);

        // Update or create settings
        foreach ($request->except('_token') as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );

            // Clear cache for this setting
            Cache::forget(Setting::CACHE_PREFIX . $key);
        }

        // Clear the landing page cache if you have one
        Cache::forget('landing_page_content');

        return redirect()->route('admin.settings.footer')->with('success', 'Footer section updated successfully.');
    }

    /**
     * Display the SEO settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function seoSettings()
    {
        // Get SEO settings
        $settings = DB::table('settings')
            ->where(function ($query) {
                $query->where('key', 'like', 'meta%')
                    ->orWhere('key', 'site_name');
            })
            ->get()
            ->keyBy('key');

        return view('admin.settings.seo', compact('settings'));
    }

    /**
     * Update SEO settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSeoSettings(Request $request)
    {
        $request->validate([
            'meta_description' => 'required|string|max:255',
            'meta_keywords' => 'required|string|max:255',
        ]);

        // Update or create settings
        foreach ($request->except('_token') as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );

            // Clear cache for this setting
            Cache::forget(Setting::CACHE_PREFIX . $key);
        }

        // Clear the landing page cache if you have one
        Cache::forget('landing_page_content');

        return redirect()->route('admin.settings.seo')->with('success', 'SEO settings updated successfully.');
    }

    /**
     * Show profile settings form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.settings.profile', compact('admin'));
    }

    /**
     * Update profile settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $adminId = $admin->id;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . $adminId,
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|confirmed|min:8',
        ]);

        // Verify current password if changing password
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }
        }

        // Prepare admin data for update
        $adminData = [
            'name' => $request->name,
            'email' => $request->email,
            'updated_at' => now()
        ];

        if ($request->filled('password')) {
            $adminData['password'] = Hash::make($request->password);
        }

        // Update admin using DB facade instead of model's save method
        DB::table('admins')->where('id', $adminId)->update($adminData);

        return redirect()->route('admin.settings.profile')->with('success', 'Profile updated successfully.');
    }
}
