<?php

use App\Http\Controllers\Api\v1\Authentication\LoginController;
use App\Http\Controllers\Api\v1\Authentication\PasswordResetController;
use App\Http\Controllers\Api\v1\Authentication\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    /**
     * Login and Register APIs
     */
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [LoginController::class, 'login']);

    /**
     * Password Reset APIs
     */
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);


    /**
     * Authenticated APIs
     */
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout']);
    });
});
