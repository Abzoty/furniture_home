<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusHistoryController;  
use App\Http\Controllers\PaymentController;  
use App\Http\Controllers\ReviewController;   
use App\Http\Controllers\EnquiryController;   
use App\Http\Controllers\StoreSettingController;   
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//region Authentication Routes
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/signin', [AuthController::class, 'signin']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
});
//endregion

//region Category API Routes
// Public routes (accessible to everyone)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/category/{id}', [CategoryController::class, 'show']);

// Admin only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/category', [CategoryController::class, 'store']);
    Route::put('/category/{id}', [CategoryController::class, 'update']);
    Route::delete('/category/{id}', [CategoryController::class, 'destroy']);
});
//endregion

//region Product API Routes
// Public routes (accessible to everyone)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/product/{id}', [ProductController::class, 'show']);

// Admin only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/product', [ProductController::class, 'store']);
    Route::put('/product/{id}', [ProductController::class, 'update']);
    Route::delete('/product/{id}', [ProductController::class, 'destroy']);
});
//endregion

//region Favorites API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::get('/favorite/{id}', [FavoriteController::class, 'show']);
    
    // Customer can create and delete favorites
    Route::middleware(['role:customer'])->group(function () {
        Route::post('/favorite', [FavoriteController::class, 'store']);
        Route::delete('/favorite/{id}', [FavoriteController::class, 'destroy']);
    });
});
//endregion

//region Cart API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/carts', [CartController::class, 'index']);
    Route::get('/cart/{id}', [CartController::class, 'show']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
});
//endregion

//region Cart-Item API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/cart-items', [CartItemController::class, 'index']);
    Route::get('/cart-item/{id}', [CartItemController::class, 'show']);
    Route::post('/cart-item', [CartItemController::class, 'store']);
    Route::put('/cart-item/{id}', [CartItemController::class, 'update']);
    Route::delete('/cart-item/{id}', [CartItemController::class, 'destroy']);
});
//endregion

//region Order API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/order/{id}', [OrderController::class, 'show']);
    Route::post('/order', [OrderController::class, 'store']);
    Route::delete('/order/{id}', [OrderController::class, 'destroy']);
});
//endregion

//region Order-Status API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/order-statuses', [OrderStatusHistoryController::class, 'index']);
    Route::get('/order-status/{id}', [OrderStatusHistoryController::class, 'show']);
    Route::post('/order-status', [OrderStatusHistoryController::class, 'store']); // Customer can create status (auto with order)
    
    // Admin can update and delete order statuses
    Route::middleware(['role:admin'])->group(function () {
        Route::put('/order-status/{id}', [OrderStatusHistoryController::class, 'update']);
        Route::delete('/order-status/{id}', [OrderStatusHistoryController::class, 'destroy']);
    });
});
//endregion

//region Payment API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payment/{id}', [PaymentController::class, 'show']);
    Route::post('/payment', [PaymentController::class, 'store']);
    Route::delete('/payment/{id}', [PaymentController::class, 'destroy']);
    
    // Admin can update payments
    Route::middleware(['role:admin'])->group(function () {
        Route::put('/payment/{id}', [PaymentController::class, 'update']);
    });
});
//endregion

//region Enquiry API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/enquiries', [EnquiryController::class, 'index']);
    Route::get('/enquiry/{id}', [EnquiryController::class, 'show']);
    Route::post('/enquiry', [EnquiryController::class, 'store']);
    Route::put('/enquiry/{id}', [EnquiryController::class, 'update']);
    Route::delete('/enquiry/{id}', [EnquiryController::class, 'destroy']);
});
//endregion

//region Review API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/review/{id}', [ReviewController::class, 'show']);
    Route::post('/review', [ReviewController::class, 'store']);
    Route::put('/review/{id}', [ReviewController::class, 'update']);
    Route::delete('/review/{id}', [ReviewController::class, 'destroy']);
});
//endregion

//region Store-Setting API Routes
// Public routes for customers
Route::get('/stores', [StoreSettingController::class, 'index']);
Route::get('/store/{id}', [StoreSettingController::class, 'show']);

// Admin only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/store', [StoreSettingController::class, 'store']);
    Route::put('/store/{id}', [StoreSettingController::class, 'update']);
    Route::delete('/store/{id}', [StoreSettingController::class, 'destroy']);
});
//endregion

