<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // 👇 If request expects JSON OR is an API route → no redirect
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // Old behavior (web routes only)
        return route('login');
    }
}
