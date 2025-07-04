<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginTrackingController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Login Tracking Routes
|--------------------------------------------------------------------------
*/

Route::prefix('login-tracking')->name('login-tracking.')->group(function () {

    // Main dashboard
    Route::get('/', [LoginTrackingController::class, 'index'])->name('index');

    // Users who have NOT logged in
    Route::get('/non-logged-in', [LoginTrackingController::class, 'nonLoggedInUsers'])->name('non-logged-in');

    // Manually add user (form + submit)
    Route::get('/create', [LoginTrackingController::class, 'createUser'])->name('create');
    Route::post('/store', [LoginTrackingController::class, 'storeUser'])->name('store');

    // Remove user
    Route::delete('/{id}', [LoginTrackingController::class, 'destroyUser'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| User Routes (should be outside login-tracking)
|--------------------------------------------------------------------------
*/

Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
