<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Config;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        addJavascriptFile('assets/js/custom/authentication/sign-in/general.js');

        return view('pages.auth.loginv2');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $user = $request->user();


        if ($user->is_active == false) {
            Auth::logout();

            ActivityLogger::log("Login attempt denied for user {$user->email} - account is inactive", 403);

            throw ValidationException::withMessages([
                'email' => 'This account is inactive. Please contact the administrator.',
            ]);
        }

        // if ($user->session_id) {
        //     $path = storage_path('framework/sessions/'.$user->session_id);

        //     if (file_exists($path)) {
        //         ActivityLogger::log("Login attempt denied for user {$user->email} - already logged in on another device", 403);

        //         throw ValidationException::withMessages([
        //             'email' => 'This account is already logged in on another device.',
        //         ]);
        //     }
        // }


        // if (!$user->hasRole('administrator')) {
        //     $allowedIps = Config::where('key', 'allowed_ips')->first()?->value ?? [];
        //     if (is_string($allowedIps)) {
        //         $allowedIps = json_decode($allowedIps, true) ?? [];
        //     }

        //     if (!in_array($request->ip(), $allowedIps)) {
        //         Auth::logout();


        //         ActivityLogger::log("Login attempt denied for user {$user?->email} - IP {$request->ip()} not in whitelist", 403);

        //         throw ValidationException::withMessages([
        //             'email' => 'Your IP address ('.$request->ip().') is not allowed to access this system.',
        //         ]);
        //     }
        // }

        $request->session()->regenerate();

        $request->user()->update([
            'last_login_at' => Carbon::now()->toDateTimeString(),
            'last_login_ip' => $request->getClientIp(),
            'session_id'    => session()->getId()
        ]);



        ActivityLogger::log("Login successful for user {$user->email}", 200);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $user = Auth::guard('web')->user();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            $user->session_id = null;
            $user->save(); // save() works because $user is an Eloquent model

            // ActivityLogger::log(
            //     "Logout successful for user {$user->email}",
            //     200,
            //     $user->id
            // );
        }

        ActivityLogger::log("Logout successful for user {$user?->email}", 200, $user->id);

        return redirect('/');
    }
}
