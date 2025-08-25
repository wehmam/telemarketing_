<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ips = Config::where('key', 'allowed_ips')->first()?->value ?? [];
        if (is_string($ips)) {
            $ips = json_decode($ips, true) ?? [];
        }

        return view("pages.apps.config.index", compact('ips'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'allowed_ips' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(), // ambil pesan error pertama
                'errors' => $validator->errors()
            ], 422);
        }

        $ips = collect(explode("\n", $request->allowed_ips))
            ->map(fn($ip) => trim($ip))
            ->filter()
            ->values()
            ->toArray();

        Config::updateOrCreate(
            ['key' => 'allowed_ips'],
            ['value' => json_encode($ips)]
        );

        return response()->json([
            'status' => true,
            'message' => 'Allowed IPs updated successfully',
            'ips' => $ips
        ]);
    }
}
