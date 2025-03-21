<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyStaffUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->hasRole('staff')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Staff access required.'
            ], 403);
        }

        return $next($request);
    }
}
