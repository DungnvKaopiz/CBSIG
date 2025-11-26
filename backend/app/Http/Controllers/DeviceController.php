<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $devices = $this->deviceService->getAll();
        
        return response()->json([
            'message' => 'Devices fetched successfully',
            'data' => $devices,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        return response()->json([
            'message' => 'Device fetched successfully',
            'data' => $device,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeviceRequest $request)
    {
        $device = $this->deviceService->create($request->validated());

        return response()->json([
            'message' => 'Device created successfully',
            'data' => $device,
        ], 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeviceRequest $request, Device $device)
    {
        $test = $device->get();
        $device = $this->deviceService->update($device, $request->validated());

        return response()->json([
            'message' => 'Device updated successfully',
            'data' => $device,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        $this->deviceService->delete($device);

        return response()->json([
            'message' => 'Device deleted successfully',
        ]);
    }
}

