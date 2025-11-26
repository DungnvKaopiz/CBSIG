<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Str;

class DeviceService
{
    /**
     * Get all devices
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return Device::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get device by ID
     *
     * @param int $id
     * @return Device|null
     */
    public function find(int $id)
    {
        return Device::find($id);
    }

    /**
     * Create a new device
     *
     * @param array $data
     * @return Device
     */
    public function create(array $data): Device
    {
        // Generate unique API key
        $apiKey = $this->generateUniqueApiKey();

        // Prepare device data with defaults
        $deviceData = [
            'device_uid' => $data['device_uid'],
            'name' => $data['name'],
            'location' => $data['location'] ?? null,
            'status' => $data['status'] ?? 5, // Default to pending
            'ip_address' => $data['ip_address'] ?? null,
            'api_key' => $apiKey,
            'firmware_version' => $data['firmware_version'] ?? null,
            'canvas_width' => $data['canvas_width'] ?? 1280,
            'canvas_height' => $data['canvas_height'] ?? 720,
        ];

        return Device::create($deviceData);
    }

    /**
     * Update a device
     *
     * @param Device $device
     * @param array $data
     * @return Device
     */
    public function update(Device $device, array $data): Device
    {
        $device->update($data);
        return $device->fresh();
    }

    /**
     * Delete a device
     *
     * @param Device $device
     * @return bool
     */
    public function delete(Device $device): bool
    {
        return $device->delete();
    }

    /**
     * Generate a unique API key
     *
     * @return string
     */
    protected function generateUniqueApiKey(): string
    {
        $apiKey = 'sk_live_' . Str::random(32);
        
        // Ensure API key is unique
        while (Device::where('api_key', $apiKey)->exists()) {
            $apiKey = 'sk_live_' . Str::random(32);
        }

        return $apiKey;
    }
}

