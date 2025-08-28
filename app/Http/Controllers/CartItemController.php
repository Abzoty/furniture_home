<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Traits\FiltersByRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{
    use FiltersByRole;

    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = auth()->user();
            
            if ($user->role === 'admin') {
                $cartItems = CartItem::with(['cart', 'product'])->get();
            } else {
                // Customer can only see their own cart items
                $cartItems = CartItem::with(['cart', 'product'])
                    ->whereHas('cart', function ($query) use ($user) {
                        $query->where('customer_id', $user->id);
                    })->get();
            }

            return response()->json([
                'message' => 'Cart items retrieved successfully',
                'data' => $cartItems
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving cart items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'cart_id' => 'required|exists:cart,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cart = Cart::findOrFail($request->cart_id);
            
            // Check if user can access this cart
            if (!$this->canAccessResource($cart)) {
                return response()->json([
                    'message' => 'Access denied. You can only add items to your own cart.'
                ], 403);
            }

            // Check if product already exists in cart
            $existingItem = CartItem::where('cart_id', $request->cart_id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingItem) {
                // Update quantity if item already exists
                $existingItem->quantity += $request->quantity;
                $product = Product::findOrFail($request->product_id);
                $existingItem->total = $product->price * $existingItem->quantity;
                $existingItem->save();

                // Update cart total
                $this->updateCartTotal($cart);

                return response()->json([
                    'message' => 'Cart item quantity updated successfully',
                    'data' => $existingItem
                ], 200);
            }

            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            
            $cartItem = CartItem::create([
                'cart_id' => $request->cart_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'total' => $product->price * $request->quantity
            ]);

            // Update cart total
            $this->updateCartTotal($cart);

            DB::commit();

            return response()->json([
                'message' => 'Cart item added successfully',
                'data' => $cartItem->load('product')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error adding cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $cartItem = CartItem::with(['cart', 'product'])->findOrFail($id);

            // Check if user can access this cart item
            if (!$this->canAccessResource($cartItem->cart)) {
                return response()->json([
                    'message' => 'Access denied. You can only view your own cart items.'
                ], 403);
            }

            return response()->json([
                'message' => 'Cart item retrieved successfully',
                'data' => $cartItem
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem = CartItem::with('cart')->findOrFail($id);

            // Check if user can access this cart item
            if (!$this->canAccessResource($cartItem->cart)) {
                return response()->json([
                    'message' => 'Access denied. You can only update your own cart items.'
                ], 403);
            }

            DB::beginTransaction();

            $product = Product::findOrFail($cartItem->product_id);
            
            $cartItem->update([
                'quantity' => $request->quantity,
                'total' => $product->price * $request->quantity
            ]);

            // Update cart total
            $this->updateCartTotal($cartItem->cart);

            DB::commit();

            return response()->json([
                'message' => 'Cart item updated successfully',
                'data' => $cartItem->load('product')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $cartItem = CartItem::with('cart')->findOrFail($id);

            // Check if user can access this cart item
            if (!$this->canAccessResource($cartItem->cart)) {
                return response()->json([
                    'message' => 'Access denied. You can only delete your own cart items.'
                ], 403);
            }

            DB::beginTransaction();

            $cart = $cartItem->cart;
            $cartItem->delete();

            // Update cart total
            $this->updateCartTotal($cart);

            DB::commit();

            return response()->json([
                'message' => 'Cart item deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart total based on cart items
     */
    private function updateCartTotal(Cart $cart)
    {
        $total = $cart->items()->sum('total');
        $cart->update(['total' => $total]);
    }
}
