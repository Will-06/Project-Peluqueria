<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isClient();
    }

    public function rules(): array
    {
        return [
            'haircut_id' => 'required|exists:haircuts,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.min' => 'La calificación debe ser al menos 1 estrella',
            'rating.max' => 'La calificación no puede ser mayor a 5 estrellas',
        ];
    }
}