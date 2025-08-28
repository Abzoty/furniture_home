<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Traits\FiltersByRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class StoreSettingController extends Controller
{
    use FiltersByRole;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin'])->except(['index', 'show']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $storeSettings = StoreSetting::all();

            return response()->json([
                'message' => 'Store settings retrieved successfully',
                'data' => $storeSettings
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving store settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $user = Auth::user();

            // Only admin can create store settings
            if ($user->role !== 'admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'logo_url' => 'required|string|max:500',
                'about_image_url' => 'required|string|max:500',
                'about_description' => 'required|string',
                'terms_and_conditions' => 'required|string',
                'facebook_url' => 'required|string|max:255',
                'whatsapp_number' => 'required|string|max:20',
                'phone_number' => 'required|string|max:20',
                'second_phone_number' => 'required|string|max:20',
            ]);

            $storeSetting = new StoreSetting();
            $storeSetting->name = $request->input('name');
            $storeSetting->logo_url = $request->input('logo_url');
            $storeSetting->about_image_url = $request->input('about_image_url');
            $storeSetting->about_description = $request->input('about_description');
            $storeSetting->terms_and_conditions = $request->input('terms_and_conditions');
            $storeSetting->facebook_url = $request->input('facebook_url');
            $storeSetting->whatsapp_number = $request->input('whatsapp_number');
            $storeSetting->phone_number = $request->input('phone_number');
            $storeSetting->second_phone_number = $request->input('second_phone_number');            
            $storeSetting->save();

            return response()->json(['message' => 'Store setting created successfully', 'data' => $storeSetting], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating store setting: ' . $e->getMessage()], 500);
        
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $storeSetting = StoreSetting::find($id);
        if ($storeSetting) {
            return response()->json($storeSetting);
        }
        return response()->json(['message' => 'Store setting not found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Only admin can update store settings
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'logo_url' => 'sometimes|required|string|max:500',
            'about_image_url' => 'sometimes|required|string|max:500',
            'about_description' => 'sometimes|required|string',
            'terms_and_conditions' => 'sometimes|required|string',
            'facebook_url' => 'sometimes|required|string|max:255',
            'whatsapp_number' => 'sometimes|required|string|max:20',
            'phone_number' => 'sometimes|required|string|max:20',
            'second_phone_number' => 'sometimes|required|string|max:20',
        ]);

        $storeSetting = StoreSetting::find($id);
        if (!$storeSetting) {
            return response()->json(['message' => 'Store setting not found'], 404);
        }

        try {
            $storeSetting->name = $request->input('name', $storeSetting->name);
            $storeSetting->logo_url = $request->input('logo_url', $storeSetting->logo_url);
            $storeSetting->about_image_url = $request->input('about_image_url', $storeSetting->about_image_url);
            $storeSetting->about_description = $request->input('about_description', $storeSetting->about_description);
            $storeSetting->terms_and_conditions = $request->input('terms_and_conditions', $storeSetting->terms_and_conditions);
            $storeSetting->facebook_url = $request->input('facebook_url', $storeSetting->facebook_url);
            $storeSetting->whatsapp_number = $request->input('whatsapp_number', $storeSetting->whatsapp_number);
            $storeSetting->phone_number = $request->input('phone_number', $storeSetting->phone_number);
            $storeSetting->second_phone_number = $request->input('second_phone_number', $storeSetting->second_phone_number);
            $storeSetting->save();

            return response()->json(['message' => 'Store setting updated successfully', 'data' => $storeSetting], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating store setting: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        // Only admin can delete store settings
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $storeSetting = StoreSetting::find($id);
        if ($storeSetting) {
            $storeSetting->delete();
            return response()->json(['message' => 'Store setting deleted successfully']);
        }
        return response()->json(['message' => 'Store setting not found'], 404);
    }
}
