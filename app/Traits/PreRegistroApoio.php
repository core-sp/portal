<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Traits\Gerenti;

trait PreRegistroApoio {

    use Gerenti;

    private $relation_anexos = "anexos";
    private $relation_contabil = "contabil";
    private $relation_pf = "pessoaFisica";
    private $relation_pj = "pessoaJuridica";
    private $relation_pre_registro = "preRegistro";
    private $relation_rt = "pessoaJuridica.responsavelTecnico";

    private function criarAjax($relacao, $campo, $valor, $gerenti)
    {
        $classe = array_search($relacao, $this->getRelacoes());

        switch ($relacao) {
            case $this->relation_rt:
                return $classe::criarFinal($campo, $valor, $gerenti, $this);
                break;
            case $this->relation_contabil:
            case $this->relation_anexos:
                return $classe::criarFinal($campo, $valor, $this);
                break;
            default:
                return null;
        }
    }

    private function atualizarAjax($classe, $campo, $valor, $gerenti)
    {
        switch ($classe) {
            case $this->relation_pre_registro:
            case $this->relation_pf:
            case $this->relation_pj:
                $temp = $classe == $this->relation_pre_registro ? $this : $this->loadMissing($classe)[$classe];
                return isset($temp) ? $temp->atualizarFinal($campo, $valor) : null;
                break;
            case $this->relation_contabil:
                $pr = $this;
                return $this->loadMissing($classe)[$classe]->atualizarFinal($campo, $valor, $pr);
                break;
            case $this->relation_rt:
                $pj = $this->loadMissing($classe)->pessoaJuridica;
                return $pj->responsavelTecnico->atualizarFinal($campo, $valor, $gerenti, $pj);
                break;
            default:
                return null;
        }
    }

    private function salvarArray($classe, $arrayCampos, $gerenti)
    {
        $nome_classe = array_search($classe, $this->getRelacoes());
        $obj_existe = ($classe == $this->relation_contabil) || ($classe == $this->relation_rt) ? $this->has($classe)->where('id', $this->id)->exists() : true;

        switch ($classe) {
            case $this->relation_pre_registro:
            case $this->relation_pf:
            case $this->relation_pj:
                $temp = $this->relation_pre_registro == $classe ? $this : $this->loadMissing($classe)[$classe];
                return $temp->finalArray($arrayCampos);
                break;
            case $this->relation_contabil:
                $temp = !$obj_existe ? $nome_classe::buscar($arrayCampos['cnpj']) : $this->loadMissing($classe)[$classe];
                return isset($temp) ? $temp->finalArray($arrayCampos, $this) : false;
                break;
            case $this->relation_rt:
                $pj = $this->loadMissing($classe)->pessoaJuridica;
                $temp = !$obj_existe ? $nome_classe::buscar($arrayCampos['cpf'], $gerenti) : $pj->responsavelTecnico;
                return isset($temp) ? $temp->finalArray($arrayCampos, $pj) : false;
                break;
        }

        throw new \Exception('Classe não está configurada para ser salva no array final', 500);
    }
    
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

    public function formatarCamposRequest($request, $admin = false)
    {
        if($admin)
        {
            $campo = $request['campo'];
            $valor = $request['valor'];
            $classe = 'preRegistro';

            if($request['acao'] != 'editar')
            {
                $campo = $request['acao'] == 'justificar' ? 'justificativa' : 'confere_anexos';
                $valor = $request['acao'] == 'justificar' ? ['campo' => $request['campo'], 'valor' => $request['valor']] : $request['valor'];
            }
            
            switch ($campo) {
                case 'justificativa':
                case 'confere_anexos':
                case 'registro_secundario':
                    $classe = 'preRegistro';
                    break;
                case 'registro':
                    $classe = 'pessoaJuridica.responsavelTecnico';
                    break;
            }
            
            return ['classe' => $classe, 'campo' => $campo, 'valor' => $valor];
        }

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
        $request = $this->formatarCamposRequest($request);

        return $campos->map(function ($item, $key) use($request){
            return array_intersect_key($request, array_fill_keys($item, ''));
        })
        ->map(function ($valores, $classe) {
            return collect($valores)->mapWithKeys(function ($val, $campo) use($classe){
                if(!isset($val))
                    return [$this->limparNomeCamposAjax($classe, $campo) => null];
                return [$this->limparNomeCamposAjax($classe, $campo) => in_array($campo, ['checkEndEmpresa', 'email_contabil']) ? $val : mb_strtoupper($val, 'UTF-8')];
            });
        })
        ->toArray();
    }

    public function getRTGerenti($relacao, $gerentiRepository, $cpf)
    {
        if(($relacao != $this->relation_rt) && (!isset($cpf) || (strlen($cpf) != 11)))
            return null;

        $resultadosGerenti = $gerentiRepository->gerentiBusca("", null, $cpf);
        $ass_id = null;
        $gerenti = array();

        // Para testar: colocar 5 em "ASS_TP_ASSOC" em gerentiBusca() em GerentiRepositoryMock
        foreach($resultadosGerenti as $resultado)
        {
            $naoCancelado = $resultado['CANCELADO'] == "F";
            $ativo = $resultado['ASS_ATIVO'] == "T";
            $tipo = $resultado["ASS_TP_ASSOC"] == $this->getCodigoRT();

            if($naoCancelado && $ativo && $tipo)
            {
                $ass_id = $resultado["ASS_ID"];
                $gerenti['nome'] = $resultado["ASS_NOME"];
                $gerenti['registro'] = apenasNumeros($resultado["ASS_REGISTRO"]);
            }
        }
        
        if(isset($ass_id))
        {
            $resultadosGerenti = utf8_converter($gerentiRepository->gerentiDadosGeraisPF($ass_id));

            $gerenti['nome_mae'] = isset($resultadosGerenti['Nome da mãe']) ? $resultadosGerenti['Nome da mãe'] : null;
            $gerenti['nome_pai'] = isset($resultadosGerenti['Nome do pai']) ? $resultadosGerenti['Nome do pai'] : null;
            $gerenti['identidade'] = isset($resultadosGerenti['identidade']) ? $resultadosGerenti['identidade'] : null;
            $gerenti['orgao_emissor'] = isset($resultadosGerenti['emissor']) ? $resultadosGerenti['emissor'] : null;
            $gerenti['dt_expedicao'] = isset($resultadosGerenti['expedicao']) && Carbon::hasFormat($resultadosGerenti['expedicao'], 'd/m/Y') ? 
                Carbon::createFromFormat('d/m/Y', $resultadosGerenti['expedicao'])->format('Y-m-d') : null;
            $gerenti['dt_nascimento'] = isset($resultadosGerenti['Data de nascimento']) && Carbon::hasFormat($resultadosGerenti['Data de nascimento'], 'd/m/Y') ? 
                Carbon::createFromFormat('d/m/Y', $resultadosGerenti['Data de nascimento'])->format('Y-m-d') : null;
            $gerenti['sexo'] = null;
            if(isset($resultadosGerenti['Sexo']))
                $gerenti['sexo'] = $resultadosGerenti['Sexo'] == "MASCULINO" ? "M" : "F";
            $gerenti['cpf'] = $cpf;
        }

        return $gerenti;
    }
}
