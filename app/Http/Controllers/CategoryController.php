<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
        //return view('categories.index', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $test_category = Category::where('name', $request->input('name'))->first();
        if ($test_category) {
            return response()->json(['message' => 'Category already exists'], 409);
        }
        
        try{
            $category = new Category();
            $category->name = $request->input('name');
            $category->save();

            return response()->json(['message' => 'Category created successfully'], 201);
        }
        catch(\Exception $e) {
            return response()->json(['message' => 'Error adding Category' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::find($id);
        if ($category) {
            return response()->json($category);
        }
        return response()->json(['message' => 'Category not found'], 404);
        //return view('categories.show', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $category = Category::find($id);
        if ($category) {
            $category->name = $request->input('name');
            $category->save();
            return response()->json(['message' => 'Category updated successfully'], 200);
        }

        return response()->json(['message' => 'Category not found'], 404);
        //return redirect()->route('categories.show', ['id' => $id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully'], 200);
        }
        return response()->json(['message' => 'Category not found'], 404);
        //return redirect()->route('categories.index');
    }
}
