<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class LicitacaoRequest extends FormRequest
{
    private $service;
    private $regraRegex;
    private $regraRegexBusca;
    private $regraRegexNl;
    private $regraRegexBuscaNl;
    private $msgRegex;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Licitacao');
    }

    protected function prepareForValidation()
    {
        $this->regraRegexNl = 'regex:/^[0-9]{1,5}\/[0-9]{4}$/';
        $this->regraRegex = 'regex:/^[0-9]{1,3}\/[0-9]{4}$/';
        $this->regraRegexBuscaNl = 'regex:/^[0-9]{1,5}\/[0-9]{2,4}$/';
        $this->regraRegexBusca = 'regex:/^[0-9]{1,3}\/[0-9]{2,4}$/';
        $this->msgRegex = 'Formato válido: n° processo 001/1900 e n° licitação 00001/1900';

        if(!\Route::is('licitacoes.siteBusca'))
            if(isset(request()->datarealizacao))
                $this->merge(['datarealizacao' => str_replace('T', ' ', request()->datarealizacao)]);
    }

    public function rules()
    {
        if(\Route::is('licitacoes.siteBusca'))
        {
            $this->msgRegex = 'Formato válido: 1/00 ou 1/000 ou 1/0000';
            return [
                'palavrachave' => 'nullable|max:191',
                'modalidade' => 'nullable|in:' . implode(',', $this->service->getModalidades()),
                'nrlicitacao' => 'nullable|' . $this->regraRegexBuscaNl,
                'nrprocesso' => 'nullable|' . $this->regraRegexBusca,
                'situacao' => 'nullable|in:' . implode(',', $this->service->getSituacoes()),
                'datarealizacao' => 'nullable|date',
            ];
        }

        return [
            'uasg' => 'max:191',
            'edital' => 'max:191',
            'modalidade' => 'required|in:' . implode(',', $this->service->getModalidades()),
            'titulo' => 'required|max:191',
            'nrlicitacao' => 'required|' . $this->regraRegexNl,
            'nrprocesso' => 'required|' . $this->regraRegex,
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
            'regex' => $this->msgRegex,
            'date' => 'Deve ser uma data válida'
        ];
    }
}
