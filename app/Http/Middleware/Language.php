<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = 'spanish';

        if ($request->session()->has('language_setting')) {
            $locale = $request->session()->get('language_setting');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
