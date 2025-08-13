<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustProxies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Request::setTrustedProxies(
            $request->getClientIps(),
            Request::HEADER_X_FORWARDED_ALL
        );

        if (config('app.env') === 'production') {
            $request->server->set('HTTPS', 'on');
            url()->forceScheme('https');
        }

        return $next($request);
    }
}
