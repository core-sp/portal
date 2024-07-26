<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Traits\Gerenti;
use Illuminate\Support\Arr;

trait PreRegistroApoio {

    use Gerenti;

    private $relation_anexos = "anexos";
    private $relation_contabil = "contabil";
    private $relation_pf = "pessoaFisica";
    private $relation_pj = "pessoaJuridica";
    private $relation_pre_registro = "preRegistro";
    private $relation_rt = "pessoaJuridica.responsavelTecnico";
    private $relation_socio = "pessoaJuridica.socios";

    private function criarAjax($relacao, $campo, $valor, $gerenti)
    {
        if(get_class($this) != 'App\PreRegistro')
            throw new \Exception('Este método somente a classe App\PreRegistro deve usar', 500);

        $classe = array_search($relacao, $this->getClassesRelacoes());

        switch ($relacao) {
            case $this->relation_rt:
                return $classe::criarFinal($campo, $valor, $gerenti, $this);
                break;
            case $this->relation_socio:
                return $classe::criarFinal($campo[1], $valor, $gerenti, $this);
                break;
            case $this->relation_contabil:
            case $this->relation_anexos:
                return $classe::criarFinal($campo, $valor, $this);
                break;
            default:
                return null;
        }
    }

    private function atualizarAjax($classe, $campo, $valor)
    {
        if(get_class($this) != 'App\PreRegistro')
            throw new \Exception('Este método somente a classe App\PreRegistro deve usar', 500);

        switch ($classe) {
            case $this->relation_pre_registro:
                return $this->atualizarFinal($campo, $valor);
                break;
            case $this->relation_pf:
            case $this->relation_pj:
                return $this->loadMissing($classe)[$classe]->atualizarFinal($campo, $valor);
                break;
            case $this->relation_contabil:
                return $this->loadMissing($classe)[$classe]->atualizarFinal($campo, $valor, $this);
                break;
            case $this->relation_rt:
                $pj = $this->loadMissing($classe)->pessoaJuridica;
                return $pj->responsavelTecnico->atualizarFinal($campo, $valor, $pj);
                break;
            case $this->relation_socio:
                $pj = $this->loadMissing($classe)->pessoaJuridica;
                $socio = $pj->socios->where('id', $campo[0])->first();
                if(!isset($socio))
                    throw new \Exception('Não há sócio com a ID '. $campo[0] . ' relacionado com o pré-registro de ID ' . $this->id . '.', 404);
                return $socio->atualizarFinal($campo[1], $valor, $pj);
                break;
            default:
                return null;
        }
    }

    // private function salvarArray($classe, $arrayCampos, $gerenti)
    // {
    //     $nome_classe = array_search($classe, $this->getClassesRelacoes());
    //     $obj_existe = ($classe == $this->relation_contabil) || ($classe == $this->relation_rt) ? $this->has($classe)->where('id', $this->id)->exists() : true;

    //     switch ($classe) {
    //         case $this->relation_pre_registro:
    //         case $this->relation_pf:
    //         case $this->relation_pj:
    //             $temp = $this->relation_pre_registro == $classe ? $this : $this->loadMissing($classe)[$classe];
    //             return $temp->finalArray($arrayCampos);
    //             break;
    //         case $this->relation_contabil:
    //             $temp = !$obj_existe ? $nome_classe::buscar($arrayCampos['cnpj']) : $this->loadMissing($classe)[$classe];
    //             return isset($temp) ? $temp->finalArray($arrayCampos, $this) : false;
    //             break;
    //         case $this->relation_rt:
    //             $pj = $this->loadMissing($classe)->pessoaJuridica;
    //             $temp = !$obj_existe ? $nome_classe::buscar($arrayCampos['cpf'], $gerenti) : $pj->responsavelTecnico;
    //             return isset($temp) ? $temp->finalArray($arrayCampos, $pj) : false;
    //             break;
    //         case $this->relation_socio:
    //             return true;
    //             break;
    //     }

