<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class LocalizationController extends Controller
{
    /**
     * Get all available locales
     */
    public function getLocales()
    {
        $locales = [];
        $langPath = resource_path('lang');
        
        if (File::exists($langPath)) {
            $directories = File::directories($langPath);
            
            foreach ($directories as $directory) {
                $locale = basename($directory);
                $name = $this->getLocaleName($locale);
                
                $locales[] = [
                    'code' => $locale,
                    'name' => $name,
                    'native_name' => $this->getLocaleNativeName($locale),
                    'is_rtl' => $this->isRtl($locale)
                ];
            }
        }
        
        return response()->json([
            'current_locale' => App::getLocale(),
            'available_locales' => $locales
        ]);
    }
    
    /**
     * Set user's preferred locale
     */
    public function setLocale(Request $request)
    {
        $request->validate([
            'locale' => ['required', 'string', 'max:10']
        ]);
        
        $locale = $request->locale;
        
        // Check if locale is supported
        $langPath = resource_path('lang/' . $locale);
        if (!File::exists($langPath)) {
            return response()->json([
                'message' => 'Unsupported locale'
            ], 400);
        }
        
        // Update user's preferences if authenticated
        if ($request->user()) {
            $preferences = $request->user()->preferences ?? [];
            $preferences['locale'] = $locale;
            
            $request->user()->update([
                'preferences' => $preferences
            ]);
        }
        
        return response()->json([
            'message' => 'Locale set successfully',
            'locale' => $locale
        ]);
    }
    
    /**
     * Get translations for a specific locale
     */
    public function getTranslations(Request $request, string $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        
        // Check if locale is supported
        $langPath = resource_path('lang/' . $locale);
        if (!File::exists($langPath)) {
            return response()->json([
                'message' => 'Unsupported locale'
            ], 400);
        }
        
        // Get all translation files for the locale
        $translations = [];
        $files = File::files($langPath);
        
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $translations[$filename] = require $file->getPathname();
        }
        
        return response()->json([
            'locale' => $locale,
            'translations' => $translations
        ]);
    }
    
    /**
     * Get locale name in English
     */
    private function getLocaleName(string $locale): string
    {
        $localeNames = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'ar' => 'Arabic',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
        ];
        
        return $localeNames[$locale] ?? $locale;
    }
    
    /**
     * Get locale name in its native language
     */
    private function getLocaleNativeName(string $locale): string
    {
        $nativeNames = [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'ar' => 'العربية',
            'zh' => '中文',
            'ja' => '日本語',
        ];
        
        return $nativeNames[$locale] ?? $locale;
    }
    
    /**
     * Check if locale is RTL
     */
    private function isRtl(string $locale): bool
    {
        $rtlLocales = ['ar', 'he', 'fa', 'ur'];
        return in_array($locale, $rtlLocales);
    }
}