<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct()
    {
        // Admin-only routes
        $this->middleware(['auth:sanctum', 'role:admin'])->only(['store', 'update', 'destroy']);
        
        // Public routes for customers (index, show) - no auth required
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::with(['categories', 'images'])->get();

            return response()->json([
                'message' => 'Products retrieved successfully',
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving products',
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
                'name' => 'required|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'required|numeric|min:0|max:99999999.99',
                'categories' => 'sometimes|array|max:10',
                'categories.*.name' => 'required|string|max:255|exists:categories,name',
                'images' => 'sometimes|array|max:10',
                'images.*.url' => 'required|url|max:2048',
            ]);

            DB::beginTransaction();

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description ?? '',
                'price' => $request->price
            ]);

            // Create categories
            if ($request->has('categories') && is_array($request->categories)) {
                foreach ($request->categories as $categoryData) {
                    $category = Category::where('name', $categoryData['name'])->first();
                    if ($category) {
                        ProductCategory::create([
                            'product_id' => $product->id,
                            'category_id' => $category->id
                        ]);
                    }
                }
            }

            // Create images
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $imageData) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $imageData['url']
                    ]);
                }
            }

            DB::commit();

            $product->load(['categories', 'images']);

            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error creating product',
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
            $product = Product::with(['categories.category', 'images'])->findOrFail($id);

            return response()->json([
                'message' => 'Product retrieved successfully',
                'data' => $product
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving product',
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
                'name' => 'required|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'required|numeric|min:0|max:99999999.99',
                'categories' => 'sometimes|array|max:10',
                'categories.*.name' => 'required|string|max:255|exists:categories,name',
                'images' => 'sometimes|array|max:10',
                'images.*.url' => 'required|url|max:2048',
            ]);

            $product = Product::findOrFail($id);

            DB::beginTransaction();

            $product->update([
                'name' => $request->name,
                'description' => $request->description ?? $product->description,
                'price' => $request->price
            ]);

            // Update categories if provided
            if ($request->has('categories')) {
                // Delete existing categories
                ProductCategory::where('product_id', $product->id)->delete();
                
                // Create new categories
                if (is_array($request->categories)) {
                    foreach ($request->categories as $categoryData) {
                        $category = Category::where('name', $categoryData['name'])->first();
                        if ($category) {
                            ProductCategory::create([
                                'product_id' => $product->id,
                                'category_id' => $category->id
                            ]);
                        }
                    }
                }
            }

            // Update images if provided
            if ($request->has('images')) {
                // Delete existing images
                ProductImage::where('product_id', $product->id)->delete();
                
                // Create new images
                if (is_array($request->images)) {
                    foreach ($request->images as $imageData) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $imageData['url']
                        ]);
                    }
                }
            }

            DB::commit();

            $product->load(['categories.category', 'images']);

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error updating product',
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
            $product = Product::findOrFail($id);
            
            DB::beginTransaction();
            
            // Delete related records
            ProductCategory::where('product_id', $id)->delete();
            ProductImage::where('product_id', $id)->delete();
            
            $product->delete();
            
            DB::commit();

            return response()->json([
                'message' => 'Product deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error deleting product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
