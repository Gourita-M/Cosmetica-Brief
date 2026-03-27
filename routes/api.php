<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StatisticsController;

Route::get('/profile', function(Request $request) {
        return auth()->user();
    })->middleware('auth:api');

Route::get('/orders', [OrderController::class, 'index'])->middleware('auth:api');
Route::post('/orders', [OrderController::class, 'store'])->middleware('auth:api');
Route::get('/orders/{order}', [OrderController::class, 'show'])->middleware('auth:api');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->middleware('auth:api');
Route::put('/orders/{order}/status', [OrderController::class, 'update'])->middleware('auth:api');

Route::get('/statistics', [StatisticsController::class, 'index'])->middleware('auth:api');

Route::POST('/register', [AuthController::class, 'register']);
Route::POST('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index'])->middleware('auth:api');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->middleware('auth:api');

Route::post('/categories', [CategoryController::class, 'store'])->middleware('auth:api');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('auth:api');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('auth:api');

Route::get('/products', [ProductController::class, 'index'])->middleware('auth:api');
Route::get('/products/{slug}', [ProductController::class, 'show'])->middleware('auth:api');

Route::post('/products', [ProductController::class, 'store'])->middleware('auth:api');
Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('auth:api');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('auth:api');

