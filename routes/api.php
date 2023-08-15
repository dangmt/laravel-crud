<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\StripeController;

Route::resource('products', ProductController::class);
Route::resource('cart', CartItemController::class);
Route::resource('orders', OrderController::class);
Route::get('stripe', [StripeController::class, 'payment']);
Route::get('paypal', [PaypalController::class, 'payment']);
Route::get('/paypal/complete', [PayPalController::class, 'complete'])->name('paypal.complete');
Route::get('/paypal/cancel', [PayPalController::class, 'cancel'])->name('paypal.cancel');
