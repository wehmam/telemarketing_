<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Write activity log with optional description
     */
    public static function log($description = null, $statusCode = null, $userId = null, $method = null)
    {
        try {
            $request  = request();
            $response = response();

            ActivityLog::create([
                'user_id'     => Auth::id() ?? $userId,
                'method'      => $method ?? $request->method(),
                'url'         => $request->fullUrl(),
                'status_code' => $statusCode ?? ($response ? $response->getStatusCode() : null),
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->header('User-Agent'),
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            // do nothing if logging fails
        }
    }
}
