<?php

namespace App\Http\Controllers\Api\v1\Authentication;

use App\Http\Controllers\Api\v1\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        // Validate login input data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors()->all(), 422);
        }

        // Check if the user exists
        $user = User::where('email', $request->get('email'))->first();

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return $this->sendError('Invalid email or password', ['error' => 'Unauthorized'], 401);
        }

        // Revoke all existing tokens for the user
        $user->tokens()->delete();
        // Create and return an API token for the user
        $token = $user->createToken('NewsAggregatorApp')->plainTextToken;

        return $this->sendResponse(compact(['user', 'token']), 'User Logged In Successfully.');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke all current tokens
        $request->user()->tokens()->delete();

        return $this->sendResponse([], 'User Logged Out Successfully.');
    }

}
