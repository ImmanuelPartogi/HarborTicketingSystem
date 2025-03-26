<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckSessionExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $now = time();

            // Jika terakhir aktif lebih dari sesi timeout
            if ($lastActivity && $now - $lastActivity > config('session.lifetime') * 60) {
                Auth::logout();
                Session::flush();

                return redirect()->route('login')
                    ->with('message', 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.');
            }

            // Perbarui waktu aktivitas terakhir
            Session::put('last_activity', $now);
        }

        return $next($request);
    }
}
