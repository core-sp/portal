<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicitacaoRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return false;
    // }

    public function rules()
    {
        return [
            'modalidade' => 'required|max:191',
            'titulo' => 'required|max:191',
            'nrlicitacao' => 'required|max:191',
            'nrprocesso' => 'required|max:191',
            'situacao' => 'required|max:191',
            'objeto' => 'required',
            'datarealizacao' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'nrlicitacao.required' => 'O nº da licitação é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
    }
}
