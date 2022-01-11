<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CursoRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return false;
    // }

    public function rules()
    {
        return [
            'tipo' => 'max:255',
            'tema' => 'required|max:255',
            'img' => 'max:255',
            'datarealizacao' => 'required',
            'datatermino' => 'required',
            'endereco' => 'required_unless:tipo,Live|max:255',
            'nrvagas' => 'required|numeric',
            'descricao' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização do curso',
            'datatermino.required' => 'Informe a data de término do curso',
            'numeric' => 'O :attribute aceita apenas números',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
    }
}
