<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function getTemplates(Request $request)
    {
        return response()->json([
            'message' => 'Templates fetched successfully',
        ]);
    }

    public function saveTemplate(Request $request)
    {
        return response()->json([
            'message' => 'Template saved successfully',
        ]);
    }
    
    public function updateTemplate(Request $request, $id)
    {
        return response()->json([
            'message' => 'Template updated successfully',
        ]);
    }

    public function deleteTemplate(Request $request, $id)
    {
        return response()->json([
            'message' => 'Template deleted successfully',
        ]);
    }
}

