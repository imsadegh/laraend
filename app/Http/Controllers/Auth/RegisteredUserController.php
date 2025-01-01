<?php

namespace App\Http\Controllers\Auth;

use App\Models\UserActivityLog;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // 'role_id' => ['required', 'exists:roles,id'], // Validate role_id
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            // 'role_id' => $request->role_id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Log the registration activity
        // UserActivityLog::create([
        //     'user_id' => $user->id,
        //     'activity_type' => 'registration',
        //     'activity_details' => 'User registered and logged in.',
        //     'ip_address' => $request->ip(),
        //     'user_agent' => $request->userAgent(),
        // ]);

        return response()->noContent();
    }
}
