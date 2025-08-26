<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::all();
        return response()->json($orders);
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
        $order = Order::find($id);
        if ($order) {
            $order->items = OrderItem::where('order_id', $id)->get();
            return response()->json($order);
        }
        return response()->json(['message' => 'Order not found'], 404);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully']);
        }
        return response()->json(['message' => 'Order not found'], 404);
    }
}
