<?php

namespace App\Http\Controllers\API\v1;

use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;

class UsersController extends ApiController {

    public function __construct() {
        $this->middleware('jwt-auth', ['except' => ['authenticate', 'register']]);
    }


    public function authenticate(Request $request) {
        // grab credentials from the request
        $credentials = $request->only('username', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->format(401, 'invalid_credentials');
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->format(500, 'could_not_create_token');
        }

        $user = JWTAuth::user();
        $status = 'success';
        // all good so return the token
        return response()->format(200, true, compact('token', 'user'));
    }


    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required | unique:users',
            'email' => 'required | email | unique:users',
            'password' => 'required',
            'roles' => 'required',
            'code_district_city' => 'required',
            'name_district_city' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->format(422,  $validator->messages()->all());
        } else {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'name' => $request->name,
                'password' => bcrypt($request->password),
                'roles' => $request->roles,
                'code_district_city' => $request->code_district_city,
                'name_district_city' => $request->name_district_city,
            ]);
            return response()->format(200, true, [
                'token' => JWTAuth::fromUser($user),
                'user' => $user,
            ]);
        }
    }

    public function me(Request $request) {

        $currentUser = JWTAuth::user();
        return response()->format(200, true, $currentUser);

    }

}
