<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SecurityController extends Controller
{
    /**
     * Get Content Security Policy headers
     */
    public function getCSP()
    {
        $policy = "default-src 'self'; " .
                 "script-src 'self' 'unsafe-inline' https://js.stripe.com; " .
                 "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
                 "img-src 'self' data: https://storage.googleapis.com https://*.stripe.com; " .
                 "font-src 'self' https://fonts.gstatic.com; " .
                 "connect-src 'self' https://api.stripe.com https://api.mapbox.com; " .
                 "frame-src 'self' https://js.stripe.com; " .
                 "object-src 'none'; " .
                 "base-uri 'self'; " .
                 "form-action 'self'; " .
                 "frame-ancestors 'self'; " .
                 "upgrade-insecure-requests;";
                 
        return Response::make('', 204)
            ->header('Content-Security-Policy', $policy);
    }
    
    /**
     * Get security headers
     */
    public function getSecurityHeaders()
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(self), payment=(self)',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
        ];
        
        return response()->json([
            'headers' => $headers
        ]);
    }
    
    /**
     * Get CORS configuration
     */
    public function getCorsConfig()
    {
        $config = [
            'allowed_origins' => ['https://ventlyx.com', 'https://www.ventlyx.com', 'http://localhost:3000'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],
            'exposed_headers' => ['Cache-Control', 'Content-Language', 'Content-Type', 'Expires', 'Last-Modified', 'Pragma'],
            'max_age' => 86400, // 24 hours
            'supports_credentials' => true
        ];
        
        return response()->json([
            'cors_config' => $config
        ]);
    }
    
    /**
     * Get security recommendations
     */
    public function getSecurityRecommendations()
    {
        $recommendations = [
            [
                'title' => 'Enable HTTPS',
                'description' => 'Ensure all traffic is encrypted using HTTPS',
                'priority' => 'high',
                'implemented' => true
            ],
            [
                'title' => 'Implement Content Security Policy',
                'description' => 'Add CSP headers to prevent XSS attacks',
                'priority' => 'high',
                'implemented' => true
            ],
            [
                'title' => 'Use Secure Cookies',
                'description' => 'Set secure and httpOnly flags on cookies',
                'priority' => 'high',
                'implemented' => true
            ],
            [
                'title' => 'Implement Rate Limiting',
                'description' => 'Protect against brute force attacks',
                'priority' => 'medium',
                'implemented' => true
            ],
            [
                'title' => 'Regular Security Audits',
                'description' => 'Conduct regular security audits and penetration testing',
                'priority' => 'medium',
                'implemented' => false
            ],
            [
                'title' => 'Implement CSRF Protection',
                'description' => 'Protect against cross-site request forgery',
                'priority' => 'high',
                'implemented' => true
            ]
        ];
        
        return response()->json([
            'security_recommendations' => $recommendations
        ]);
    }
}