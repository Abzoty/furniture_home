<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carts = Cart::all();
        return response()->json($carts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
        ]);
        
        // Check if a cart already exists for the given customer_id
        $customerId = $request->input('customer_id');
        $cart = Cart::where('customer_id', $customerId)->first();

        if(!$cart) {
            // Check if the customer_id corresponds to a user with the role of 'customer'
            $customerId = $request->input('customer_id');
            $user = User::find($customerId);
            if (!$user || $user->role !== 'customer') {
                return response()->json(['message' => 'this user is not a customer'], 401);
            }

            try {
                $cart = new Cart();
                $cart->customer_id = $request->input('customer_id');
                $cart->total = 0.00; // Initial total
                $cart->save();

                return response()->json(['message' => 'Cart created successfully'], 201);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Error adding Cart' . $e->getMessage()], 500);
            }
        } else {
            return response()->json(['message' => 'Cart already exists'], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cart = Cart::find($id);
        if ($cart) {
            $cart->items = CartItem::where('cart_id', $id)->get();
            return response()->json($cart);
        } else {
            return response()->json(['message' => 'Cart not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cart = Cart::find($id);
        if ($cart) {
            $cart->delete();
            return response()->json(['message' => 'Cart deleted successfully']);
        } else {
            return response()->json(['message' => 'Cart not found'], 404);
        }
    }
}
