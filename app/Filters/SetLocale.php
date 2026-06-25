<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Applies the active UI language for every request.
 * Priority: per-session choice (navbar toggle) → global default (Settings) → 'en'.
 */
class SetLocale implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $supported = config('App')->supportedLocales;
        $default   = setting('Branding.locale') ?: config('Branding')->locale ?: 'en';

        // Per-user preference (remembered) → global default; guests → session → default.
        if (auth()->loggedIn()) {
            $locale = auth()->user()->locale ?: $default;
        } else {
            $locale = session('locale') ?: $default;
        }
        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        service('request')->setLocale($locale);
        service('language')->setLocale($locale);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
