<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreRegistroAjaxRequest extends FormRequest
{
    public function rules()
    {
        $classes = null;
        $todos = null;
        $campos_array = [
            'anexos' => 'anexos',
            'contabil' => 'nome_contabil,cnpj_contabil,email_contabil,contato_contabil,telefone_contabil',
            'preRegistro' => 'registro_secundario,ramo_atividade,segmento,idregional,cep,bairro,logradouro,numero,complemento,cidade,uf,tipo_telefone.1,telefone.1,tipo_telefone.2,telefone.2',
            'pessoaFisica' => 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade,nome_mae,nome_pai,identidade,orgao_emissor,dt_expedicao',
            'pessoaJuridica' => 'razao_social,capital_social,nire,tipo_empresa,dt_inicio_atividade,inscricao_estadual,inscricao_municipal,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            'responsavelTecnico' => 'nome_rt,nome_social_rt,registro,sexo_rt,dt_nascimento_rt,cpf_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt'
        ];

        foreach($campos_array as $key => $campos)
        {
            $classes .= isset($classes) ? ','.$key : $key;
            $todos .= isset($todos) ? ','.$campos : $campos;
        }

        return [
            'valor' => 'max:191',
            'campo' => 'required|in:'.$todos,
            'classe' => 'required|in:'.$classes
        ];
    }

    public function messages()
    {
        return [];
    }
}
