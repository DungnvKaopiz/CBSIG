<?php

namespace App\Http\Controllers;

// use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    /**
     * Get today's visitors
     */
    public function todayVisitors()
    {
        return response()->json([
            'message' => 'test',
        ], 200);
    }

    /**
     * Save a visitor
     */
    public function saveVisitor(Request $request)
    {
        return response()->json([
            'message' => $request->all(),
        ], 200);
    }
}

