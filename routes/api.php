<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;

Route::post('/register', [AuthController::class, 'register']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index'])->middleware('auth:sanctum');

Route::post('/products', [ProductController::class, 'store'])->middleware('auth:sanctum');

Route::get('/orders', [OrderController::class, 'index'])->middleware('auth:sanctum');

Route::get('/orders/{order}', [OrderController::class, 'show'])->middleware('auth:sanctum');

Route::post('/orders', [OrderController::class, 'store'])->middleware('auth:sanctum');

Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->middleware('auth:sanctum');

Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->middleware('auth:sanctum');


