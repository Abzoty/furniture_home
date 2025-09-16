<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Traits\FiltersByRole;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
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
            $user = Auth::user();

            if ($user->role === 'admin') {
                $orders = Order::with(['orderitems', 'statusHistory'])->get();
            } else {
                // Customer can only see their own orders
                $orders = Order::with(['orderitems', 'statusHistory'])
                    ->where('customer_id', $user->id)
                    ->get();
            }

            return response()->json([
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'status' => 'required|string|max:50',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $user = User::find($request->input('customer_id'));
        if (!$user || $user->role !== 'customer') {
            return response()->json(['message' => 'this user is not a customer'], 401);
        }

        try {
            DB::beginTransaction();

            // Calculate total first
            $orderTotal = 0;
            $orderItems = [];

            // Pre-calculate everything
            foreach ($request->input('items') as $item) {
                $product = Product::find($item['product_id']);
                $itemTotal = $product->price * $item['quantity'];
                $orderTotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal,
                ];
            }

            // Create and save order with final total
            $order = new Order();
            $order->customer_id = $request->input('customer_id');
            $order->status = $request->input('status');
            $order->total_amount = $orderTotal;
            $order->save(); // Save once to get ID

            // Create status history
            $order_status = new OrderStatusHistory();
            $order_status->order_id = $order->id;
            $order_status->status = $request->input('status');
            $order_status->save();

            // Create order items
            foreach ($orderItems as $itemData) {
                $order_item = new OrderItem();
                $order_item->order_id = $order->id;
                $order_item->product_id = $itemData['product_id'];
                $order_item->quantity = $itemData['quantity'];
                $order_item->total = $itemData['total'];
                $order_item->save();
            }

            DB::commit();

            return response()->json(['message' => 'Order created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $order = Order::with(['orderitems', 'statusHistory'])->findOrFail($id);

            // Check if user can access this cart item
            if (!$this->canAccessResource($order)) {
                return response()->json([
                    'message' => 'Access denied. You can only view your own cart items.'
                ], 403);
            }

            return response()->json([
                'message' => 'Cart item retrieved successfully',
                'data' => $order
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $order = Order::findOrFail($id);

            // Check if user can access this order
            if (!$this->canAccessResource($order)) {
                return response()->json([
                    'message' => 'Access denied. You can only delete your own orders.'
                ], 403);
            }

            // Delete related order items and status history first
            OrderItem::where('order_id', $order->id)->delete();
            OrderStatusHistory::where('order_id', $order->id)->delete();

            // Then delete the order itself
            $order->delete();

            return response()->json([
                'message' => 'Order deleted successfully'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
