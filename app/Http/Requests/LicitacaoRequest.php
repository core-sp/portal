<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class LicitacaoRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Licitacao');
    }

    private function getRegrasBusca()
    {
        if(\Route::is('licitacoes.siteBusca'))
            return [
                'palavrachave' => 'nullable|max:191',
                'modalidade' => 'nullable|in:' . implode(',', $this->service->getModalidades()),
                'nrlicitacao' => 'nullable|regex:/[0-9]{1,3}\/[0-9]{4}/',
                'nrprocesso' => 'nullable|regex:/[0-9]{1,3}\/[0-9]{2,4}/',
                'situacao' => 'nullable|in:' . implode(',', $this->service->getSituacoes()),
                'datarealizacao' => 'nullable|date',
            ];

        return null;
    }

    protected function prepareForValidation()
    {
        if(!\Route::is('licitacoes.siteBusca'))
            if(isset(request()->datarealizacao))
                $this->merge(['datarealizacao' => str_replace('T', ' ', request()->datarealizacao)]);
    }

    public function rules()
    {
        $regras = $this->getRegrasBusca();
        if(isset($regras))
            return $regras;

        return [
            'uasg' => 'max:191',
            'edital' => 'max:191',
            'modalidade' => 'required|in:' . implode(',', $this->service->getModalidades()),
            'titulo' => 'required|max:191',
            'nrlicitacao' => 'required|regex:/[0-9]{1,3}\/[0-9]{4}/',
            'nrprocesso' => 'required|regex:/[0-9]{1,3}\/[0-9]{2,4}/',
            'situacao' => 'required|in:' . implode(',', $this->service->getSituacoes()),
            'objeto' => 'required',
            'datarealizacao' => 'required|date_format:Y-m-d H:i',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Campo é obrigatório',
            'max' => 'O campo excedeu o limite de :max caracteres',
            'date_format' => 'Formato inválido',
            'modalidade.in' => 'Modalidade não encontrada',
            'situacao.in' => 'Situação não encontrada',
            'nrlicitacao.regex' => 'Formato válido: 001/1900',
            'nrprocesso.regex' => 'Formato válido: 001/00 ou 001/1900',
            'date' => 'Deve ser uma data válida'
        ];
    }
}
