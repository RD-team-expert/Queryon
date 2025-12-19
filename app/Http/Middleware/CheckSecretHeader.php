<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class CheckSecretHeader
{

    public function handle(Request $request, Closure $next)
    {

        $providedKey = $request->header('X-Secret-Key') ?? $request->header('x-secret-key') ?? $request->header('X-SECRET-KEY');


        // The expected key is read from config (points to .env)
        $expectedKey = config('services.excel_secret');
        // or env('X_SECRET_KEY') if you'd rather go directly
        Log::info('Received API Key:', ['provided' => $providedKey]);

        // Check if they match
        if (!$providedKey || $providedKey !== $expectedKey) {
            // If the header is missing or invalid, return 401 Unauthorized
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}

