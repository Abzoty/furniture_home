<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\User;
use App\Traits\FiltersByRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controller;

class EnquiryController extends Controller
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

            $enquiries = Enquiry::when($user->role === 'customer', function ($query) use ($user) {
                $query->where('customer_id', $user->id);
            })->get();

            return response()->json([
                'message' => 'Enquiries retrieved successfully',
                'data'    => $enquiries,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving enquiries',
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
                'content' => 'required|string',
            ]);

            $user = Auth::user();

            if ($user->role !== 'customer') {
                return response()->json([
                    'message' => 'Only customers can make an enquiry',
                ], 403);
            }

            $enquiry = Enquiry::create([
                'customer_id' => $user->id,
                'content'     => $request->content,
            ]);

            return response()->json([
                'message' => 'Enquiry added successfully',
                'data'    => $enquiry,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding enquiry',
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
            $enquiry = Enquiry::findOrFail($id);

            if (!$this->canAccessResource($enquiry)) {
                return response()->json([
                    'message' => 'Access denied. You can only view your own enquiries.',
                ], 403);
            }

            return response()->json([
                'message' => 'Enquiry retrieved successfully',
                'data'    => $enquiry,
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Enquiry not found'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving enquiry',
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
                'content' => 'sometimes|required|string',
            ]);

            $enquiry = Enquiry::findOrFail($id);

            if (!$this->canAccessResource($enquiry)) {
                return response()->json([
                    'message' => 'Access denied. You can only update your own enquiries.',
                ], 403);
            }

            $enquiry->content = $request->input('content', $enquiry->content);
            $enquiry->save();

            return response()->json([
                'message' => 'Enquiry updated successfully',
                'data'    => $enquiry,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Enquiry not found'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating enquiry',
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
            $enquiry = Enquiry::findOrFail($id);

            if (!$this->canAccessResource($enquiry)) {
                return response()->json([
                    'message' => 'Access denied. You can only delete your own enquiries.',
                ], 403);
            }

            $enquiry->delete();

            return response()->json([
                'message' => 'Enquiry deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Enquiry not found'], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting enquiry',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
