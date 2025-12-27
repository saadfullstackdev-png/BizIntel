<?php

namespace App\Http\Middleware;

use Closure;

class DisableApiRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Return a response indicating that the API routes are temporarily disabled
        // return response()->json(['message' => 'Service Is Temporarily unavailable, will come back soon'], 503);
         return $next($request);
    }
}
