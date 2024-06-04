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
                return response(['test' => "what"]);
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->accessToken;

                $response = [
                    'token' => $token,
                    'user' => $user
                ];

                return response($response, 200);
            } else {
                return response(['message' => 'Invalid user type'], 422);
            }


        } else {
            $response = ["message" => 'Invalid email or password'];
            return response($response, 422);
        }
        
    }

    public function get() {
        return response(['test' => "hellloo"]);
    }
}
