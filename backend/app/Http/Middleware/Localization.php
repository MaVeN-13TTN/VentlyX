<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if locale is specified in the request
        if ($request->hasHeader('Accept-Language')) {
            $locale = $request->header('Accept-Language');
            
            // Extract primary language from Accept-Language header
            if (strpos($locale, ',') !== false) {
                $locale = substr($locale, 0, strpos($locale, ','));
            }
            
            if (strpos($locale, ';') !== false) {
                $locale = substr($locale, 0, strpos($locale, ';'));
            }
            
            // Set application locale if it's supported
            if ($this->isLocaleSupported($locale)) {
                App::setLocale($locale);
            }
        }
        
        // Check if user is authenticated and has a preferred locale
        if ($request->user() && isset($request->user()->preferences['locale'])) {
            $userLocale = $request->user()->preferences['locale'];
            
            // Set application locale if it's supported
            if ($this->isLocaleSupported($userLocale)) {
                App::setLocale($userLocale);
            }
        }
        
        return $next($request);
    }
    
    /**
     * Check if the locale is supported by the application
     *
     * @param string $locale
     * @return bool
     */
    protected function isLocaleSupported(string $locale): bool
    {
        // List of supported locales
        $supportedLocales = ['en', 'es', 'fr', 'de', 'ar', 'zh', 'ja'];
        
        return in_array($locale, $supportedLocales);
    }
}