<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponseMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Generate cache key based on the full URL and user state
        $cacheKey = 'api_response_' . sha1($request->fullUrl() . ($request->user()?->id ?? 'guest'));

        // Check if we have a cached response
        if (Cache::has($cacheKey)) {
            return response()->json(
                Cache::get($cacheKey),
                200,
                ['X-Cache' => 'HIT']
            );
        }

        // Get the response
        $response = $next($request);

        // Cache successful responses for public endpoints
        if ($response->getStatusCode() === 200 && $this->shouldCache($request)) {
            Cache::put(
                $cacheKey,
                json_decode($response->getContent(), true),
                now()->addMinutes(15)
            );
        }

        return $response->header('X-Cache', 'MISS');
    }

    /**
     * Determine if the request should be cached based on predefined endpoint patterns.
     * 
     * This method checks if the current request path matches any of the predefined
     * cacheable endpoint patterns. It helps in maintaining a centralized list of
     * endpoints that should be cached, making it easier to manage caching rules.
     *
     * Cacheable endpoints include:
     * - GET /api/events (List of events)
     * - GET /api/events/{id}/ticket-types (Ticket types for a specific event)
     * - GET /api/events/{id}/ticket-types/availability (Ticket availability)
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request
     * @return bool  True if the endpoint should be cached, false otherwise
     * 
     * @example
     * // Request to /api/events
     * shouldCache($request) // returns true
     * 
     * // Request to /api/events/123/ticket-types
     * shouldCache($request) // returns true
     * 
     * // Request to /api/users (not in cacheable endpoints)
     * shouldCache($request) // returns false
     */
    private function shouldCache(Request $request): bool
    {
        // List of endpoints that should be cached
        $cacheableEndpoints = [
            'events' => true,
            'events/*/ticket-types' => true,
            'events/*/ticket-types/availability' => true
        ];

        // Check if the current path matches any cacheable endpoint pattern
        foreach ($cacheableEndpoints as $pattern => $shouldCache) {
            if ($request->is("api/$pattern")) {
                return $shouldCache;
            }
        }

        return false;
    }
}
