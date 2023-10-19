<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\Cpf;

class CursoInscricaoRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Curso');
    }

    protected function prepareForValidation()
    {
        
    }

    public function rules()
    {
        return [
            'cpf' => [
                'required', 
                'max:191', 
                'unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$this->idcurso.',deleted_at,NULL', 
                new Cpf
            ],
            'nome' => 'required|max:191',
            'telefone' => 'required|max:18|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})/',
            'email' => 'required|email|max:191',
            'registrocore' => 'nullable|max:191',
            'tipo_inscrito' => 'required|in:' . implode(',', $this->service->inscritos()->tiposInscricao()),
        ];
    }

    public function messages()
    {
        return [
            'cpf.unique' => 'Este CPF já está cadastrado para o curso',
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'telefone.regex' => 'Telefone no formato inválido',
            'email' => 'Digite um email válido',
            'in' => 'Valor inválido',
        ];
    }
}
