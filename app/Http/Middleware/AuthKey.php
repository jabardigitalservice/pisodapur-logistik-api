<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;

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
        $JWTtoken = JWTAuth::getToken();
        $token = $request->header('Api-Key');
        $authKey = \App\AuthKey::whereToken($token)->first();
        if (!isset($authKey)) {
            if (!$JWTtoken) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            } else {
                try {
                    if (!$user = JWTAuth::parseToken()->authenticate()) {
                        return response()->format(404, 'user_not_found');
                    }
                    $request->merge(array("authenticated_user_id" => $user->id));
                } catch (TokenExpiredException $e) {
                    $token = $request->token;
                    $refreshedToken = JWTAuth::refresh($token);
                    return response()->format(200, "token_expired", ["new_token" => $refreshedToken]);
                } catch (JWTException $e) {
                    return response()->format(422, $e->getMessage());
                } catch (Exception $exception) {
                    return response()->format(422, 'token_failure');
                }
            }
        }
        return $next($request);
    }
}
