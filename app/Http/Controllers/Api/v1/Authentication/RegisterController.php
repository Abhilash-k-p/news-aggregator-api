<?php

namespace App\Http\Controllers\Api\v1\Authentication;

use App\Http\Controllers\Api\v1\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{

    /**
     * Register User and generate token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors()->all(), 422);
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            // Create new user
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
            ]);
            // Generate API token after registration
            $token = $user->createToken('NewsAggregatorApp')->plainTextToken;
            // Commit the transaction
            DB::commit();

            // Return success message with the user's data and token
            return $this->sendResponse(compact(['user', 'token']), 'User Registered successfully.', 201);
        } catch (\Exception $e) {
            // If anything goes wrong, rollback the transaction
            DB::rollBack();
            return $this->sendError('Internal Server Error', ['error' => 'Registration failed'], 500);
        }
    }
}
