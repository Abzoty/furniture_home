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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        foreach ($products as $product) {
            $product->image = ProductImage::where('product_id', $product->id)->first()->value('image_url');
        }

        return response()->json($products);
        // return view('products.index', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string',
            'price' => 'required|numeric|min:0|max:99999999.99',

            // Categories validation - array of category objects/data
            'categories' => 'array|max:10',
            'categories.*.name' => 'required|string|max:255|exists:categories,name',

            // Images validation - array of image objects/data
            'images' => 'array|max:10',
            'images.*.url' => 'required|url|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Create the product
            $product = new Product();
            $product->name = $request->input('name');
            $product->description = $request->input('description');
            $product->price = $request->input('price');
            $product->save();

            // Create categories
            if ($request->has('categories') && is_array($request->categories)) {
                foreach ($request->categories as $categoryData) {
                    $category = new ProductCategory();
                    $category->product_id = $product->id;
                    $category->category_id = Category::where('name', $categoryData['name'])->value('id');
                    $category->save();
                }
            }

            // Create images
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $imageData) {
                    $image = new ProductImage();
                    $image->product_id = $product->id;
                    $image->image_url = $imageData['url'];
                    $image->save();
                }
            }

            DB::commit();

            // Load relationships for response
            $product->load(['categories', 'images']);

            return response()->json([
                'message' => 'Product added successfully',
                'product' => $product
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error adding product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product_categories = ProductCategory::where('product_id', $id)->get();
            $product_images = ProductImage::where('product_id', $id)->get();
            return response()->json([
                'product' => $product,
                'product_categories' => $product_categories,
                'product_images' => $product_images,
            ]);
        }
        return response()->json(['message' => 'Product not found'], 404);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string',
            'price' => 'required|numeric|min:0|max:99999999.99',

            // Categories validation - array of category objects/data
            'categories' => 'array|max:10',
            'categories.*.name' => 'required|string|max:255|exists:categories,name',

            // Images validation - array of image objects/data
            'images' => 'array|max:10',
            'images.*.url' => 'required|url|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::find($id);
            if (!$product) {
                DB::rollback();
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Update product details
            $product->name = $request->input('name');
            $product->description = $request->input('description');
            $product->price = $request->input('price');
            $product->save();

            // Delete existing categories and images (more efficient way)
            ProductCategory::where('product_id', $product->id)->delete();
            ProductImage::where('product_id', $product->id)->delete();

            // Create new categories
            if ($request->has('categories') && is_array($request->categories)) {
                foreach ($request->categories as $categoryData) {
                    $category = new ProductCategory();
                    $category->product_id = $product->id;
                    $category->category_id = Category::where('name', $categoryData['name'])->value('id');
                    $category->save();
                }
            }

            // Create new images
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $imageData) {
                    $image = new ProductImage();
                    $image->product_id = $product->id;
                    $image->image_url = $imageData['url'];
                    $image->save();
                }
            }

            DB::commit();

            $product->load(['categories', 'images']);
            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error updating product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully']);
        }
        return response()->json(['message' => 'Product not found'], 404);
    }
}
