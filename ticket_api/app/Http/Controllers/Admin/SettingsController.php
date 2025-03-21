<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class SettingsController extends Controller
{
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
        }

        return redirect()->route('admin.settings')->with('success', 'System settings updated successfully.');
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
