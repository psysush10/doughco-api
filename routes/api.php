<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products',[ProductController::class, 'store']);

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders',[OrderController::class, 'store']);
