<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\User;

//region Product CRUD API Routes
// Route to Create a new product
Route::post('/add/product', function (Request $request) {
    try {
        $product = new Product();
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->price = $request->input('price');
        $product->save();

        return response()->json(['message' => 'Product added successfully']);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Error adding product' . $e->getMessage()], 500);
    }
});

// Route to Read all existing products
Route::get('/products', function () {
    $products = Product::all();
    return response()->json($products);
});

// Route to Read a specific product
Route::get('/product/{id}', function ($id) {
    $product = Product::find($id);
    if ($product) {
        return response()->json($product);
    }
    return response()->json(['message' => 'Product not found'], 404);
});

// Route to Update an existing product
Route::put('/update/product/{id}', function (Request $request, $id) {
    $product = Product::find($id);
    if ($product) {
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->price = $request->input('price');
        $product->save();
        return response()->json(['message' => 'Product updated successfully']);
    }
    return response()->json(['message' => 'Product not found'], 404);
});

// Route to Delete a product
Route::delete('/delete/product/{id}', function ($id) {
    $product = Product::find($id);
    if ($product) {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
    return response()->json(['message' => 'Product not found'], 404);
});
//endregion

//region User CRUD API Routes
// Route to Create a new user
Route::post('/add/user', function (Request $request) {
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
Route::put('/update/user/{id}', function (Request $request, $id) {
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
Route::delete('/delete/user/{id}', function ($id) {
    $user = User::find($id);
    if ($user) {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
    return response()->json(['message' => 'User not found'], 404);
});
//endregion