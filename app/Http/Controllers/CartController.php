<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Traits\FiltersByRole;
use Illuminate\Http\Request;

class CartController extends Controller
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
            $query = Cart::query()->with('items');
            $carts = $this->applyRoleFilters($query)->get();

            return response()->json([
                'message' => 'Carts retrieved successfully',
                'data' => $carts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving carts',
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
            $user = auth()->user();

            // Customers can only create carts for themselves
            if ($user->role === 'customer') {
                $customerId = $user->id;
            } else {
                // Admin can create carts for any customer
                $request->validate([
                    'customer_id' => 'required|exists:users,id',
                ]);
                $customerId = $request->customer_id;
            }

            // Check if customer already has a cart
            $existingCart = Cart::where('customer_id', $customerId)->first();
            if ($existingCart) {
                return response()->json([
                    'message' => 'Cart already exists for this customer',
                    'data' => $existingCart
                ], 400);
            }

            // Verify the customer role
            $customer = User::findOrFail($customerId);
            if ($customer->role !== 'customer') {
                return response()->json([
                    'message' => 'Can only create carts for customers'
                ], 422);
            }

            $cart = Cart::create([
                'customer_id' => $customerId,
                'total' => 0.00
            ]);

            return response()->json([
                'message' => 'Cart created successfully',
                'data' => $cart
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating cart',
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
            $cart = Cart::with('items.product')->findOrFail($id);

            if (!$this->canAccessResource($cart)) {
                return response()->json([
                    'message' => 'Access denied. You can only view your own cart.'
                ], 403);
            }

            return response()->json([
                'message' => 'Cart retrieved successfully',
                'data' => $cart
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving cart',
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
            $cart = Cart::findOrFail($id);

            if (!$this->canAccessResource($cart)) {
                return response()->json([
                    'message' => 'Access denied. You can only delete your own cart.'
                ], 403);
            }

            $cart->delete();

            return response()->json([
                'message' => 'Cart deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
