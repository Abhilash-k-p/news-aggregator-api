<?php

namespace App\Http\Controllers\Api\v1\Authentication;


use App\Http\Controllers\Api\v1\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{

    /**
     * send password reset link
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email|exists:users,email']);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors()->all(), 422);
        }

        $user = User::where('email', $request->get('email'))->first();
        // Generate the reset token
        $token = Password::broker()->createToken($user);

        return !empty($token)
            ? $this->sendResponse(compact('token'), 'Reset token successfully generated')
            : $this->sendError('Failed to generate reset token', code: 500);
    }

    /**
     * Do password reset
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors()->all(), 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->sendResponse([], 'Password reset successfully')
            : $this->sendError('Failed to reset password', code: 500);
    }
}
