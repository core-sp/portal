<?php

namespace App\Http\Requests;

use App\PeriodoFiscalizacao;
use Illuminate\Foundation\Http\FormRequest;

class PeriodoFiscalizacaoRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Remove os nomes dos campos repetidos em cada regional
        if(isset(request()->dados) && (gettype(request()->dados) == 'array'))
        {
            $all = $this->all();
            foreach($all['dados'] as $key => $value)
                if(isset($all['dados'][$key]['campo']) && (gettype($all['dados'][$key]['campo']) == 'array'))
                    $all['dados'][$key]['campo'] = array_unique($value['campo']);
            $this->replace($all);
        }
    }

    public function rules()
    {
        $campos = 'processofiscalizacaopf,processofiscalizacaopj,registroconvertidopf,registroconvertidopj,processoverificacao,';
        $campos .= 'dispensaregistro,notificacaort,orientacaorepresentada,orientacaorepresentante,cooperacaoinstitucional,autoconstatacao,';
        $campos .= 'autosdeinfracao,multaadministrativa';

        return [
            'periodo' => 'required_without:dados|date_format:Y|size:4|after_or_equal:2000|unique:periodos_fiscalizacao,periodo',
            'dados' => 'required_without:periodo|array|size:13',
            'dados.*.id' => 'required_without:periodo|exists:dados_fiscalizacao,id|distinct',
            'dados.*.campo' => 'required_without:periodo|array|size:13',
            'dados.*.campo.*' => 'required_without:periodo|in:' . $campos,
            'dados.*.valor' => 'required_without:periodo|array|size:13',
            'dados.*.valor.*' => 'required_without:periodo|integer|min:0|max:999999999',
        ];
    }

    public function messages() 
    {
        return [
            'periodo.required_without' => 'Período obrigatório',
            'dados.required_without' => 'Dados obrigatórios',
            'dados.*.id.required_without' => 'Obrigatória a id dos dados',
            'dados.*.campo.required_without' => 'Obrigatório o campo',
            'dados.*.campo.*.required_without' => 'Obrigatório o nome do campo',
            'dados.*.valor.required_without' => 'Obrigatório o valor',
            'dados.*.valor.*.required_without' => 'Obrigatório inserir o valor',
            'date_format' => 'Ano inválido',
            'periodo.size' => 'O ano deve conter 4 dígitos',
            'after_or_equal' => 'O ano deve ser maior ou igual a 2000',
            'unique' => 'Ano já está cadastrado',
            'min' => 'Valor deve ser maior ou igual a 0',
            'max' => 'Valor deve ser menor ou igual a 999999999',
            'integer' => 'Valor deve ser um inteiro',
            'array' => 'Campo não está formato de array na requisição',
            'exists' => 'ID inexistente',
            'in' => 'Nome do campo inválido',
            'dados.size' => 'Quantidade errada de regionais',
            'dados.*.campo.size' => 'Quantidade errada de campos',
            'dados.*.valor.size' => 'Quantidade errada de valores',
            'distinct' => 'Existe id repetida'
        ];
    }

    // public function toModel()
    // {
    //     return [
    //         'periodo' => $this->periodo,
    //         'status' => false
    //     ];
    // }
}
