<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterEnseignantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['enseignant'])],
            'user_log_adm' => ['required', 'string', 'max:50', 'exists:administrateur,user_log_adm'],
            'user_log_sp' => ['required', 'string', 'max:50', 'exists:secretaire_principal,user_log_sp'],
            'id_grade' => ['required', 'integer', 'exists:grades,id_grade'],
            'id_statut' => ['required', 'integer', 'exists:statut,id_statut'],
            'id_depart' => ['required', 'integer', 'exists:departement,id_depart'],
            'user_log_ens' => ['required', 'string', 'max:50'],
            'user_pasw_ens' => ['required', 'string', 'min:8'],
            'nom_ens' => ['required', 'string', 'max:100'],
            'pren_ens' => ['required', 'string', 'max:100'],
            'email_ens' => ['required', 'email', 'max:150'],
            'tel_ens' => ['required', 'string', 'max:20'],
            'taux_hor_ens' => ['required', 'numeric'],
        ];
    }
}
