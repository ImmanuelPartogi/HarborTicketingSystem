<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; // Add Carbon import for DateTime handling

class AuthService
{
    /**
     * Register a new user.
     *
     * @param array $data
     * @return User
     */
    public function registerUser(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        return $user;
    }

    /**
     * Authenticate a user and generate a token.
     *
     * @param array $credentials
     * @param bool $remember
     * @return array
     */
    public function loginUser(array $credentials, bool $remember = false)
    {
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        // Delete existing tokens
        $user->tokens()->delete();

        // Set token expiration based on remember me
        $minutes = $remember
            ? config('sanctum.remember_expiration', 43200)  // 30 days default
            : config('sanctum.expiration', 120);            // 2 hours default

        // Create DateTime object for expiration
        $expiresAt = Carbon::now()->addMinutes($minutes);

        // Create token with proper expiration format
        $token = $user->createToken('auth_token', [], $expiresAt)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'expiration' => $minutes, // Return minutes for compatibility with frontend
            'remember' => $remember,
        ];
    }

    /**
     * Authenticate an admin.
     *
     * @param array $credentials
     * @param bool $remember
     * @return Admin|null
     */
    public function loginAdmin(array $credentials, bool $remember = false)
    {
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            return Auth::guard('admin')->user();
        }

        return null;
    }

    /**
     * Log out a user by revoking tokens.
     *
     * @param User $user
     * @return bool
     */
    public function logoutUser(User $user)
    {
        $user->tokens()->delete();
        return true;
    }

    /**
     * Update user profile information.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUserProfile(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }

    /**
     * Change user password.
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changeUserPassword(User $user, string $currentPassword, string $newPassword)
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak benar.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return true;
    }

    /**
     * Create a new admin.
     *
     * @param array $data
     * @return Admin
     */
    public function createAdmin(array $data)
    {
        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'OPERATOR',
        ]);

        return $admin;
    }
}
