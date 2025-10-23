<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHaircutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'featured_image_url' => 'required|url|max:500',
            'is_published' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'images' => 'array',
            'images.*.image_url' => 'required|url|max:500',
            'images.*.order' => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del corte es obligatorio',
            'featured_image_url.required' => 'La imagen principal es obligatoria',
            'tags.*.exists' => 'Uno o más tags no existen en el sistema',
        ];
    }
}