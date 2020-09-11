<?php

namespace App\Http\Controllers\API\v1;

use App\AuthKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

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
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->format(422,  $validator->messages()->all());
        } else {
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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'token' => 'required',
            'retoken' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->format(422,  $validator->messages()->all());
        } elseif ($request->token !== $request->retoken) {
            return response()->format(422, ['message' => 'token is not match.']);
        } else {
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
