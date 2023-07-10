<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use Carbon\Carbon;
use App\Rules\CpfCnpj;

class SuspensaoExcecaoRequest extends FormRequest
{
    private $service;
    private $dataFinalIn;
    private $dataInicialExcecao;
    private $dataFinalExcecao;
    private $dataFinal;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        if(!isset($this->situacao))
        {
            $this->merge(['cpf_cnpj' => apenasNumeros($this->cpf_cnpj)]);
            if($this->filled('cpf_cnpj'))
            {
                $suspenso = $this->service->getService('SalaReuniao')->suspensaoExcecao()->verificaSuspenso($this->cpf_cnpj);
                if(isset($suspenso))
                    $this->merge(['cpf_cnpj' => null, 'idrepresentante' => null]);
                else
                {
                    $rc = $this->service->getService('Representante')->getRepresentanteByCpfCnpj($this->cpf_cnpj);
                    if(isset($rc))
                        $this->merge(['cpf_cnpj' => null, 'idrepresentante' => $rc->id]);
                }
            }

            if($this->filled('data_inicial') && Carbon::hasFormat($this->data_inicial, 'Y-m-d'))
                $this->dataFinal = !$this->filled('data_final') ? '' : 
                '|after_or_equal:'.Carbon::parse($this->data_inicial)->addDays(30)->format('Y-m-d');
            return;
        }

        if($this->situacao == 'suspensao')
        {
            $this->dataFinalIn = '30,60,90';
            $suspensao = $this->service->getService('SalaReuniao')->suspensaoExcecao()->view(auth()->user(), $this->id)['resultado'];
            if($this->filled('data_final'))
                $this->dataFinalIn = !isset($suspensao->data_final) ? $this->dataFinalIn : '30,60,90,00';
            return;
        }

        if($this->situacao == 'excecao')
        {
            $suspensao = $this->service->getService('SalaReuniao')->suspensaoExcecao()->view(auth()->user(), $this->id)['resultado'];
            $totalDiasExcecao = $suspensao->getTotalDiasExcecao();

            if($this->filled('data_inicial_excecao'))
                $this->dataInicialExcecao = now()->format('Y-m-d') >= $suspensao->data_inicial ? '|after_or_equal:today' : '|after_or_equal:'.$suspensao->data_inicial;
            
            if($this->filled('data_final_excecao') && Carbon::hasFormat($this->data_final_excecao, 'Y-m-d'))
                $this->dataFinalExcecao = isset($suspensao->data_final) ? '|after_or_equal:data_inicial_excecao|before_or_equal:'.$suspensao->data_final : 
                    '|after_or_equal:data_inicial_excecao';

            $dtInicial = $this->filled('data_inicial_excecao') && Carbon::hasFormat($this->data_inicial_excecao, 'Y-m-d');
            $dtFinal = $this->filled('data_final_excecao') && Carbon::hasFormat($this->data_final_excecao, 'Y-m-d');

            if($dtInicial && $dtFinal)
                Carbon::parse($this->data_inicial_excecao)->addDays($totalDiasExcecao)->format('Y-m-d') < Carbon::parse($this->data_final_excecao)->format('Y-m-d') ? 
                $this->merge(['data_final_excecao' => null]) : null;
            return;
        }
    }

    public function rules()
    {
        $justificativa = ['justificativa' => 'required|string|min:10|max:1000'];

        if(!isset($this->situacao))
            return array_merge([
                'idrepresentante' => '',
                'cpf_cnpj' => [
                    'required_if:idrepresentante,',
                    'nullable',
                    new CpfCnpj
                ],
                'data_inicial' => 'required|date_format:Y-m-d|after_or_equal:today',
                'data_final' => 'present|nullable|date_format:Y-m-d'.$this->dataFinal,
            ], $justificativa);

        if($this->situacao == 'suspensao')
            return array_merge([
                'data_final' => 'required|in:'.$this->dataFinalIn,
            ], $justificativa);
        
        if($this->situacao == 'excecao')
            return array_merge([
                'data_inicial_excecao' => 'required_unless:data_final_excecao,|nullable|date_format:Y-m-d'.$this->dataInicialExcecao,
                'data_final_excecao' => 'required_unless:data_inicial_excecao,|nullable|date_format:Y-m-d'.$this->dataFinalExcecao,
            ], $justificativa);
    }

    public function messages()
    {
        return [
            'required' => 'O campo é obrigatório',
            'in' => 'Esse valor é inválido',
            'min' => 'Deve ter pelo menos :min caracteres',
            'max' => 'Deve ter no máximo :max caracteres',
            'data_final_excecao.required' => 'O campo é obrigatório / período não aceito',
            'date_format' => 'Data deve ser no formato válido',
            'data_inicial_excecao.after_or_equal' => 'A data inicial da exceção deve ser maior ou igual a data inicial da suspensão e, no mínimo, igual a hoje',
            'data_final_excecao.after_or_equal' => 'A data final da exceção deve ser maior ou igual a data inicial da exceção',
            'data_final_excecao.before_or_equal' => 'A data final da exceção deve ser menor ou igual a data final da suspensão',
            'required_unless' => 'O campo obrigatório caso data inicial ou final da exceção esteja preenchido / período ultrapassou o limite de 15 dias incluindo a data inicial da exceção',
            'data_inicial.after_or_equal' => 'A data inicial deve ser maior ou igual hoje',
            'data_final.after_or_equal' => 'A data final deve ser maior ou igual a data inicial + 30 dias, ou vazia para Tempo Indeterminado',
            'cpf_cnpj.required_if' => 'Campo obrigatório ou CPF / CNPJ já possui suspensão válida',
        ];
    }
}
