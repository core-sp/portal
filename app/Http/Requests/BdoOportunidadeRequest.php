<?php

namespace App\Http\Requests;

use App\BdoOportunidade;
use Illuminate\Foundation\Http\FormRequest;

class BdoOportunidadeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'titulo' => 'required|max:191',
            'segmento' => 'max:191',
            'vagasdisponiveis' => 'required',
            'descricao' => 'required',
            'status' => 'max:191',
            'observacao' => 'max:500',
            'regiaoatuacao' => 'required|array|min:1'
        ];
    }

    public function messages()
    {
        return [
            'titulo.required' => 'O Título é obrigatório',
            'titulo.max' => 'O Título excedeu o limite de caracteres permitido',
            'segmento.max' => 'O Segmento excedeu o limite de caracteres permitido',
            'vagasdisponiveis.required' => 'Informe o número de vagas disponíveis',
            'descricao.required' => 'A Descrição  é obrigatória',
            'status.max' => 'O Status excedeu o limite de caracteres permitido',          
            'observacao.max' => 'A Observação excedeu o limite de caracteres permitido', 
            'regiaoatuacao.required' => 'Por favor, selecione ao menos uma região de atuação' 
        ];
    }

    public function toModel()
    {
        return [
            'idempresa' => $this->idempresa,
            'titulo' => $this->titulo,
            'segmento' => $this->segmento,
            'regiaoatuacao' => ','.implode(',',$this->regiaoatuacao).',',
            'descricao' => $this->descricao,
            'vagasdisponiveis' => $this->vagasdisponiveis,
            'vagaspreenchidas' => $this->vagaspreenchidas,
            'status' => $this->status,
            'observacao' => $this->observacao,
            'datainicio' => (!isset($this->datainicio) && $this->status === BdoOportunidade::STATUS_EM_ANDAMENTO) ? now() : null,
            'idusuario' => $this->idusuario
        ];
    }
}
