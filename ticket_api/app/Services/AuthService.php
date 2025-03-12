<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
     * @return array
     */
    public function loginUser(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate an admin.
     *
     * @param array $credentials
     * @return Admin|null
     */
    public function loginAdmin(array $credentials)
    {
        if (Auth::guard('admin')->attempt($credentials)) {
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
