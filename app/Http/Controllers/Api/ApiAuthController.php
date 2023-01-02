<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiAuthController extends Controller
{

    public function __construct()
    {

    }

    //Autenticação
    public function login(Request $request)
    {
        // grab credentials from the request
       // $credentials = $request->only('username', 'password');
       $credentials = array_merge($request->only('username', 'password'),  ['site_id' => env('ID_SITE')]);

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }

        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $user = auth()->user();

         $this->respondWithToken($token);

        // all good so return the token
        return response()->json(compact('token', 'user'));
    }

    //Refresh Token
    public function refresh()

    {      // return $this->respondWithToken(auth()->refresh());
        //return $this->respondWithToken(auth()->refresh());
        //var_dump(json_decode($this->respondWithToken(auth()->refresh())));
        //return response($this->respondWithToken(auth()->refresh()))->json();
    }

     protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            //'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }



    //Logout
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
