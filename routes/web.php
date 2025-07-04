<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginTrackingController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Login Tracking Routes
|--------------------------------------------------------------------------
| These routes handle user login tracking views and operations, including:
| - Viewing login data over a time range
| - Viewing users who haven’t logged in
| - Adding new users manually
| - Deleting users
*/

Route::prefix('login-tracking')->group(function () {
    
    // Main dashboard: shows login counts, last login, etc.
    Route::get('/', [LoginTrackingController::class, 'index'])
        ->name('login-tracking.index');

    // Shows list of users who have NOT logged in within selected days
    Route::get('/non-logged-in', [LoginTrackingController::class, 'nonLoggedInUsers'])
        ->name('login-tracking.non-logged-in');

    // Manually add a new user to the system
    Route::post('/store', [LoginTrackingController::class, 'storeUser'])
        ->name('login-tracking.store');

    // Remove a user from the system
    Route::delete('/{id}', [LoginTrackingController::class, 'destroyUser'])
        ->name('login-tracking.destroy');

        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

});
