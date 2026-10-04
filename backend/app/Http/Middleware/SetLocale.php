<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = [
        'de',
        'ar',
        'en',
        'tr',
        'uk',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('X-Locale', 'de');

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'de';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
