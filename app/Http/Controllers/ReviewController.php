<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = Review::all();
        return response()->json($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'customer_id' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // Ensure the user is a customer
        $customer = User::find($request->input('customer_id'));
        if ($customer->role !== 'customer') {
            return response()->json(['message' => 'Only customers can make a review'], 400);
        }

        try {
            $review = Review::create($request->all());
            return response()->json($review, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create review', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $review = Review::find($id);
        if ($review) {
            return response()->json($review);
        }
        return response()->json(['error' => 'Review not found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|nullable|string'
        ]);

        // TODO:Ensure the user is a customer

        $review = Review::find($id);
        if ($review) {
            $review->rating = $request->input('rating', $review->rating);
            $review->comment = $request->input('comment', $review->comment);
            return response()->json($review);
        }
        return response()->json(['error' => 'Review not found'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $review = Review::find($id);
        if ($review) {
            $review->delete();
            return response()->json(['message' => 'Review deleted successfully']);
        }
        return response()->json(['error' => 'Review not found'], 404);
    }
}
