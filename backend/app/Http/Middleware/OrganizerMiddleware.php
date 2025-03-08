<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || (!$request->user()->hasRole('Organizer') && !$request->user()->hasRole('Admin'))) {
            return response()->json([
                'message' => 'Unauthorized. Organizer access required.'
            ], 403);
        }

        return $next($request);
    }
}
