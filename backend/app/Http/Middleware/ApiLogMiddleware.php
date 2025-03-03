<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Record start time
        $startTime = microtime(true);

        // Process the request
        $response = $next($request);

        // Calculate duration
        $duration = microtime(true) - $startTime;

        // Prepare log data
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'status' => $response->getStatusCode(),
            'duration' => round($duration * 1000, 2) . 'ms', // Convert to milliseconds
            'user_agent' => $request->userAgent(),
        ];

        // Add request parameters if not a file upload
        if (!$request->isMethod('post') || !$request->hasFile('image')) {
            $logData['params'] = $request->except(['password', 'password_confirmation']);
        }

        // Log based on response status
        if ($response->getStatusCode() >= 400) {
            Log::error('API Request Failed', $logData);
        } else {
            Log::info('API Request', $logData);
        }

        return $response;
    }
}
