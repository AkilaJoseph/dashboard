<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    private const SUPPORTED = ['en', 'sw'];

    private function dashboardRoute(): string
    {
        if (! auth()->check()) {
            return route('login');
        }

        return match(auth()->user()->role) {
            'admin'   => route('admin.dashboard'),
            'officer' => route('officer.dashboard'),
            default   => route('student.dashboard'),
        };
    }

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, self::SUPPORTED, true)) {
            abort(404);
        }

        // Persist to session for guests.
        $request->session()->put('locale', $locale);

        // Persist to user record for authenticated users so the choice
        // survives browser restarts and across devices.
        // Guard against the column not existing yet (migration pending).
        if (auth()->check()) {
            try {
                auth()->user()->update(['locale' => $locale]);
            } catch (\Illuminate\Database\QueryException) {
                // Column not yet migrated — session-only locale is sufficient.
            }
        }

        // redirect()->back() uses the Referer header, which can point to a non-HTML
        // resource (e.g. /sw.js, /manifest.json) if the user visited one before
        // clicking the switcher. Fall back to the role dashboard in that case.
        $previous = url()->previous('/');
        $path     = parse_url($previous, PHP_URL_PATH) ?? '/';

        if (preg_match('/\.(js|json|css|png|ico|webp|webmanifest|txt|xml)$/i', $path)) {
            $previous = $this->dashboardRoute();
        }

        return redirect($previous)->withHeaders(['Vary' => 'Accept-Language']);
    }
}
