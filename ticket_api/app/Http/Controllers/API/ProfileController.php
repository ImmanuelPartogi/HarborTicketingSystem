<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Get user profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
 * Update user profile.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function updateProfile(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|max:255|unique:users,email,' . $request->user()->id,  // Added email field
        'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . $request->user()->id,
        'address' => 'nullable|string',
        'id_number' => 'nullable|string|max:30',
        'id_type' => 'nullable|in:KTP,SIM,PASPOR',
        'dob' => 'nullable|date_format:Y-m-d',
        'gender' => 'nullable|in:MALE,FEMALE',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $user = $request->user();

        // Check if we need to handle email change specially
        $emailChanged = isset($request->email) && $request->email !== $user->email;

        // Update user data
        $user->update($request->all());

        // Optional: Log email change or trigger email verification if needed
        if ($emailChanged) {
            // Log change
            Log::info("User {$user->id} changed email from {$user->getOriginal('email')} to {$user->email}");

            // Here you could add email verification logic if needed
            // For example: $user->email_verified_at = null; $user->save();
            // And send verification email: Mail::to($user->email)->send(new VerifyEmail($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update profile',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Change user password.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        try {
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user notifications.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
            ],
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @param Request $request
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markNotificationAsRead(Request $request, $notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Get saved passengers.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedPassengers(Request $request)
    {
        // Get unique passengers from user's bookings
        $passengers = $request->user()->bookings()
            ->with('passengers')
            ->get()
            ->pluck('passengers')
            ->flatten()
            ->unique('id_number')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'passengers' => $passengers,
            ],
        ]);
    }

    /**
     * Get saved vehicles.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedVehicles(Request $request)
    {
        // Get unique vehicles from user's bookings
        $vehicles = $request->user()->bookings()
            ->with('vehicles')
            ->get()
            ->pluck('vehicles')
            ->flatten()
            ->unique('license_plate')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'vehicles' => $vehicles,
            ],
        ]);
    }
}
