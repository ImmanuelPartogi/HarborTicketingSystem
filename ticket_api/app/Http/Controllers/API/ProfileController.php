<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    protected $profileService;

    /**
     * Create a new controller instance.
     *
     * @param ProfileService $profileService
     */
    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get user profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Add extra profile data if needed
            $profileData = $this->profileService->getEnhancedProfileData($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'profile' => $profileData
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving user profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
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
            'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . $request->user()->id,
            'address' => 'nullable|string|max:500',
            'id_number' => 'nullable|string|max:30',
            'id_type' => 'nullable|in:KTP,SIM,PASPOR',
            'dob' => 'nullable|date_format:Y-m-d|before:today',
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
            DB::beginTransaction();
            $user = $request->user();
            $user->update($request->all());

            // Update any additional profile data if needed
            $this->profileService->updateExtendedProfileData($user, $request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $user,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => 'An unexpected error occurred'
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
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                'different:current_password'
            ],
        ], [
            'password.regex' => 'New password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.different' => 'New password must be different from your current password.'
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
            DB::beginTransaction();

            $user->password = Hash::make($request->password);
            $user->save();

            // Revoke all tokens and create a new one
            $user->tokens()->delete();
            $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

            // Log the password change
            $this->profileService->logSecurityEvent($user->id, 'PASSWORD_CHANGE', $request->ip());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
                'data' => [
                    'token' => $token,
                    'token_expiration' => now()->addDays(7)->toDateTimeString()
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password change error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => 'An unexpected error occurred'
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
        try {
            $page = $request->query('page', 1);
            $perPage = min($request->query('per_page', 20), 50); // Maximum 50 per page

            $notifications = Notification::where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications->items(),
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'total' => $notifications->total(),
                        'per_page' => $notifications->perPage(),
                        'last_page' => $notifications->lastPage()
                    ],
                    'unread_count' => Notification::where('user_id', $request->user()->id)
                        ->where('is_read', false)
                        ->count()
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
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
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            $notification->is_read = true;
            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => [
                    'unread_count' => Notification::where('user_id', $request->user()->id)
                        ->where('is_read', false)
                        ->count()
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
                'error' => 'The requested notification could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        try {
            $count = Notification::where('user_id', $request->user()->id)
                ->where('is_read', false)
                ->count();

            Notification::where('user_id', $request->user()->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'data' => [
                    'updated_count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get saved passengers.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedPassengers(Request $request)
    {
        try {
            // Get unique passengers from user's bookings
            $passengers = $this->profileService->getSavedPassengers($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'passengers' => $passengers,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving saved passengers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve saved passengers',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get saved vehicles.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedVehicles(Request $request)
    {
        try {
            // Get unique vehicles from user's bookings
            $vehicles = $this->profileService->getSavedVehicles($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'vehicles' => $vehicles,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving saved vehicles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve saved vehicles',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get user's booking history summary.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookingHistory(Request $request)
    {
        try {
            $bookingHistory = $this->profileService->getBookingHistory($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'booking_history' => $bookingHistory
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving booking history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve booking history',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
