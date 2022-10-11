<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PagamentoGetnetRequest extends FormRequest
{
    private $tiposAceitos;

    protected function prepareForValidation()
    {
        // if(request()->filled('mes') || request()->filled('ano'))
        //     $this->tiposAceitos = 'interno,externo';
        // else
        //     $this->tiposAceitos = 'erros,interno,externo';
    }

    public function rules()
    {
        return [
            // 'tipo' => 'required_with:data,mes,ano,texto|in:'.$this->tiposAceitos,
            // 'data' => 'required_without_all:mes,ano,file|date|before_or_equal:'.date('Y-m-d', strtotime('yesterday')).'|after:2018-12-31',
            // 'mes' => 'required_without_all:data,ano,file|date_format:Y-m|before_or_equal:'.date('Y-m').'|after:2018-12',
            // 'ano' => 'required_without_all:data,mes,file|date_format:Y|before_or_equal:'.date('Y').'|after:2018',
            // 'texto' => 'required_with:mes,ano|min:3|max:191',
            // 'n_linhas' => 'nullable',
            // 'file' => 'file|mimetypes:text/plain',
        ];
    }

    public function messages()
    {
        return [
            // 'data.before_or_equal' => 'Deve selecionar uma data anterior a hoje',
            // 'min' => 'O texto não pode ser menor que 3 caracteres',
            // 'max' => 'O texto não pode ser maior que 191 caracteres',
            // 'file' => 'Deve ser um arquivo válido',
            // 'mimetypes' => 'Somente arquivo com a extensão .txt',
            // 'in' => 'Tipo de log não existente',
            // 'required_without_all' => 'Campo obrigatório',
            // 'required_with' => 'Campo obrigatório',
            // 'mes.before_or_equal' => 'Deve selecionar um mês e ano anterior ou igual a hoje',
            // 'ano.before_or_equal' => 'Deve selecionar um ano anterior ou igual a hoje',
            // 'date' => 'Formato de data inválido',
            // 'date_format' => 'Formato de período inválido',
            // 'after' => 'Data deve ser a partir de 2019'
        ];
    }
}
