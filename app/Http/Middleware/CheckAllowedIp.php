<?php

namespace App\Http\Middleware;

use App\Models\Config;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAllowedIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('administrator')) {
            return $next($request);
        }

        $allowedIps = Config::where('key', 'allowed_ips')->first()?->value ?? [];

        if (!in_array($request->ip(), $allowedIps)) {
            abort(403, 'Your IP is not allowed to access this system.');
        }

        return $next($request);
    }
}
