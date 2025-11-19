<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function getDevices(Request $request)
    {
        return response()->json([
            'message' => 'Devices fetched successfully',
        ]);
    }

    public function saveDevice(Request $request)
    {
        return response()->json([
            'message' => 'Device saved successfully',
        ]);
    }
    
    public function updateDevice(Request $request, $id)
    {
        return response()->json([
            'message' => 'Device updated successfully',
        ]);
    }

    public function deleteDevice(Request $request, $id)
    {
        return response()->json([
            'message' => 'Device deleted successfully',
        ]);
    }   
    
    
}

