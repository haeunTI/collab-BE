<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
 * @OA\Post(
 *     path="/login",
 *     tags={"Login"},
 *     operationId="login",
 *     summary="User login",
 *     description="Authenticate user and return a token",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email", example="xjohnson@example.org"),
 *             @OA\Property(property="password", type="string", format="password", example="password")
 *         )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Successful login",
 *         @OA\JsonContent(
 *             example={
 *                 "token": "example_token_string",
 *                 "user": {
 *                     "id": "1",
 *                     "name": "Grace",
 *                     "email": "tes@gmail.com",
 *                      "email_verified_at": "timestamp",
 *                      "created_at": "timestamp",
 *                      "updated_at": "timestamp"
 *                 }
 * 
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response="422",
 *         description="Invalid email or password",
 *         @OA\JsonContent(
 *             example={
 *                 "message": "Invalid email or password"
 *             }
 *         )
 *     )
 * )
 */
    public function login(UserRequest $request)
    {
        $validated = $request->validated();
        
        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            $user = Auth::user();
            
            if ($user instanceof User) {
                $tokenResult = $user->createToken('Token');
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

    public function logout()
    {
        try {
            $user = Auth::user();
            
            if ($user instanceof User) {
                $user->token()->revoke();

                return response([
                    'status' => true,
                    'message' => 'Successfully logged out'
                ], 200);
            } else {
                return response(['message' => 'Invalid user type'], 422);
            }

        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'Failed to logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
