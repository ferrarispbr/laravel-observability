<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrometheusToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('prometheus.scrape_token');
        $providedToken = $request->bearerToken();

        if (! is_string($expectedToken) || $expectedToken === '' || ! is_string($providedToken) || ! hash_equals($expectedToken, $providedToken))
        {
            return response()->json(['message' => 'Unauthorized.'],401,['WWW-Authenticate' => 'Bearer'],);
        }

        return $next($request);
    }
}
