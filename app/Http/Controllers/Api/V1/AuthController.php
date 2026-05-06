<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\AuthenticateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(AuthenticateUserRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error(
                'Invalid credentials',
                Response::HTTP_UNAUTHORIZED
            );
        }


        // Create token saat success login
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success(
            [
                'token' => $token,
                'user' => new UserResource($user),
            ],
            'Login successful',

        );
    }



    public function me(Request $request)
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            'user data',
        );
    }




    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(
            null,
            'Logout successful',
            Response::HTTP_OK
        );
    }
}
