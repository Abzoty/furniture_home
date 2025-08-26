<?php

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
use App\Models\User;

//region Category CRUD API Routes
Route::post('/category', [CategoryController::class, 'store']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/category/{id}', [CategoryController::class, 'show']);
Route::put('/category/{id}', [CategoryController::class, 'update']);
Route::delete('/category/{id}', [CategoryController::class, 'destroy']);
//endregion

//region Product CRUD API Routes
Route::post('/product', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/product/{id}', [ProductController::class, 'show']);
Route::put('/product/{id}', [ProductController::class, 'update']);
Route::delete('/product/{id}', [ProductController::class, 'destroy']);
//endregion

//region Favorites API Routes
Route::post('/favorite', [FavoriteController::class, 'store']);
Route::get('/favorites', [FavoriteController::class, 'index']);
Route::get('/favorite/{id}', [FavoriteController::class, 'show']);
Route::delete('/favorite/{id}', [FavoriteController::class, 'destroy']);
//endregion

//region Cart CRUD API Routes
Route::post('/cart', [CartController::class, 'store']);
Route::get('/carts', [CartController::class, 'index']);
Route::get('/cart/{id}', [CartController::class, 'show']);
Route::delete('/cart/{id}', [CartController::class, 'destroy']);
//endregion

//region Cart-Item CRUD API Routes
Route::post('/cart-item', [CartItemController::class, 'store']);
Route::get('/cart-items', [CartItemController::class, 'index']);
Route::get('/cart-item/{id}', [CartItemController::class, 'show']);
Route::put('/cart-item/{id}', [CartItemController::class, 'update']);
Route::delete('/cart-item/{id}', [CartItemController::class, 'destroy']);
//endregion

//region Order CRUD API Routes
Route::post('/order', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/order/{id}', [OrderController::class, 'show']);
Route::delete('/order/{id}', [OrderController::class, 'destroy']);
//endregion

//region Order-Status CRUD API Routes
Route::get('/order-statuses', [OrderStatusHistoryController::class, 'index']);
Route::get('/order-status/{id}', [OrderStatusHistoryController::class, 'show']);
Route::put('/order-status/{id}', [OrderStatusHistoryController::class, 'update']);
Route::delete('/order-status/{id}', [OrderStatusHistoryController::class, 'destroy']);
//endregion

//region Payment CRUD API Routes
Route::post('/payment', [PaymentController::class, 'store']);
Route::get('/payments', [PaymentController::class, 'index']);
Route::get('/payment/{id}', [PaymentController::class, 'show']);
Route::put('/payment/{id}', [PaymentController::class, 'update']);
Route::delete('/payment/{id}', [PaymentController::class, 'destroy']);
//endregion

//region Enquiry CRUD API Routes
Route::post('/enquiry', [EnquiryController::class, 'store']);
Route::get('/enquires', [EnquiryController::class, 'index']);
Route::get('/enquiry/{id}', [EnquiryController::class, 'show']);
Route::put('/enquiry/{id}', [EnquiryController::class, 'update']);
Route::delete('/enquiry/{id}', [EnquiryController::class, 'destroy']);
//endregion

//region Review CRUD API Routes
Route::post('/review', [ReviewController::class, 'store']);
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/review/{id}', [ReviewController::class, 'show']);
Route::put('/review/{id}', [ReviewController::class, 'update']);
Route::delete('/review/{id}', [ReviewController::class, 'destroy']);
//endregion

//region Store-Setting CRUD API Routes
Route::post('/store', [StoreSettingController::class, 'store']);
Route::get('/stores', [StoreSettingController::class, 'index']);
Route::get('/store/{id}', [StoreSettingController::class, 'show']);
Route::put('/store/{id}', [StoreSettingController::class, 'update']);
Route::delete('/store/{id}', [StoreSettingController::class, 'destroy']);
//endregion

//region User CRUD API Routes
// Route to Create a new user
Route::post('/user', function (Request $request) {
    try {
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->phone = $request->input('phone');
        $user->role = $request->input('role', 'customer'); // Default role
        $user->save();

        return response()->json(['message' => 'User added successfully']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error adding user' . $e->getMessage()], 500);
    }
});

// Route to Read all existing users
Route::get('/users', function () {
    $users = User::all();
    return response()->json($users);
});

// Route to Read a specific user
Route::get('/user/{id}', function ($id) {
    $user = User::find($id);
    if ($user) {
        return response()->json($user);
    }
    return response()->json(['message' => 'User not found'], 404);
});

// Route to Update an existing user
Route::put('/user/{id}', function (Request $request, $id) {
    $user = User::find($id);
    if ($user) {
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        //$user->password = $request->input('password');
        $user->phone = $request->input('phone');
        $user->role = $request->input('role', 'customer'); // Default role
        $user->save();
        return response()->json(['message' => 'User updated successfully']);
    }
    return response()->json(['message' => 'User not found'], 404);
});

// Route to Delete a user
Route::delete('/user/{id}', function ($id) {
    $user = User::find($id);
    if ($user) {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
    return response()->json(['message' => 'User not found'], 404);
});
//endregion

