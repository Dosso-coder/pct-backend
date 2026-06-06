<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterSecretaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['secretaire'])],
            'user_log_sp' => ['required', 'string', 'max:50'],
            'user_log_adm' => ['required', 'string', 'max:50', 'exists:administrateur,user_log_adm'],
            'user_pasw_sp' => ['required', 'string', 'min:8'],
            'nom_sp' => ['required', 'string', 'max:100'],
            'pren_sp' => ['required', 'string', 'max:100'],
            'email_sp' => ['required', 'email', 'max:150'],
            'rol_sp' => ['required', 'string', 'max:50'],
        ];
    }
}
