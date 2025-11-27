<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         // Check if token is missing or invalid
        if (!$request->bearerToken() || !$request->user()) {
            // Throw AuthenticationException
            throw new AuthenticationException('Token missing or invalid.');
        }

        return $next($request);
    }
}
