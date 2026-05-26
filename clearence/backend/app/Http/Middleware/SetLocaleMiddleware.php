<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    private const SUPPORTED = ['en', 'sw'];
    private const DEFAULT   = 'en';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        App::setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Authenticated user's saved preference — highest priority.
        if (auth()->check() && in_array(auth()->user()->locale, self::SUPPORTED, true)) {
            return auth()->user()->locale;
        }

        // 2. Session (set by the language switcher for guests or before login).
        $session = $request->session()->get('locale');
        if ($session && in_array($session, self::SUPPORTED, true)) {
            return $session;
        }

        // 3. Accept-Language header — honour browser preference.
        $preferred = $request->getPreferredLanguage(self::SUPPORTED);
        if ($preferred && in_array($preferred, self::SUPPORTED, true)) {
            return $preferred;
        }

        return self::DEFAULT;
    }
}
