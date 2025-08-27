<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class TermoConsentimentoRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('TermoConsentimento');
    }

    public function rules()
    {
        if(\Route::is('representante.beneficios.acao'))
            return [
                'inscricoes' => 'array|nullable|in:' . implode(',', $this->service->beneficios()),
                'inscricoes.*' => 'distinct'
            ];
        if(\Route::is('termo.consentimento.upload'))
            return [
                'file' => 'required|mimes:pdf|max:2048'
            ];
        return [
            'email' => 'required|email|max:191'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute é obrigatório',
            'max' => 'Excedido limite de :max caracteres',
            'email' => 'Email no formato inválido',
            'mimes' => 'Tipo de arquivo não suportado',
            'file.max' => 'Limite de até 2MB o tamanho do arquivo',
            'inscricoes.in' => 'Benefício inexistente',
            'inscricoes.*.distinct' => 'Benefício repetido',
        ];
    }
}
