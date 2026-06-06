<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['administrateur', 'secretaire', 'enseignant'])],
            'login' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }
}
