<?php

namespace App\Http\Controllers\Auth;

use App\Models\UserActivityLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Log the login activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'login',
            'activity_details' => 'User logged in.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $userId = Auth::id();
        // Log the logout activity
        UserActivityLog::create([
            'user_id' => $userId,
            'activity_type' => 'logout',
            'activity_details' => 'User logged out.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->noContent();
    }
}
