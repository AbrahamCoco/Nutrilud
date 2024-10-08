<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info(
            'Method: ' . $request->method() .
                ', URL: ' . $request->fullUrl() .
                ', IP: ' . $request->ip() .
                ', TIMESTAMP: ' . now()->toDateTimeString()
        );
        return $next($request);
    }
}