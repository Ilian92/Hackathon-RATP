<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignSeverityRequest extends FormRequest
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
            'level' => ['required', 'integer', 'min:0', 'max:4'],
            'justification' => ['required', 'string', 'min:10', 'max:2000'],
            'negative' => ['nullable', 'boolean'],
        ];
    }
}
