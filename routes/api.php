<?php

use App\Http\Controllers\Api\v1\ArticleController;
use App\Http\Controllers\Api\v1\Authentication\LoginController;
use App\Http\Controllers\Api\v1\Authentication\PasswordResetController;
use App\Http\Controllers\Api\v1\Authentication\RegisterController;
use App\Http\Controllers\Api\v1\UserPreferenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    /**
     * Login and Register APIs
     */
    Route::post('register', [RegisterController::class, 'register'])->name('register');
    Route::post('login', [LoginController::class, 'login'])->name('login');

    /**
     * Password Reset APIs
     */
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetToken']);
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

    /**
     * Authenticated APIs
     */
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout']);

        /**
         * Article APIs
         */
        Route::get('/articles', [ArticleController::class, 'index']);
        Route::get('/articles/search', [ArticleController::class, 'search']);
        Route::get('/articles/{id}', [ArticleController::class, 'show']);

        /**
         * User Preference APIs
         */
        Route::post('/user/preferences', [UserPreferenceController::class, 'store']);
        Route::get('/user/preferences', [UserPreferenceController::class, 'show']);
        Route::get('/user/personalized-feed', [UserPreferenceController::class, 'getPersonalizedFeed']);

    });
});
