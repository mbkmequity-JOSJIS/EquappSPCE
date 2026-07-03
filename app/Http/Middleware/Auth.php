<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !$request->session()->has('firebase_user')
        ) {
            return redirect()
                ->route('login.index')
                ->with('warning', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
