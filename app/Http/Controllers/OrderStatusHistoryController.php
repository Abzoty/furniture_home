<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Traits\FiltersByRole;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;


class OrderStatusHistoryController extends BaseController
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
                $cartItems = OrderStatusHistory::with(['order'])->get();
            } else {
                // Customer can only see their own cart items
                $cartItems = OrderStatusHistory::with(['Order'])
                    ->whereHas('order', function ($query) use ($user) {
                        $query->where('customer_id', $user->id);
                    })->get();
            }

            return response()->json([
                'message' => 'Order status history retrieved successfully',
                'data' => $cartItems
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving Order status history items',
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
            $history = OrderStatusHistory::findOrFail($id);

            if (!$this->canAccessResource($history->order)) {
                return response()->json([
                    'message' => 'Access denied. You can only view your own Order status history.'
                ], 403);
            }

            return response()->json([
                'message' => 'Order status history retrieved successfully',
                'data' => $history
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order status history not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving order status history',
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
                'status' => 'required|string|max:50|in:pending,confirmed,shipped,delivered,cancelled'
            ]);

            $history = OrderStatusHistory::findOrFail($id);

            if (!$this->canAccessResource($history)) {
                return response()->json([
                    'message' => 'Access denied. You can only update your own order history.'
                ], 403);
            }

            $history->status = $request->status;
            $history->save();

            return response()->json([
                'message' => 'Order status history updated successfully',
                'data' => $history
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order status history not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating order status history',
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
            $history = OrderStatusHistory::with('order')->findOrFail($id);

            if (!$this->canAccessResource($history->order)) {
                return response()->json([
                    'message' => 'Access denied. You can only delete your own order history.'
                ], 403);
            }

            $history->delete();

            return response()->json([
                'message' => 'Order status history deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order status history not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting order status history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
