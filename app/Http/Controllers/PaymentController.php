<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentDetail;
use App\Models\PaymentDetail as Payment;
use App\Traits\FiltersByRole;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
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
                $payments = Payment::with(['order'])->get();
            } else {
                // Customer can only see their own cart items
                $payments = Payment::with(['Order'])
                    ->whereHas('order', function ($query) use ($user) {
                        $query->where('customer_id', $user->id);
                    })->get();
            }

            return response()->json([
                'message' => 'Payments retrieved successfully',
                'data' => $payments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving payments',
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
                'order_id' => 'required|integer|exists:orders,id',
                'payment_method' => 'required|string|max:50',
                'status' => 'required|string|max:20|in:pending,completed,failed'
            ]);

            $order = Order::findOrFail($request->order_id);

            if (!$this->canAccessResource($order)) {
                return response()->json([
                    'message' => 'Access denied. You can only create payments for your own orders.'
                ], 403);
            }

            $payment = PaymentDetail::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->status
            ]);

            return response()->json([
                'message' => 'Payment created successfully',
                'data' => $payment
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating payment',
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
            $payment = PaymentDetail::with('order')->findOrFail($id);

            if (!$this->canAccessResource($payment->order)) {
                return response()->json([
                    'message' => 'Access denied. You can only view your own payments.'
                ], 403);
            }

            return response()->json([
                'message' => 'Payment retrieved successfully',
                'data' => $payment
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Payment not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving payment',
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
                'status' => 'required|string|max:20|in:pending,completed,failed'
            ]);

            $payment = PaymentDetail::with('order')->findOrFail($id);

            if (!$this->canAccessResource($payment->order)) {
                return response()->json([
                    'message' => 'Access denied. You can only update your own payments.'
                ], 403);
            }

            $payment->payment_status = $request->status;
            $payment->save();

            return response()->json([
                'message' => 'Payment updated successfully',
                'data' => $payment
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Payment not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating payment',
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
            $payment = PaymentDetail::with('order')->findOrFail($id);

            if (!$this->canAccessResource($payment->order)) {
                return response()->json([
                    'message' => 'Access denied. You can only delete your own payments.'
                ], 403);
            }

            $payment->delete();

            return response()->json([
                'message' => 'Payment deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Payment not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
