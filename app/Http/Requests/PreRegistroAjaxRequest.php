<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\CpfCnpj;

class PreRegistroAjaxRequest extends FormRequest
{
    private $regraValor;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        $this->regraValor = ['max:191'];

        if((request()->campo == 'tipo_telefone_1') || (request()->campo == 'telefone_1'))
            $this->merge([
                'campo' => request()->campo == 'tipo_telefone_1' ? 'tipo_telefone' : 'telefone',
                'valor' => ';'.request()->valor
            ]);

        if((strpos(request()->campo, 'cpf') !== false) || (strpos(request()->campo, 'cnpj') !== false))
        {
            if(isset(request()->valor))
            {
                $this->regraValor[1] = new CpfCnpj;
                $this->merge([
                    'valor' => apenasNumeros(request()->valor)
                ]);
            }
        }

        if(request()->campo == 'path')
        {
            $this->regraValor[0] = 'file';
            $this->regraValor[1] = 'mimetypes:application/pdf,image/jpeg,image/png';
            $this->regraValor[2] = 'max:5120';
        }
    }

    public function rules()
    {
        $service = $this->service->getService('PreRegistro')->getNomeClasses();
        $classes = null;
        $todos = null;
        $campos_array = [
            $service[0] => 'path',
            $service[1] => 'nome_contabil,cnpj_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            $service[4] => 'registro_secundario,ramo_atividade,segmento,idregional,cep,bairro,logradouro,numero,complemento,cidade,uf,tipo_telefone,telefone,tipo_telefone_1,telefone_1',
            $service[2] => 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade,nome_mae,nome_pai,identidade,orgao_emissor,dt_expedicao',
            $service[3] => 'razao_social,capital_social,nire,tipo_empresa,dt_inicio_atividade,inscricao_estadual,inscricao_municipal,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            $service[5] => 'nome_rt,nome_social_rt,registro,sexo_rt,dt_nascimento_rt,cpf_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt'
        ];

        foreach($campos_array as $key => $campos)
        {
            $classes .= isset($classes) ? ','.$key : $key;
            $todos .= isset($todos) ? ','.$campos : $campos;
        }

        return [
            'valor' => $this->regraValor,
            'campo' => 'required|in:'.$todos,
            'classe' => 'required|in:'.$classes
        ];
    }

    public function messages()
    {
        return [
            'max' => request()->campo != 'path' ? 'Limite de :max caracteres' : 'Limite do tamanho do arquivo é de 5 MB',
            'in' => 'Campo não encontrado ou não permitido alterar',
            'required' => 'Falta dados para enviar a requisição',
            'mimetypes' => 'O arquivo não possue extensão permitida ou está com erro',
            'file' => 'Deve ser um arquivo',
        ];
    }
}
