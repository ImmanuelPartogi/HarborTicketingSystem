<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        // Map incoming field names to database field names
        $data = $request->all();

        // Handle field name differences
        if (isset($data['identity_number'])) {
            $data['id_number'] = $data['identity_number'];
            unset($data['identity_number']);
        }

        if (isset($data['identity_type'])) {
            $data['id_type'] = $data['identity_type'];
            unset($data['identity_type']);
        }

        if (isset($data['date_of_birth'])) {
            $data['dob'] = $data['date_of_birth'];
            unset($data['date_of_birth']);
        }

        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $request->user()->id,
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
            $user->update($data);

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
