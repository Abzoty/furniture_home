<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
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
Route::get('/product/details/{id}', [ProductController::class, 'showDetails']);
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
