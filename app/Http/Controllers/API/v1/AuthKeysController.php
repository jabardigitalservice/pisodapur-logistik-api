<?php

namespace App\Http\Controllers\API\v1;

use App\AuthKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Validation;

class AuthKeysController extends Controller
{

    public function index()
    {
        return response()->format(401, "Unauthenticated");
    }

    /**
     * Register function
     * 
     * To Register an external app to get data from Logistic
     *
     * @param Request $request
     * @return void
     */
    public function register(Request $request)
    {
        $param = ['name' => 'required'];
        if (Validation::validate($request, $param)){
            $generateToken = bin2hex(openssl_random_pseudo_bytes(16));
            $user = AuthKey::create([
                'name' => $request->name,
                'token' => $generateToken
            ]);
            return response()->format(200, true, ['auth_keys' => $user]);
        }
    }

    /**
     * Reset function
     * 
     * To Reset an external app Key Token to get data from Logistic
     *
     * @param Request $request
     * @return void
     */
    public function reset(Request $request)
    {
        $param = [
            'name' => 'required',
            'token' => 'required',
            'retoken' => 'required'
        ];
        if (Validation::validate($request, $param)){
            $generateToken = bin2hex(openssl_random_pseudo_bytes(16));
            $authKey = AuthKey::whereName($request->name)->whereToken($request->token)->update([
                'name' => $request->name,
                'token' => $generateToken
            ]);

            if (!$authKey) {
                return response()->json([
                    'error' => true, 
                    'message' => 'Data not Found!'
                ], 422);
            } else {
                return response()->format(200, true, [
                    'auth_keys' => [                    
                        'name' => $request->name,
                        'token' => $generateToken
                    ]
                ]);
            }
        }
    }
}
