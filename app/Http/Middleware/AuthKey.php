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
        $response = $next($request);
        if (!isset($authKey)) {
            $response = response()->json(['message' => 'Unauthenticated'], 401);
            if ($JWTtoken) {
                try {
                    if (!$user = JWTAuth::parseToken()->authenticate()) {
                        $response = response()->format(404, 'user_not_found');
                    }
                    $request->merge(array("authenticated_user_id" => $user->id));
                    $response = $next($request);
                } catch (TokenExpiredException $e) {
                    $token = $request->token;
                    $refreshedToken = JWTAuth::refresh($token);
                    $response = response()->format(200, "token_expired", ["new_token" => $refreshedToken]);
                } catch (JWTException $e) {
                    $response = response()->format(422, $e->getMessage());
                } catch (Exception $exception) {
                    $response = response()->format(422, 'token_failure');
                }
            }
        }
        return $response;
    }
}
