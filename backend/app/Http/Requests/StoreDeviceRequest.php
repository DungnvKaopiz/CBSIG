<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_uid' => 'required|string|max:255|unique:devices,device_uid',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|integer|in:1,2,3,4,5',
            'ip_address' => 'nullable|ip|max:45',
            'firmware_version' => 'nullable|string|max:50',
            'canvas_width' => 'nullable|integer|min:1|max:7680',
            'canvas_height' => 'nullable|integer|min:1|max:4320',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_uid.required' => 'Device UID is required.',
            'device_uid.unique' => 'Device UID has already been taken.',
            'device_uid.max' => 'Device UID may not be greater than 255 characters.',
            'name.required' => 'Device name is required.',
            'name.max' => 'Device name may not be greater than 255 characters.',
            'location.max' => 'Location may not be greater than 255 characters.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be one of: 1 (online), 2 (offline), 3 (syncing), 4 (error), 5 (pending).',
            'ip_address.ip' => 'IP address must be a valid IP address.',
            'ip_address.max' => 'IP address may not be greater than 45 characters.',
            'firmware_version.max' => 'Firmware version may not be greater than 50 characters.',
            'canvas_width.integer' => 'Canvas width must be an integer.',
            'canvas_width.min' => 'Canvas width must be at least 1 pixel.',
            'canvas_width.max' => 'Canvas width may not be greater than 7680 pixels.',
            'canvas_height.integer' => 'Canvas height must be an integer.',
            'canvas_height.min' => 'Canvas height must be at least 1 pixel.',
            'canvas_height.max' => 'Canvas height may not be greater than 4320 pixels.',
        ];
    }
}

