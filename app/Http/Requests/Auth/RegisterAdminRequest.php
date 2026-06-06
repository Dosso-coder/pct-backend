<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['administrateur'])],
            'user_log_adm' => ['required', 'string', 'max:50'],
            'user_pasw_adm' => ['required', 'string', 'min:8'],
            'ann_aca' => ['required', 'integer'],
            'rol_usr' => ['required', 'string', 'max:50'],
            'para_cal' => ['required', 'numeric'],
            'coef_niv' => ['required', 'numeric'],
            'taux_hor' => ['required', 'numeric'],
        ];
    }
}
