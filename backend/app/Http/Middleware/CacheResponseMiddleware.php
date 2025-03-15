<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        $cacheKey = 'api_response_' . sha1($request->fullUrl() . ($request->user()?->id ?? 'guest'));

        if (Cache::has($cacheKey)) {
            return response()->json(
                Cache::get($cacheKey),
                200,
                ['X-Cache' => 'HIT']
            );
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && $this->shouldCache($request)) {
            Cache::put(
                $cacheKey,
                json_decode($response->getContent(), true),
                now()->addMinutes(15)
            );
        }

        return $response->header('X-Cache', 'MISS');
    }

    private function shouldCache(Request $request): bool
    {
        $cacheableEndpoints = [
            'events' => true,
            'events/*/ticket-types' => true,
            'events/*/ticket-types/availability' => true
        ];

        foreach ($cacheableEndpoints as $pattern => $shouldCache) {
            if ($request->is("api/$pattern")) {
                return $shouldCache;
            }
        }

        return false;
    }
}
