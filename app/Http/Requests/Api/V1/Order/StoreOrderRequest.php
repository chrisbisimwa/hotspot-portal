<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_profile_id' => ['required', 'integer', 'exists:user_profiles,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            // TODO: Add stock validation when needed
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'user_profile_id.required' => 'Profil utilisateur requis',
            'user_profile_id.exists' => 'Profil utilisateur invalide',
            'quantity.required' => 'Quantité requise',
            'quantity.min' => 'Quantité doit être au moins 1',
        ];
    }
}