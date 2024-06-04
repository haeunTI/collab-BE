<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(UserRequest $request)
    {
        $validated = $request->validated();
        
        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            $user = Auth::user();
            
            if ($user instanceof User) {
                $tokenResult = $user->createToken('Token');
                $token = $tokenResult->accessToken;
                // return response(['test' => "what"]);

                $response = [   
                    'token' => $token,
                    'user' => $user
                ];

                return response($response, 200);
            } else {
                return response(['message' => 'Invalid user type'], 422);
            }

            // $token = $user->createToken('Token')->accessToken; 
            // $response = ['token' => $token, 'user' => $user];
            
            // return response($response, 200);


        } else {
            $response = ["message" => 'Invalid email or password'];
            return response($response, 422);
        }
        
    }

    public function get() {
        return response(['test' => "hellloo"]);
    }
}
