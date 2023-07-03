<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use Carbon\Carbon;

class SuspensaoExcecaoRequest extends FormRequest
{
    private $service;
    private $dataFinalIn;
    private $dataInicialExcecao;
    private $dataFinalExcecao;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('SalaReuniao');
    }

    protected function prepareForValidation()
    {
        if($this->situacao == 'suspensao')
        {
            $this->dataFinalIn = '30,60,90';
            $suspensao = $this->service->suspensaoExcecao()->view(auth()->user(), $this->id)['resultado'];
            if($this->filled('data_final'))
                $this->dataFinalIn = !isset($suspensao->data_final) ? $this->dataFinalIn : '30,60,90,00';
            return;
        }

        if($this->situacao == 'excecao')
        {
            $suspensao = $this->service->suspensaoExcecao()->view(auth()->user(), $this->id)['resultado'];
            if($this->filled('data_inicial_excecao'))
                $this->dataInicialExcecao = '|after_or_equal:'.$suspensao->data_inicial;
            if($this->filled('data_final_excecao') && Carbon::hasFormat($this->data_final_excecao, 'Y-m-d'))
                $this->dataFinalExcecao = '|after_or_equal:data_inicial_excecao|before_or_equal:'.$suspensao->data_final;

            if($this->filled('data_inicial_excecao') && $this->filled('data_final_excecao') && Carbon::hasFormat($this->data_inicial_excecao, 'Y-m-d') && Carbon::hasFormat($this->data_final_excecao, 'Y-m-d'))
                Carbon::parse($this->data_inicial_excecao)->addDays(15)->format('Y-m-d') < Carbon::parse($this->data_final_excecao)->format('Y-m-d') ? 
                $this->merge(['data_final_excecao' => null]) : null;
            return;
        }
    }

    public function rules()
    {
        if($this->situacao == 'suspensao')
            return [
                'data_final' => 'required|in:'.$this->dataFinalIn,
                'justificativa' => 'required|min:10|max:1000',
            ];
        
        if($this->situacao == 'excecao')
            return [
                'data_inicial_excecao' => 'required|date'.$this->dataInicialExcecao,
                'data_final_excecao' => 'required|date'.$this->dataFinalExcecao,
                'justificativa' => 'required|min:10|max:1000',
            ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo é obrigatório',
            'in' => 'Esse valor é inválido',
            'min' => 'Deve ter pelo menos :min caracteres',
            'max' => 'Deve ter no máximo :max caracteres',
            'data_final_excecao.required' => 'O campo é obrigatório / período não aceito',
            'date' => 'Data deve ser no formato válido',
        ];
    }
}
