<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdminController::class, 'index'])->name('login');

Route::post('/login', [AdminController::class, 'login'])->name('login.post');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});