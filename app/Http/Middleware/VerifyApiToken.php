<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('app.api_token');
        if (! $expected) {
            abort(503, 'API token is not configured');
        }

        $token = $request->header('X-Api-Token') ?? $request->bearerToken();

        if (! is_string($token) || ! hash_equals($expected, $token)) {
            abort(401, 'Unauthorized');
        }

        return $next($request);
    }
}
