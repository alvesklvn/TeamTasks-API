<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectControler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);

    Route::post('login', [AuthController::class, 'login']);

    Route::delete('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware('signed')->name('verification.verify');
});

Route::prefix('project')->group(function () {
    Route::post('create', [ProjectControler::class, 'store'])->middleware('auth:sanctum');

    Route::get('/{project}/{response}', [ProjectControler::class, 'join'])->middleware('auth:sanctum');
});