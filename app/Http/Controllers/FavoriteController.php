<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $favorites = Favorite::all();
        return response()->json($favorites);
        // return view('favorites.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        // Check if the customer_id corresponds to a user with the role of 'customer'
        $customerId = $request->input('customer_id');
        $user = User::find($customerId);
        if (!$user || $user->role !== 'customer') {
            return response()->json(['message' => 'this user is not a customer'], 401);
        }
        

        try {
            $favorite = new Favorite();
            $favorite->customer_id = $request->input('customer_id');
            $favorite->product_id = $request->input('product_id');
            $favorite->save();

            return response()->json($favorite, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding Favorite' . $e->getMessage()], 500);
        }
        // return redirect()->route('favorites.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $favorite = Favorite::find($id);
        if ($favorite) {
            return response()->json($favorite);
        }
        return response()->json(['message' => 'Favorite not found'], 404);
        //return view('favorites.show', compact('favorite'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $favorite = Favorite::find($id);
        if ($favorite) {
            $favorite->delete();
            return response()->json(['message' => 'Favorite deleted successfully']);
        }
        return response()->json(['message' => 'Favorite not found'], 404);
        // return redirect()->route('favorites.index');
    }
}
