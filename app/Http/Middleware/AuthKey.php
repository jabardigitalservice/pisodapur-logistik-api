<?php

namespace App\Http\Middleware;

use Closure;

class AuthKey
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
        $token = $request->header('Api-Key');
        $authKey = \App\AuthKey::whereToken($token)->first();
        if (!isset($authKey)) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        return $next($request);
    }
}