    //     throw new \Exception('Classe não está configurada para ser salva no array final', 500);
    // }
    
    private function limparCampoTelefones($campo)
    {
        return preg_replace('/_[0-9]{1,2}$/', '', $campo);
    }
    
    private function formataTelefonesComCampo($limite, $array, $nomeCampo, $retorno = null)
    {
        for($cont = 0; $cont < $limite; ++$cont)
        {
            $chave = $cont > 0 ? $nomeCampo . '_' . $cont : $nomeCampo;
            $temp[$chave] = isset($array[$cont]) ? $array[$cont] : $retorno;
        }

        return $temp;
    }
    
    private function getClassesRelacoes()
    {
        return [
            'App\Anexo' => $this->relation_anexos,
            'App\Contabil' => $this->relation_contabil,
            'App\PreRegistroCpf' => $this->relation_pf,
            'App\PreRegistroCnpj' => $this->relation_pj,
            'App\PreRegistro' => $this->relation_pre_registro,
            'App\ResponsavelTecnico' => $this->relation_rt,
            'App\Socio' => $this->relation_socio,
        ];
    }

    private function getAbasCampos($pessoaFisica = true)
    {
        $pf = 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade_cidade,naturalidade_estado,nome_mae,nome_pai,tipo_identidade,identidade,orgao_emissor,dt_expedicao,titulo_eleitor,zona,secao,ra_reservista';
        $pj = 'razao_social,nome_fantasia,capital_social,nire,tipo_empresa,dt_inicio_atividade';
        $dadosGerais = $pessoaFisica ? $pf : $pj;

        // A índice é referente a índice do menu
        // Colocar na ordem dos campos nas blades
        return [
            'cnpj_contabil,nome_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            $dadosGerais . ',segmento,idregional,pergunta',
            'cep,bairro,logradouro,numero,complemento,cidade,uf,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            'cpf_rt,registro,nome_rt,nome_social_rt,dt_nascimento_rt,sexo_rt,tipo_identidade_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,titulo_eleitor_rt,zona_rt,secao_rt,ra_reservista_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt',
            'checkRT_socio,cpf_cnpj_socio,registro_socio,nome_socio,nome_social_socio,dt_nascimento_socio,identidade_socio,orgao_emissor_socio,cep_socio,bairro_socio,logradouro_socio,numero_socio,complemento_socio,cidade_socio,uf_socio,nome_mae_socio,nome_pai_socio,nacionalidade_socio,naturalidade_estado_socio',
            'tipo_telefone,telefone,opcional_celular,tipo_telefone_1,telefone_1,opcional_celular_1',
            'path',
        ];
    }

    public function getMenu()
    {
        return explode(',', 'Contabilidade,Dados Gerais,Endereço,Contato / RT,Sócios,Canal de Relacionamento,Anexos');
    }

    private function getCampos($relacao)
    {
        $model = array_keys($this->getClassesRelacoes(), $relacao, true);

        if(isset($model[0]))
            return $model[0]::camposPreRegistro();

        throw new \Exception('Classe não encontrada no serviço de pré-registro: ' . $relacao, 404);
    }

    private function limparNomeCamposAjax($relacao, $campo)
    {
        $campos = $this->getCampos($relacao);
        $siglas = null;
        switch ($relacao) {
            case $this->relation_pj:
                $siglas = '_empresa';
                break;
            case $this->relation_contabil:
                $siglas = '_contabil';
                break;
            case $this->relation_rt:
                $siglas = '_rt';
                break;
            case $this->relation_socio:
                $siglas = '_socio';
                break;
            default:
                $siglas = null;
        }

        foreach($campos as $key => $cp)
        {
            $temp = $cp . $siglas;
            if(is_array($campo) && (($campo[1] == $cp) || ($campo[1] == $temp)))
                return [
                    $campo[0], $campos[$key]
                ];

            if(($campo == $cp) || ($campo == $temp))
                return $campos[$key];
        }

        return $campo;
    }

