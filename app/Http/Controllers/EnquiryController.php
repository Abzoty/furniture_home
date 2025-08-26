<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enquiries = Enquiry::all();
        return response()->json($enquiries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:users,id',
            'content' => 'required|string',
        ]);

        // Ensure the user is a customer
        $customer = User::find($request->input('customer_id'));
        if ($customer->role !== 'customer') {
            return response()->json(['message' => 'Only customers can make an enquiry'], 400);
        }

        try {
            $enquiry = new Enquiry();
            $enquiry->customer_id = $request->input('customer_id');
            $enquiry->content = $request->input('content');
            $enquiry->save();

            return response()->json(['message' => 'Enquiry added successfully', 'enquiry' => $enquiry], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding enquiry: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $enquiry = Enquiry::find($id);
        if ($enquiry) {
            return response()->json($enquiry);
        }
        return response()->json(['message' => 'Enquiry not found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'sometimes|required|string',
        ]);

        // TODO:Ensure the user is a customer

        $enquiry = Enquiry::find($id);
        if ($enquiry) {
            $enquiry->content = $request->input('content', $enquiry->content);
            $enquiry->save();
            return response()->json(['message' => 'Enquiry updated successfully', 'enquiry' => $enquiry]);
        }
        return response()->json(['message' => 'Enquiry not found'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $enquiry = Enquiry::find($id);
        if ($enquiry) {
            $enquiry->delete();
            return response()->json(['message' => 'Enquiry deleted successfully']);
        }
        return response()->json(['message' => 'Enquiry not found'], 404);
    }
}
