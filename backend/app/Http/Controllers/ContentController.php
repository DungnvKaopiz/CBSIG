<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Contents fetched successfully',
        ]);
    }

    public function show(Request $request, $id)
    {
        return response()->json([
            'message' => 'Content fetched successfully',
            'id' => $id,
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Content created successfully',
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Content updated successfully',
            'id' => $id,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json([
            'message' => 'Content deleted successfully',
            'id' => $id,
        ]);
    }
}

