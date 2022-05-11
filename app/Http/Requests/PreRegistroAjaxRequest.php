<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreRegistroAjaxRequest extends FormRequest
{
    public function rules()
    {
        return [
            'valor' => 'max:191',
            'campo' => 'required',
            'classe' => 'required|in:Anexo,Contabil,PreRegistro,PreRegistroCpf,PreRegistroCnpj,ResponsavelTecnico'
        ];
    }

    public function messages()
    {
        return [
        ];
    }
}
