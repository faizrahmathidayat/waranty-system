<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyCmsApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('services.cms.api_key');
        $provided = $request->header('X-API-Key');

        if (empty($expected) || empty($provided) || !hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Invalid or missing API key.'], 401);
        }

        return $next($request);
    }
}
