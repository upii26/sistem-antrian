<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\QueueController as AdminQueueController;
use App\Http\Controllers\QueueController;

// Public Routes
Route::get('/', [QueueController::class, 'index'])->name('queue.index');
Route::post('/queue', [QueueController::class, 'store'])->name('queue.store');
Route::get('/queue/data', [QueueController::class, 'getData'])->name('queue.data');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {

    // Login admin (guest)
    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->name('login.post');

    // Protected admin routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminQueueController::class, 'index'])->name('dashboard');
        Route::post('/queue/next', [AdminQueueController::class, 'next'])->name('queue.next');
        Route::post('/queue/previous', [AdminQueueController::class, 'previous'])->name('queue.previous');
        Route::get('/queue/data', [AdminQueueController::class, 'getData'])->name('queue.data');
    });
});
