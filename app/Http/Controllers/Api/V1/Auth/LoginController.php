<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        $user = User::where(['email' => $request['email']])->first();

        if (! $user) {
            return response()->json('The credentials you provide are incorrect.', 422);
        }

        $user->tokens()->delete();

        $token = $user->createToken($request->userAgent());

        return ['token' => $token->plainTextToken];

    }
}
