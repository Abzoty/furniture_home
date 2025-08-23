<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;

//region Category CRUD API Routes
Route::post('/category', [CategoryController::class, 'store']);
Route::get('/category', [CategoryController::class, 'index']);
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

