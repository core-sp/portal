<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class GeralRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if(\Route::is('consultaSituacao.post'))
            $this->merge(['cpfCnpj' => apenasNumeros($this->cpfCnpj)]);
    }

    public function rules()
    {
        if(\Route::is('site.busca'))
            return [
                'busca' => 'required|min:3'
            ];

        if(\Route::is('consultaSituacao.post'))
            return [
                'cpfCnpj' => ['required', new CpfCnpj],
            ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de :max caracteres permitidos',
            'min' => 'O :attribute deve ter pelo menos :min caracteres',
        ];
    }
}
