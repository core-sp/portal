<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use App\Rules\Cnpj;
use Illuminate\Foundation\Http\FormRequest;

class PreCadastroRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|max:191',
            'cpf' => ['required', new Cpf],
            'tipoDocumento' => 'required|max:191',
            'numeroDocumento' => 'required|max:191',
            'orgaoEmissor' => 'required|max:191',
            'dataExpedicao' => 'required|date_format:d/m/Y',
            'dataNascimento' => 'required|date_format:d/m/Y',
            'estadoCivil' => 'required|max:191',
            'sexo' => 'required|max:191',
            'naturalizado' => 'required|max:191',
            'nacionalidade' => 'required|max:191',
            'nomeMae' => 'required|max:191',
            'nomePai' => 'required|max:191',
            'email' => 'required|email|max:191',
            'celular' => 'max:191|min:14',
            'telefoneFixo' => 'max:191',
            'segmento' => 'required|max:191',
            'cep' => 'required',
            'bairro' => 'required|max:30',
            'logradouro' => 'required|max:100',
            'numero' => 'required|max:15',
            'complemento' => 'max:100',
            'estado' => 'required|max:5',
            'municipio' => 'required|max:30',
            // 'cnpj' => [new Cnpj],
            'anexoCpf' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'anexoDocumento' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'anexoComprovanteResidencia' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'anexoCertidaoQuitacaoEleitoral' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'anexoReservistaMilitar' => 'mimes:jpeg,png,jpg,gif,pdf|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Campo obrigatório',
            'max' => 'Excedido limite de caracteres',
            'mimes' => 'Tipo de arquivo não suportado',
            'max' => 'Arquivo não pode ultrapassar 2MB',
            'date_format' => 'Data inválida',
        ];
    }
}
