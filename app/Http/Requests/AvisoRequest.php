<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class AvisoRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Aviso');
    }

    public function rules()
    {
        return [
            'cor_fundo_titulo' => 'required|in:' . implode(',', $this->service->cores()),
            'titulo' => 'required|max:191',
            'conteudo' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'in' => 'Cor escolhida não disponível'
        ];
    }
}
