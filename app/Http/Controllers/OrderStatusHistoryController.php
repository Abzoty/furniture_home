<?php

namespace App\Http\Controllers;

use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;

class OrderStatusHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $history = OrderStatusHistory::all();
        return response()->json($history);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $history = OrderStatusHistory::find($id);
        if (!$history) {
            return response()->json(['message' => 'Order status history not found'], 404);
        }
        return response()->json($history);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:50|in:pending,confirmed,shipped,delivered,cancelled'
        ]);

        $history = OrderStatusHistory::find($id);
        if ($history) {
            $history->status = $request->input('status');
            $history->save();
            return response()->json($history);
        } else {
            return response()->json(['message' => 'Order status history not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order_history = OrderStatusHistory::find($id);
        if ($order_history) {
            $order_history->delete();
            return response()->json(['message' => 'Order status history deleted successfully']);
        }
        return response()->json(['message' => 'Order status history not found'], 404);
    }
}
