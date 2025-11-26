<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayoutRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'canvas_width' => 'nullable|integer|min:1|max:7680',
            'canvas_height' => 'nullable|integer|min:1|max:4320',
            'frames' => 'nullable|array',
            'frames.*.id' => 'integer',
            'frames.*.name' => 'required_with:frames|string|max:255',
            'frames.*.content_id' => 'nullable|integer|exists:contents,id',
            'frames.*.x' => 'nullable|numeric|min:0',
            'frames.*.y' => 'nullable|numeric|min:0',
            'frames.*.width' => 'nullable|numeric|min:1',
            'frames.*.height' => 'nullable|numeric|min:1',
            'frames.*.z_index' => 'nullable|integer|min:0',
            'frames.*.image_fit' => 'nullable|string|in:contain,cover,fill,none,scale-down',
            'frames.*.order_index' => 'nullable|integer|min:0',
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
            'name.max' => 'Layout name may not be greater than 255 characters.',
            'description.max' => 'Description may not be greater than 1000 characters.',
            'canvas_width.integer' => 'Canvas width must be an integer.',
            'canvas_width.min' => 'Canvas width must be at least 1 pixel.',
            'canvas_width.max' => 'Canvas width may not be greater than 7680 pixels.',
            'canvas_height.integer' => 'Canvas height must be an integer.',
            'canvas_height.min' => 'Canvas height must be at least 1 pixel.',
            'canvas_height.max' => 'Canvas height may not be greater than 4320 pixels.',
            'frames.array' => 'Frames must be a valid array.',
            'frames.*.name.required_with' => 'Each frame must have a name.',
            'frames.*.name.max' => 'Frame name may not exceed 255 characters.',
            'frames.*.content_id.exists' => 'The selected content is invalid.',
            'frames.*.image_fit.in' => 'Image fit must be contain, cover, fill, none or scale-down.',
        ];
    }
}

