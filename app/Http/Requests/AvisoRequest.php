<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use Carbon\Carbon;

class AvisoRequest extends FormRequest
{
    private $service;
    private $dia_hora;
    private $dia_hora_des;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Aviso');
    }

    protected function prepareForValidation()
    {
        $aviso = $this->service->getById($this->id);
        if(isset($aviso) && $aviso->isComponenteSimples())
        {
            // A tag <p> difere na página aberta comparada a fechada
            $temp = str_replace('<p>', '<div>', $this->conteudo);
            $temp = str_replace('</p>', '</div>', $temp);
            $this->merge([
                'titulo' => '------------',
                'conteudo' => $temp
            ]);
        }

        $this->dia_hora = now()->addMinutes(30)->format('Y-m-d H:i');
        
        if($this->filled('dia_hora_ativar'))
        {
            $this->merge(['dia_hora_ativar' => str_replace('T', ' ', $this->dia_hora_ativar)]);
            $this->dia_hora_des = Carbon::hasFormat($this->dia_hora_ativar, 'Y-m-d H:i') ? Carbon::parse($this->dia_hora_ativar)->addHour()->format('Y-m-d H:i') : $this->dia_hora;
        }

        if($this->filled('dia_hora_desativar'))
            $this->merge(['dia_hora_desativar' => str_replace('T', ' ', $this->dia_hora_desativar)]);
    }

    public function rules()
    {
        return [
            'cor_fundo_titulo' => 'required|in:' . implode(',', $this->service->cores()),
            'titulo' => 'required|max:191',
            'conteudo' => 'required',
            'dia_hora_ativar' => 'nullable|date_format:Y-m-d H:i|after_or_equal:' . $this->dia_hora,
            'dia_hora_desativar' => 'nullable|date_format:Y-m-d H:i|after_or_equal:' . $this->dia_hora_des,
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'in' => 'Cor escolhida não disponível',
            'date_format' => 'Formato inválido',
            'dia_hora_ativar.after_or_equal' => 'Deve ser dia e horário igual ou depois de ' . formataData($this->dia_hora),
            'dia_hora_desativar.after_or_equal' => 'Deve ser dia e horário igual ou depois de ' . formataData($this->dia_hora_des),
        ];
    }
}
