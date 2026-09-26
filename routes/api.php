<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);

    Route::post('login', [AuthController::class, 'login']);

    Route::delete('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware('signed')->name('verification.verify');
});

Route::prefix('project')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->middleware('auth:sanctum');

    Route::get('/{project}', [ProjectController::class, 'show'])->middleware(['auth:sanctum', 'user.inProject']);

    Route::post('create', [ProjectController::class, 'store'])->middleware('auth:sanctum');

    Route::post('/{project}/members', [ProjectController::class, 'addMembers'])->middleware(['auth:sanctum', 'is.admin']);

    Route::get('/{project}/decision/{response}', [ProjectController::class, 'join'])->middleware('auth:sanctum');

    Route::post('/{project}/tasks', [TaskController::class, 'create'])->middleware(['auth:sanctum', 'is.admin']);

    Route::get('/{project}/tasks/{task?}', [TaskController::class, 'index'])->middleware(['auth:sanctum', 'user.inProject']);    
});

Route::prefix('tasks')->group(function () {
    Route::patch('/{task}', [TaskController::class, 'update'])->middleware(['auth:sanctum', 'user.adminOrOwner']);
    Route::put('/{task}', [TaskController::class, 'update'])->middleware(['auth:sanctum', 'user.adminOrOwner']);
});