    private function formatarCamposRequest($request, $admin = false)
    {
        if($admin)
        {
            $campo = $request['campo'];
            $valor = $request['valor'];
            $classe = 'preRegistro';

            if($request['acao'] != 'editar')
            {
                $campo = ($request['acao'] == 'justificar') || ($request['acao'] == 'exclusao_massa') ? 'justificativa' : 'confere_anexos';
                $valor = ($request['acao'] == 'justificar') || ($request['acao'] == 'exclusao_massa') ? ['campo' => $request['campo'], 'valor' => $request['valor']] : $request['valor'];
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

        return Arr::except(array_merge($request, array_map(function($valor){
            return count($valor) == 0 ? null : implode(',', $valor);
        }, array_filter($request, function($v, $k) {
            return preg_match('/(^opcional_celular$)|(^opcional_celular_[0-9]{1,2}$)/', $k);
        }, ARRAY_FILTER_USE_BOTH))), ['pergunta']);
    }

    public function getNomesRelacoes()
    {
        return array_values($this->getClassesRelacoes());
    }

    public function getNomesCampos()
    {
        return [
            $this->relation_anexos => 'path',
            $this->relation_contabil => 'nome_contabil,cnpj_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            $this->relation_pre_registro => 'segmento,idregional,cep,bairro,logradouro,numero,complemento,cidade,uf,tipo_telefone,telefone,opcional_celular,tipo_telefone_1,telefone_1,opcional_celular_1,pergunta',
            $this->relation_pf => 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade_cidade,naturalidade_estado,nome_mae,nome_pai,tipo_identidade,identidade,orgao_emissor,dt_expedicao,titulo_eleitor,zona,secao,ra_reservista',
            $this->relation_pj => 'razao_social,nome_fantasia,capital_social,nire,tipo_empresa,dt_inicio_atividade,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            $this->relation_rt => 'nome_rt,nome_social_rt,sexo_rt,dt_nascimento_rt,cpf_rt,tipo_identidade_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,titulo_eleitor_rt,zona_rt,secao_rt,ra_reservista_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt',
            $this->relation_socio => 'checkRT_socio,cpf_cnpj_socio,nome_socio,nome_social_socio,dt_nascimento_socio,identidade_socio,orgao_emissor_socio,cep_socio,bairro_socio,logradouro_socio,numero_socio,complemento_socio,cidade_socio,uf_socio,nome_mae_socio,nome_pai_socio,nacionalidade_socio,naturalidade_estado_socio',
        ];
    }

    // public function camposPjOuPf($pf = true)
    // {
    //     $camposView = $this->getNomesCampos();
    //     return $pf ? [
    //         $this->relation_pre_registro => explode(',', $camposView[$this->relation_pre_registro]),
    //         $this->relation_contabil => explode(',', $camposView[$this->relation_contabil]),
    //         $this->relation_pf => explode(',', $camposView[$this->relation_pf]),
    //     ] : [
    //         $this->relation_pre_registro => explode(',', $camposView[$this->relation_pre_registro]),
    //         $this->relation_contabil => explode(',', $camposView[$this->relation_contabil]),
    //         $this->relation_pj => explode(',', $camposView[$this->relation_pj]),
    //         $this->relation_rt => explode(',', $camposView[$this->relation_rt]),
    //         $this->relation_socio => explode(',', $camposView[$this->relation_socio]),
    //     ];
    // }

    // Fazer os códigos automaticos
    public function getCodigosCampos($pessoaFisica = true)
    {
        return collect($this->getAbasCampos($pessoaFisica))->transform(function ($item, $key) {
            $chave = (string) ($key + 1) . '.';
            return collect(array_flip(array_filter(explode(',', $item))))->transform(function ($item_1, $key_1) use($chave){
                return $chave . ++$item_1;
            })
            ->toArray();
        })
        ->toArray();
    }

    // public function getCamposLimpos($request, $campos)
    // {
    //     $request = $this->formatarCamposRequest($request);

    //     return $campos->map(function ($item, $key) use($request){
    //         return array_intersect_key($request, array_fill_keys($item, ''));
    //     })
    //     ->map(function ($valores, $classe) {
    //         return collect($valores)->mapWithKeys(function ($val, $campo) use($classe){
    //             if(!isset($val))
    //                 return [$this->limparNomeCamposAjax($classe, $campo) => null];
    //             return [$this->limparNomeCamposAjax($classe, $campo) => in_array($campo, ['checkEndEmpresa', 'email_contabil', 'checkRT_socio']) ? $val : mb_strtoupper($val, 'UTF-8')];
    //         });
    //     })
    //     ->toArray();
    // }

    private function getRTGerenti($ass_id, $gerentiRepository, $cpf_cnpj, &$gerenti)
    {
        $resultadosGerenti = utf8_converter($gerentiRepository->gerentiDadosGeraisPF($ass_id));

        $gerenti['nome_mae'] = isset($resultadosGerenti['Nome da mãe']) ? mb_strtoupper($resultadosGerenti['Nome da mãe'], 'UTF-8') : null;
        $gerenti['nome_pai'] = isset($resultadosGerenti['Nome do pai']) ? mb_strtoupper($resultadosGerenti['Nome do pai'], 'UTF-8') : null;
        $gerenti['identidade'] = isset($resultadosGerenti['identidade']) ? mb_strtoupper(apenasNumerosLetras($resultadosGerenti['identidade']), 'UTF-8') : null;
        $gerenti['orgao_emissor'] = isset($resultadosGerenti['emissor']) ? mb_strtoupper($resultadosGerenti['emissor'], 'UTF-8') : null;
        $gerenti['dt_expedicao'] = isset($resultadosGerenti['expedicao']) && Carbon::hasFormat($resultadosGerenti['expedicao'], 'd/m/Y') ? 
            Carbon::createFromFormat('d/m/Y', $resultadosGerenti['expedicao'])->format('Y-m-d') : null;
        $gerenti['dt_nascimento'] = isset($resultadosGerenti['Data de nascimento']) && Carbon::hasFormat($resultadosGerenti['Data de nascimento'], 'd/m/Y') ? 
            Carbon::createFromFormat('d/m/Y', $resultadosGerenti['Data de nascimento'])->format('Y-m-d') : null;
        $gerenti['sexo'] = null;
        if(isset($resultadosGerenti['Sexo']))
            $gerenti['sexo'] = $resultadosGerenti['Sexo'] == "MASCULINO" ? "M" : "F";
        $gerenti['cpf'] = $cpf_cnpj;
    }

    private function getSocioGerenti($ass_id, $gerentiRepository, $cpf_cnpj, &$gerenti, $tipo)
    {
        $gerenti['cpf_cnpj'] = $cpf_cnpj;

        if($tipo == 'PJ')
            return;

        $resultadosGerenti = utf8_converter($gerentiRepository->gerentiDadosGeraisPF($ass_id));

        $gerenti['nome_mae'] = isset($resultadosGerenti['Nome da mãe']) ? mb_strtoupper($resultadosGerenti['Nome da mãe'], 'UTF-8') : null;
        $gerenti['nome_pai'] = isset($resultadosGerenti['Nome do pai']) ? mb_strtoupper($resultadosGerenti['Nome do pai'], 'UTF-8') : null;
        $gerenti['identidade'] = isset($resultadosGerenti['identidade']) ? mb_strtoupper(apenasNumerosLetras($resultadosGerenti['identidade']), 'UTF-8') : null;
        $gerenti['orgao_emissor'] = isset($resultadosGerenti['emissor']) ? mb_strtoupper($resultadosGerenti['emissor'], 'UTF-8') : null;
        $gerenti['dt_nascimento'] = isset($resultadosGerenti['Data de nascimento']) && Carbon::hasFormat($resultadosGerenti['Data de nascimento'], 'd/m/Y') ? 
            Carbon::createFromFormat('d/m/Y', $resultadosGerenti['Data de nascimento'])->format('Y-m-d') : null;
    }

    private function getRegistradoGerenti($relacao, $gerentiRepository, $cpf_cnpj)
    {
        if(!isset($gerentiRepository) || !isset(class_implements($gerentiRepository)["App\Repositories\GerentiRepositoryInterface"]))
            return null;
        if(!isset($cpf_cnpj))
            return null;
        if(($relacao != $this->relation_socio) && ($relacao != $this->relation_rt))
            return null;
        if(
            (($relacao == $this->relation_socio) && !((strlen($cpf_cnpj) == 11) || (strlen($cpf_cnpj) == 14))) || 
            (($relacao == $this->relation_rt) && (strlen($cpf_cnpj) != 11))
        )
            return null;

        $resultadosGerenti = $gerentiRepository->gerentiBusca("", null, $cpf_cnpj);
        $ass_id = null;
        $tipo = null;
        $gerenti = array();

        // Para testar: em caso de relation_rt, colocar 5 em "ASS_TP_ASSOC" em gerentiBusca() em GerentiRepositoryMock
        foreach($resultadosGerenti as $resultado)
        {
            $naoCancelado = $resultado['CANCELADO'] == "F";
            $ativo = $resultado['ASS_ATIVO'] == "T";
            $tipo = $this->getTipoPessoaByCodigo($resultado["ASS_TP_ASSOC"]);

            if($naoCancelado && $ativo && ((($relacao == $this->relation_rt) && ($tipo == 'RT')) || (($relacao == $this->relation_socio) && ($tipo != 'Indefinida'))))
            {
                if(
                    (($tipo != 'PJ') && (strlen($cpf_cnpj) == 11)) || 
                    (($relacao == $this->relation_socio) && ($tipo == 'PJ') && (strlen($cpf_cnpj) == 14))
                ){
                    $ass_id = $resultado["ASS_ID"];
                    $gerenti['nome'] = mb_strtoupper($resultado["ASS_NOME"], 'UTF-8');
                    $gerenti['registro'] = apenasNumeros($resultado["ASS_REGISTRO"]);
                    break;
                }
            }
        }

        if(isset($ass_id))
            $relacao == $this->relation_rt ? $this->getRTGerenti($ass_id, $gerentiRepository, $cpf_cnpj, $gerenti) : 
            $this->getSocioGerenti($ass_id, $gerentiRepository, $cpf_cnpj, $gerenti, $tipo);

        return $gerenti;
    }

    public function verificaSeCriaOuAtualiza($request, $gerentiRepository, $objetoExiste = false)
    {
        $request['campo'] = $this->limparNomeCamposAjax($request['classe'], $request['campo']);

        $cpf_cnpj = $this->getCpfCnpjRequest($request['campo'], $request['valor']);

        $resp = $this->podeCriarRequest($request['classe'], $request['campo'], $objetoExiste) ? 'criar' : 'atualizar';

        $gerenti = $resp == 'criar' ? $this->getRegistradoGerenti($request['classe'], $gerentiRepository, $cpf_cnpj) : null;

        return [
            'resp' => $resp,
            'gerenti' => $gerenti,
            'classe' => $request['classe'],
            'campo' => $request['campo'],
            'valor' => $request['valor'],
        ];
    }

    private function getCpfCnpjRequest($campo, $valor)
    {
        return (is_array($campo) && ($campo[1] == 'cpf_cnpj')) || (!is_array($campo) && ($campo == 'cpf')) ? $valor : '';
    }

    private function podeCriarRequest($classe, $campo, $objetoExiste)
    {
        if((($classe == $this->relation_socio) && is_array($campo)) || (($classe != $this->relation_anexos) && ($classe != $this->relation_pre_registro)))
            return (is_array($campo) && ($campo[0] == 0)) || !$objetoExiste;

        if($classe == $this->relation_pre_registro)
            return false;

        return true;
    }
}
