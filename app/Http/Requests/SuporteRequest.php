<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuporteRequest extends FormRequest
{
    private $tiposAceitos;

    protected function prepareForValidation()
    {
        if(request()->filled('mes') || request()->filled('ano'))
            $this->tiposAceitos = 'interno,externo';
        else
            $this->tiposAceitos = 'erros,interno,externo';
    }

    public function rules()
    {
        return [
            'tipo' => 'required|in:'.$this->tiposAceitos,
            'data' => 'sometimes|required|date|before_or_equal:'.date('Y-m-d', strtotime('yesterday')),
            'mes' => 'sometimes|required|date_format:Y-m|before_or_equal:'.date('Y-m'),
            'ano' => 'sometimes|required|date_format:Y|before_or_equal:'.date('Y'),
            'texto' => 'sometimes|required|min:3|max:191',
            'n_linhas' => 'nullable',
            'file' => 'file|mimetypes:text/plain',
        ];
    }

    public function messages()
    {
        return [
            'data.before_or_equal' => 'Deve selecionar uma data anterior a hoje',
            'min' => 'O texto não pode ser menor que 3 caracteres',
            'max' => 'O texto não pode ser maior que 191 caracteres',
            'file' => 'Deve ser um arquivo válido',
            'mimetypes' => 'Somente arquivo com a extensão .txt',
            'in' => 'Tipo de log não existente',
            'required' => 'Campo obrigatório',
            'mes.before_or_equal' => 'Deve selecionar um mês e ano anterior ou igual a hoje',
            'ano.before_or_equal' => 'Deve selecionar um ano anterior ou igual a hoje',
            'date' => 'Formato de data inválida',
            'date_format' => 'Formato de período inválido'
        ];
    }
}
