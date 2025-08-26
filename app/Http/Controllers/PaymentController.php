<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentDetail;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = PaymentDetail::all();
        return response()->json($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'payment_method' => 'required|string|max:50',
            'status' => 'required|string|max:20|in:pending,completed,failed'
        ]);

        try {
            $payment = new PaymentDetail();
            $payment->order_id = $request->order_id;
            $payment->amount = Order::findOrFail($request->order_id)->total_amount;
            $payment->payment_method = $request->payment_method;
            $payment->payment_status = $request->status;
            $payment->save();

            return response()->json($payment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create payment', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $payment = PaymentDetail::find($id);
        if ($payment) {
            return response()->json($payment);
        }
        return response()->json(['message' => 'Payment not found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:20|in:pending,completed,failed'
        ]);

        $payment = PaymentDetail::find($id);
        if ($payment) {
            $payment->payment_status = $request->status;
            $payment->save();
            return response()->json($payment);            
        }
        return response()->json(['message' => 'Payment not found'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $payment = PaymentDetail::find($id);
        if ($payment) {
            $payment->delete();
            return response()->json(['message' => 'Payment deleted successfully']);
        }
        return response()->json(['message' => 'Payment not found'], 404);
    }
}
