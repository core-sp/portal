<?php

namespace App\Traits;

trait PreRegistroApoio {

    private $relation_anexos = "anexos";
    private $relation_contabil = "contabil";
    private $relation_pf = "pessoaFisica";
    private $relation_pj = "pessoaJuridica";
    private $relation_pre_registro = "preRegistro";
    private $relation_rt = "pessoaJuridica.responsavelTecnico";
    
    public function getRelacoes()
    {
        return [
            'App\Anexo' => $this->relation_anexos,
            'App\Contabil' => $this->relation_contabil,
            'App\PreRegistroCpf' => $this->relation_pf,
            'App\PreRegistroCnpj' => $this->relation_pj,
            'App\PreRegistro' => $this->relation_pre_registro,
            'App\ResponsavelTecnico' => $this->relation_rt,
        ];
    }

    public function getCodigos($classe)
    {
        $model = array_keys($this->getRelacoes(), $classe, true);

        if(isset($model[0]))
            return $model[0]::camposPreRegistro();

        throw new \Exception('Classe não encontrada no serviço de pré-registro: ' . $classe, 404);
    }

    public function limparNomeCamposAjax($classe, $campo)
    {
        $campos = $this->getCodigos($classe);
        $siglas = [
            $this->relation_anexos => null,
            $this->relation_pre_registro => null,
            $this->relation_pf => null,
            $this->relation_pj => '_empresa',
            $this->relation_contabil => '_contabil',
            $this->relation_rt => '_rt',
        ];

        $siglas = $siglas[$classe];

        foreach($campos as $key => $cp)
        {
            $temp = $cp . $siglas;
            if(($campo == $cp) || ($campo == $temp))
                return $campos[$key];
        }

        return $campo;
    }

    public function formatarCamposRequest($request)
    {
        $request['opcional_celular'] = isset($request['opcional_celular']) ? implode(',', $request['opcional_celular']) : null;
        if(isset($request['opcional_celular_1']))
            $request['opcional_celular_1'] = implode(',', $request['opcional_celular_1']);
        unset($request['pergunta']);
        
        return $request;
    }

    public function getNomeClasses()
    {
        return array_values($this->getRelacoes());
    }

    public function getNomesCampos()
    {
        $classes = $this->getNomeClasses();

        return [
            $classes[0] => 'path',
            $classes[1] => 'nome_contabil,cnpj_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            $classes[4] => 'segmento,idregional,cep,bairro,logradouro,numero,complemento,cidade,uf,tipo_telefone,telefone,opcional_celular,tipo_telefone_1,telefone_1,opcional_celular_1,pergunta',
            $classes[2] => 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade_cidade,naturalidade_estado,nome_mae,nome_pai,tipo_identidade,identidade,orgao_emissor,dt_expedicao,titulo_eleitor,zona,secao,ra_reservista',
            $classes[3] => 'razao_social,nome_fantasia,capital_social,nire,tipo_empresa,dt_inicio_atividade,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            $classes[5] => 'nome_rt,nome_social_rt,sexo_rt,dt_nascimento_rt,cpf_rt,tipo_identidade_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,titulo_eleitor_rt,zona_rt,secao_rt,ra_reservista_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt'
        ];
    }

    public function camposPjOuPf($pf = true)
    {
        $camposView = $this->getNomesCampos();
        return $pf ? [
            $this->relation_pre_registro => explode(',', $camposView[$this->relation_pre_registro]),
            $this->relation_contabil => explode(',', $camposView[$this->relation_contabil]),
            $this->relation_pf => explode(',', $camposView[$this->relation_pf]),
        ] : [
            $this->relation_pre_registro => explode(',', $camposView[$this->relation_pre_registro]),
            $this->relation_contabil => explode(',', $camposView[$this->relation_contabil]),
            $this->relation_pj => explode(',', $camposView[$this->relation_pj]),
            $this->relation_rt => explode(',', $camposView[$this->relation_rt]),
        ];
    }

    public function getCamposLimpos($request, $campos)
    {
        $request = collect($this->formatarCamposRequest($request));
        $request->transform(function ($value, $chave) {
            if(!isset($value))
                return null;
            return in_array($chave, ['checkEndEmpresa', 'email_contabil']) ? $value : mb_strtoupper($value, 'UTF-8');
        });

        return $campos->map(function ($item, $key) use($request){
            return array_intersect_key($request->toArray(), array_fill_keys($item, ''));
        })
        ->map(function ($valores, $classe) {
            return collect($valores)->mapWithKeys(function ($val, $campo) use($classe){
                return [$this->limparNomeCamposAjax($classe, $campo) => $val];
            });
        })
        ->toArray();
    }
}
