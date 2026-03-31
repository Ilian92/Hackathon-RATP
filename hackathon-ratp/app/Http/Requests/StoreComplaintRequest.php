<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
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
            'description' => ['required', 'string', 'max:2000'],
            'complaint_type_id' => ['required', 'integer', 'exists:complaint_types,id'],
            'bus_code' => ['required', 'string', 'exists:buses,code'],
            'scanned_at' => ['required', 'date'],
        ];
    }
}
