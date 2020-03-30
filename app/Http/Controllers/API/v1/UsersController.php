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
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $status = true;
        // all good so return the token
        return response()->json(compact('token', 'status'));
    }


    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required | email | unique:users',
            'password' => 'required',
            'roles' => 'required',
            'code_district_city' => 'required',
            'name_district_city' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        } else {
            $user = User::create([
                'email' => $request->email,
                'name' => $request->name,
                'password' => bcrypt($request->password),
                'roles' => $request->roles,
                'code_district_city' => $request->code_district_city,
                'name_district_city' => $request->name_district_city,
            ]);
            return response()->json(array('status' => true, 'token' => JWTAuth::fromUser($user), 'data' => $user), 200);
        }
    }
}
