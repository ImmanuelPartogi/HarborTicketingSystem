<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserProfileService
{
    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    public function getSavedPassengers(Request $request)
    {
        $passengers = DB::table('passengers')
            ->join('bookings', 'passengers.booking_id', '=', 'bookings.id')
            ->where('bookings.user_id', $request->user()->id)
            ->select('passengers.*')
            ->distinct('passengers.id_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'passengers' => $passengers,
            ],
        ]);
    }
}
