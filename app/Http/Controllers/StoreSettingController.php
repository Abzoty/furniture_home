<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;

class StoreSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $storeSettings = StoreSetting::all();
        return response()->json($storeSettings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

        //TODO: chick if user is admin

        try {
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

            return response()->json(['message' => 'Store setting added successfully', 'data' => $storeSetting], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding store setting: ' . $e->getMessage()], 500);
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

        //TODO: chick if user is admin

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
        $storeSetting = StoreSetting::find($id);
        if ($storeSetting) {
            $storeSetting->delete();
            return response()->json(['message' => 'Store setting deleted successfully']);
        }
        return response()->json(['message' => 'Store setting not found'], 404);
    }
}
