<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItems = CartItem::all();
        return response()->json($cartItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:cart,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Create cart item
            $cartItem = new CartItem();
            $cartItem->cart_id = $request->input('cart_id');
            $cartItem->product_id = $request->input('product_id');
            $cartItem->quantity = $request->input('quantity');
            // Calculate total
            $cartItem->total = Product::find($request->input('product_id'))->price * $request->input('quantity');
            $cartItem->save();

            // Update cart total
            $cart = Cart::find($request->input('cart_id'));
            $cart->total += $cartItem->total;
            $cart->save();

            DB::commit();

            return response()->json(['message' => 'Cart item added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding cart item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cartItem = CartItem::find($id);
        if ($cartItem) {
            return response()->json($cartItem);
        } else {
            return response()->json(['message' => 'Cart item not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $cartItem = CartItem::find($id);
            if (!$cartItem) {
                return response()->json(['message' => 'Cart item not found'], 404);
            }

            // Update cart total
            $cart = Cart::find($cartItem->cart_id);
            $cart->total -= $cartItem->total;

            // Update cart item
            $cartItem->quantity = $request->input('quantity');
            // Recalculate total
            $cartItem->total = Product::find($cartItem->product_id)->price * $request->input('quantity');
            $cartItem->save();

            // Update cart total
            $cart->total += $cartItem->total;
            $cart->save();

            DB::commit();

            return response()->json(['message' => 'Cart item updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating cart item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cartItem = CartItem::find($id);
        if ($cartItem) {
            DB::beginTransaction();

            // Update cart total
            $cart = Cart::find($cartItem->cart_id);
            $cart->total -= $cartItem->total;
            $cart->save();
            // Delete cart item
            $cartItem->delete();

            DB::commit();
            
            return response()->json(['message' => 'Cart item deleted successfully']);
        } else {
            return response()->json(['message' => 'Cart item not found'], 404);
        }
    }
}
