<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\User;
use App\Traits\FiltersByRole;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use FiltersByRole;

    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(['role:customer'])->only(['store', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $query = Favorite::with(['customer', 'product']);
            $favorites = $this->applyRoleFilters($query)->get();

            return response()->json([
                'message' => 'Favorites retrieved successfully',
                'data' => $favorites
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving favorites',
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
                'product_id' => 'required|exists:products,id',
            ]);

            $user = auth()->user();

            // Check if favorite already exists
            $existingFavorite = Favorite::where('customer_id', $user->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingFavorite) {
                return response()->json([
                    'message' => 'Product is already in favorites',
                    'data' => $existingFavorite
                ], 400);
            }

            $favorite = Favorite::create([
                'customer_id' => $user->id,
                'product_id' => $request->product_id
            ]);

            return response()->json([
                'message' => 'Product added to favorites successfully',
                'data' => $favorite->load(['customer', 'product'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding to favorites',
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
            $favorite = Favorite::with(['customer', 'product'])->findOrFail($id);

            if (!$this->canAccessResource($favorite)) {
                return response()->json([
                    'message' => 'Access denied. You can only view your own favorites.'
                ], 403);
            }

            return response()->json([
                'message' => 'Favorite retrieved successfully',
                'data' => $favorite
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Favorite not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving favorite',
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
            $favorite = Favorite::findOrFail($id);

            if (!$this->canAccessResource($favorite)) {
                return response()->json([
                    'message' => 'Access denied. You can only delete your own favorites.'
                ], 403);
            }

            $favorite->delete();

            return response()->json([
                'message' => 'Favorite removed successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Favorite not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error removing favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
