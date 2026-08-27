<?php

declare(strict_types=1);

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function (): void {
    /**
     * Public routes
     */
    Route::group(['prefix' => '/'], function (): void {
        /**
         * Authentication
         */
        Route::middleware('throttle:3,1')
            ->post('/tokens', [TokenController::class, 'create'])
            ->name('tokens.create');

        /**
         * Registration
         */
        Route::middleware('throttle:6,1')
            ->post('/register', [RegisterController::class, 'store'])
            ->name('register');

        /**
         * Password recovery
         */
        Route::middleware('throttle:3,1')
            ->post('/forgot-password', [PasswordResetController::class, 'forgot'])
            ->name('password.forgot');
        Route::middleware('throttle:3,1')
            ->post('/reset-password', [PasswordResetController::class, 'reset'])
            ->name('password.reset');

        /**
         * API Home Route
         */
        Route::get('/', fn () => [
            'data' => [
                'message' => __('messages.default.welcome'),
            ],
        ])->name('home');

        /**
         * Timelines (read-only)
         */
        Route::get('/timelines', [TimelineController::class, 'index'])
            ->name('timelines.index');
        Route::get('/timelines/{slug}', [TimelineController::class, 'show'])
            ->name('timelines.show');

        /**
         * People (read-only)
         */
        Route::get('/people', [PersonController::class, 'index'])
            ->name('people.index');
        Route::get('/people/{slug}', [PersonController::class, 'show'])
            ->name('people.show');
    });

    /**
     * Protected routes
     */
    Route::group(['middleware' => 'auth:sanctum'], function (): void {
        /**
         * Authentication (logout / token revocation).
         * Revocation targets the bearer-token path; currentAccessToken()->delete()
         * is a no-op under a TransientToken.
         */
        Route::delete('/tokens', [TokenController::class, 'destroy'])
            ->name('tokens.destroy');
        Route::delete('/tokens/all', [TokenController::class, 'destroyAll'])
            ->name('tokens.destroyAll');

        /**
         * Timelines (write)
         */
        Route::post('/timelines', [TimelineController::class, 'store'])
            ->name('timelines.store');
        Route::match(['put', 'patch'], '/timelines/{slug}', [TimelineController::class, 'update'])
            ->name('timelines.update');
        Route::delete('/timelines/{slug}', [TimelineController::class, 'destroy'])
            ->name('timelines.destroy');
    });
});
