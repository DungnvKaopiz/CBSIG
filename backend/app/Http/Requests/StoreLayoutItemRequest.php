<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLayoutItemRequest extends FormRequest
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
            'layout_id' => 'required|integer|exists:layouts,id',
            'name' => 'required|string|max:255',
            'content_id' => 'nullable|integer|exists:contents,id',
            'frame_metadata' => 'nullable|array',
            'frame_metadata.x' => 'nullable|numeric|min:0',
            'frame_metadata.y' => 'nullable|numeric|min:0',
            'frame_metadata.width' => 'nullable|numeric|min:1',
            'frame_metadata.height' => 'nullable|numeric|min:1',
            'frame_metadata.z_index' => 'nullable|integer|min:0',
            'frame_metadata.image_fit' => 'nullable|string|in:contain,cover,fill,none,scale-down',
            'frame_metadata.order_index' => 'nullable|integer|min:0',
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
            'layout_id.required' => 'Layout ID is required.',
            'layout_id.exists' => 'The selected layout does not exist.',
            'name.required' => 'Frame name is required.',
            'name.max' => 'Frame name may not be greater than 255 characters.',
            'content_id.exists' => 'The selected content does not exist.',
            'frame_metadata.array' => 'Frame metadata must be an array.',
            'frame_metadata.image_fit.in' => 'Image fit must be one of: contain, cover, fill, none, scale-down.',
        ];
    }
}

