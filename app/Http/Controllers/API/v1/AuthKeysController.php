<?php

namespace App\Http\Controllers\API\v1;

use App\AuthKey;
use Illuminate\Http\Request;
use Illuminate\Http\response;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthKey\RegisterRequest;
use App\Http\Requests\AuthKey\ResetRequest;

class AuthKeysController extends Controller
{
    /**
     * Register function
     *
     * To Register an external app to get data from Logistic
     *
     * @param Request $request
     * @return void
     */
    public function register(RegisterRequest $request)
    {
        $generateToken = bin2hex(openssl_random_pseudo_bytes(16));
        $user = AuthKey::create([
            'name' => $request->name,
            'token' => $generateToken
        ]);
        return response()->format(Response::HTTP_OK, true, ['auth_keys' => $user]);
    }

    /**
     * Reset function
     *
     * To Reset an external app Key Token to get data from Logistic
     *
     * @param Request $request
     * @return void
     */
    public function reset(ResetRequest $request)
    {
        $generateToken = bin2hex(openssl_random_pseudo_bytes(16));
        $authKey = AuthKey::whereName($request->name)->whereToken($request->token)->update([
            'name' => $request->name,
            'token' => $generateToken
        ]);

        if (!$authKey) {
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Data not Found!');
        } else {
            $authKeyData = [
                'name' => $request->name,
                'token' => $generateToken
            ];
            $response = response()->format(response::HTTP_OK, true, ['auth_keys' => $authKeyData]);
        }
        return $response;
    }
}
