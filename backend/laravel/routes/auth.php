<?php

use App\Http\Controllers\Front\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Front\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Front\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Front\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Front\Auth\NewPasswordController;
use App\Http\Controllers\Front\Auth\PasswordResetLinkController;
use App\Http\Controllers\Front\Auth\RegisteredUserController;
use App\Http\Controllers\Front\Auth\VerifyEmailController;

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController as AdminConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController as AdminEmailVerificationNotificationController;
use App\Http\Controllers\Admin\Auth\EmailVerificationPromptController as AdminEmailVerificationPromptController;
use App\Http\Controllers\Admin\Auth\NewPasswordController as AdminNewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController as AdminPasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\RegisteredUserController as AdminRegisteredUserController;
use App\Http\Controllers\Admin\Auth\VerifyEmailController as AdminVerifyEmailController;

use Illuminate\Support\Facades\Route;

Route::get('/register', [RegisteredUserController::class, 'create'])
                ->middleware('guest')
                ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
                ->middleware('guest')
                ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('guest');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
                ->middleware('guest')
                ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
                ->middleware('guest')
                ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.update');

Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->middleware('auth')
                ->name('verification.notice');

Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['auth', 'signed', 'throttle:6,1'])
                ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware(['auth', 'throttle:6,1'])
                ->name('verification.send');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->middleware('auth')
                ->name('password.confirm');

Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
                ->middleware('auth');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware('auth')
                ->name('logout');


// Admin
Route::prefix('admin')->name('admin.')->namespace('Admin')->group(function(){

    // Route::get('/register', [AdminRegisteredUserController::class, 'create'])
    //             ->middleware('guest:admin')
    //             ->name('register');

    // Route::post('/register', [AdminRegisteredUserController::class, 'store'])
    //                 ->middleware('guest:admin');

    Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])
                    ->middleware('guest:admin')
                    ->name('login');

    Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])
                    ->middleware('guest:admin');

    Route::get('/forgot-password', [AdminPasswordResetLinkController::class, 'create'])
                    ->middleware('guest:admin')
                    ->name('password.request');

    Route::post('/forgot-password', [AdminPasswordResetLinkController::class, 'store'])
                    ->middleware('guest:admin')
                    ->name('password.email');

    Route::get('/reset-password/{token}', [AdminNewPasswordController::class, 'create'])
                    ->middleware('guest:admin')
                    ->name('password.reset');

    Route::post('/reset-password', [AdminNewPasswordController::class, 'store'])
                    ->middleware('guest:admin')
                    ->name('password.update');

    Route::get('/verify-email', [AdminEmailVerificationPromptController::class, '__invoke'])
                    ->middleware('auth:admin')
                    ->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', [AdminVerifyEmailController::class, '__invoke'])
                    ->middleware(['auth:admin', 'signed', 'throttle:6,1'])
                    ->name('verification.verify');

    Route::post('/email/verification-notification', [AdminEmailVerificationNotificationController::class, 'store'])
                    ->middleware(['auth:admin', 'throttle:6,1'])
                    ->name('verification.send');

    Route::get('/confirm-password', [AdminConfirmablePasswordController::class, 'show'])
                    ->middleware('auth:admin')
                    ->name('password.confirm');

    Route::post('/confirm-password', [AdminConfirmablePasswordController::class, 'store'])
                    ->middleware('auth:admin');

    Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
                    ->middleware('auth:admin')
                    ->name('logout');

});