<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Traits\FiltersByRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controller;

class ReviewController extends Controller
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
            $query = Review::query()->with('product'); // optional eager load
            $reviews = $this->applyRoleFilters($query)->get();

            return response()->json([
                'message' => 'Reviews retrieved successfully',
                'data'    => $reviews,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving reviews',
                'error'   => $e->getMessage(),
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
                'product_id' => 'required|integer|exists:products,id',
                'rating'     => 'required|integer|min:1|max:5',
                'comment'    => 'nullable|string',
            ]);

            $user = Auth::user();

            // Only customers can create reviews (admins manage but don't create)
            if ($user->role !== 'customer') {
                return response()->json([
                    'message' => 'Only customers can create reviews.',
                ], 403);
            }

            $review = Review::create([
                'product_id'  => $request->product_id,
                'customer_id' => $user->id,           // enforce ownership
                'rating'      => $request->rating,
                'comment'     => $request->comment,
            ]);

            return response()->json([
                'message' => 'Review created successfully',
                'data'    => $review,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating review',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $review = Review::with('product')->findOrFail($id);

            if (!$this->canAccessResource($review)) {
                return response()->json([
                    'message' => 'Access denied. You can only view your own reviews.',
                ], 403);
            }

            return response()->json([
                'message' => 'Review retrieved successfully',
                'data'    => $review,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Review not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving review',
                'error'   => $e->getMessage(),
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
                'rating'  => 'sometimes|required|integer|min:1|max:5',
                'comment' => 'sometimes|nullable|string',
            ]);

            $review = Review::findOrFail($id);

            if (!$this->canAccessResource($review)) {
                return response()->json([
                    'message' => 'Access denied. You can only update your own reviews.',
                ], 403);
            }

            $review->rating  = $request->input('rating',  $review->rating);
            $review->comment = $request->input('comment', $review->comment);
            $review->save();

            return response()->json([
                'message' => 'Review updated successfully',
                'data'    => $review,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Review not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating review',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);

            if (!$this->canAccessResource($review)) {
                return response()->json([
                    'message' => 'Access denied. You can only delete your own reviews.',
                ], 403);
            }

            $review->delete();

            return response()->json([
                'message' => 'Review deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Review not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting review',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
