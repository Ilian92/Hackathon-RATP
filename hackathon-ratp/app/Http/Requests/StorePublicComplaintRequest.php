<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'ligne_id' => ['required', 'integer', 'exists:lignes,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'heure' => ['required', 'date_format:H:i'],
            'complaint_type_id' => ['required', 'integer', 'exists:complaint_types,id'],
            'arret_fin_id' => ['nullable', 'integer', 'exists:arrets,id'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ligne_id' => 'ligne de bus',
            'date' => 'date de l\'incident',
            'heure' => 'heure de l\'incident',
            'complaint_type_id' => 'type de plainte',
        ];
    }
}
