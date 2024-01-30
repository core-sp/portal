<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class SuporteRequest extends FormRequest
{
    private $tiposAceitos;
    private $service;

    public function authorize()
    {
        $user = auth()->user();
        return $user->can('onlyAdmin', $user);
    }

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Suporte');
    }

    protected function prepareForValidation()
    {
        if(request()->filled('mes') || request()->filled('ano') || request()->filled('relat_mes') || request()->filled('relat_ano'))
            $this->tiposAceitos = 'interno,externo';
        else
            $this->tiposAceitos = 'erros,interno,externo';
    }

    public function rules()
    {
        if(\Route::is('suporte.log.externo.relatorios'))
            return [
                'relat_tipo' => 'required|in:'.$this->tiposAceitos,
                'relat_data' => 'required|in:mes,ano',
                'relat_mes' => 'exclude_if:relat_data,ano|required_if:relat_data,mes|date_format:Y-m|before_or_equal:'.date('Y-m').'|after:2018-12',
                'relat_ano' => 'exclude_if:relat_data,mes|required_if:relat_data,ano|date_format:Y|before_or_equal:'.date('Y').'|after:2018',
                'relat_opcoes' => 'required|in:'.implode(',', array_keys($this->service->filtros())),
            ];

        return [
            'tipo' => 'required_with:data,mes,ano,texto|in:'.$this->tiposAceitos,
            'data' => 'required_without_all:mes,ano,file|date|before_or_equal:'.date('Y-m-d', strtotime('yesterday')).'|after:2018-12-31',
            'mes' => 'required_without_all:data,ano,file|date_format:Y-m|before_or_equal:'.date('Y-m').'|after:2018-12',
            'ano' => 'required_without_all:data,mes,file|date_format:Y|before_or_equal:'.date('Y').'|after:2018',
            'texto' => 'required_with:mes,ano|min:3|max:191',
            'n_linhas' => 'nullable',
            'distintos' => 'nullable',
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
            'required_without_all' => 'Campo obrigatório',
            'required_with' => 'Campo obrigatório',
            'relat_mes.required_if' => 'Campo obrigatório se o mês foi escolhido',
            'relat_ano.required_if' => 'Campo obrigatório se o ano foi escolhido',
            'required' => 'Campo obrigatório',
            'mes.before_or_equal' => 'Deve selecionar um mês e ano anterior ou igual a hoje',
            'ano.before_or_equal' => 'Deve selecionar um ano anterior ou igual a hoje',
            'date' => 'Formato de data inválido',
            'date_format' => 'Formato de período inválido',
            'after' => 'Data deve ser a partir de 2019',
            'relat_data.in' => 'Deve ser mês ou ano',
            'relat_mes.before_or_equal' => 'Deve selecionar um mês e ano anterior ou igual a hoje',
            'relat_ano.before_or_equal' => 'Deve selecionar um ano anterior ou igual a hoje',
            'relat_opcoes.in' => 'Tipo de filtro não existente',
        ];
    }
}
