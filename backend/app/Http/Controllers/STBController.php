<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class STBController extends Controller
{
    public function stbPoll(Request $request)
    {
        return response()->json([
            'message' => 'STB polled successfully',
        ]);
    }

    public function stbConfig(Request $request, $id)
    {
        return response()->json([
            'message' => 'STB config fetched successfully',
        ]);
    }
}

