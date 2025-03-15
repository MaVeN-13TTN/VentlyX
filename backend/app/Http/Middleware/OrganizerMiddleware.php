<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !($request->user()->hasRole('Admin') || $request->user()->hasRole('Organizer'))) {
            return response()->json(['message' => 'Unauthorized. Organizer access required.'], 403);
        }

        return $next($request);
    }
}
