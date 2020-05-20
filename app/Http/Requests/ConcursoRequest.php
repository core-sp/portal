<?php

namespace App\Http\Requests;

use App\Traits\ControleAcesso;
use Illuminate\Foundation\Http\FormRequest;

class ConcursoRequest extends FormRequest
{
    use ControleAcesso;

    public function authorize()
    {
        return $this->autoriza('ConcursoController', 'create');
    }

    public function rules()
    {
        $this->method() === 'POST' ? $unique = 'unique:concursos' : $unique = 'unique:concursos,nrprocesso,'.$this->id.',idconcurso';
        return [
            'modalidade' => 'required|max:191',
            'titulo' => 'required|max:191',
            'nrprocesso' => 'required|max:191|'.$unique,
            'situacao' => 'required|max:191',
            'datarealizacao' => 'required',
            'objeto' => 'required',
            'linkexterno' => 'max:255'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'nrprocesso.unique' => 'Já existe um concurso com este nº de processo',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
    }
}
