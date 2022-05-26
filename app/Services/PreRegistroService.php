<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Facades\Storage;
use App\Repositories\GerentiRepositoryInterface;

class PreRegistroService implements PreRegistroServiceInterface {

    const RELATION_ANEXOS = "anexos";
    const RELATION_CONTABIL = "contabil";
    const RELATION_PF = "pessoaFisica";
    const RELATION_PJ = "pessoaJuridica";
    const RELATION_PRE_REGISTRO = "preRegistro";
    const RELATION_RT = "pessoaJuridica.responsavelTecnico";
    const RELATION_USER_EXTERNO = 'userExterno';

    private $totalFiles;

    public function __construct()
    {
        $this->totalFiles = 'App\Anexo'::TOTAL_PRE_REGISTRO;
    }

    private function getRelacoes()
    {
        return [
            'App\Anexo' => PreRegistroService::RELATION_ANEXOS,
            'App\Contabil' => PreRegistroService::RELATION_CONTABIL,
            'App\PreRegistroCpf' => PreRegistroService::RELATION_PF,
            'App\PreRegistroCnpj' => PreRegistroService::RELATION_PJ,
            'App\PreRegistro' => PreRegistroService::RELATION_PRE_REGISTRO,
            'App\ResponsavelTecnico' => PreRegistroService::RELATION_RT,
            'App\UserExterno' => PreRegistroService::RELATION_USER_EXTERNO,
        ];
    }

    private function getCodigos()
    {
        $codigos = array();
        $relacoes = $this->getRelacoes();

        foreach($relacoes as $key => $model)
            $codigos[$model] = $key::codigosPreRegistro();
        
        return $codigos;
    }

    private function limparNomeCamposAjax($classe, $campo)
    {
        $chave = false;
        $campos = $this->getCodigos()[$classe];
        $siglas = [
            PreRegistroService::RELATION_ANEXOS => null,
            PreRegistroService::RELATION_PRE_REGISTRO => null,
            PreRegistroService::RELATION_PF => null,
            PreRegistroService::RELATION_PJ => '_empresa',
            PreRegistroService::RELATION_CONTABIL => '_contabil',
            PreRegistroService::RELATION_RT => '_rt',
            PreRegistroService::RELATION_USER_EXTERNO => null,
        ];

        foreach($campos as $key => $cp)
        {
            $temp = $cp . $siglas[$classe];
            if(($campo == $cp) || ($campo == $temp))
            {
                $chave = $key;
                break;
            }
        }

        return isset($campos[$chave]) ? $campos[$chave] : $campo;
    }

    private function limparNomeCampos($externo, $request)
    {
        $camposLimpos = array();
        $classe = null;
        $camposView = $this->getNomesCampos();
        $array = $externo->isPessoaFisica() ? [
            PreRegistroService::RELATION_PRE_REGISTRO => explode(',', $camposView[PreRegistroService::RELATION_PRE_REGISTRO]),
            PreRegistroService::RELATION_CONTABIL => explode(',', $camposView[PreRegistroService::RELATION_CONTABIL]),
            PreRegistroService::RELATION_PF => explode(',', $camposView[PreRegistroService::RELATION_PF]),
        ] : [
            PreRegistroService::RELATION_PRE_REGISTRO => explode(',', $camposView[PreRegistroService::RELATION_PRE_REGISTRO]),
            PreRegistroService::RELATION_CONTABIL => explode(',', $camposView[PreRegistroService::RELATION_CONTABIL]),
            PreRegistroService::RELATION_PJ => explode(',', $camposView[PreRegistroService::RELATION_PJ]),
            PreRegistroService::RELATION_RT => explode(',', $camposView[PreRegistroService::RELATION_RT]),
        ];

        foreach($request as $key => $value)
        {
            foreach($array as $relacao => $campos)
                if(in_array($key, $campos))
                {
                    $classe = $relacao;
                    break;
                }

            if(isset($classe))
                $camposLimpos[$classe][$this->limparNomeCamposAjax($classe, $key)] = $value;
        }

        return $camposLimpos;
    }

    private function getNomeClasses()
    {
        return [
            PreRegistroService::RELATION_ANEXOS,
            PreRegistroService::RELATION_CONTABIL,
            PreRegistroService::RELATION_PF,
            PreRegistroService::RELATION_PJ,
            PreRegistroService::RELATION_PRE_REGISTRO,
            PreRegistroService::RELATION_RT,
            PreRegistroService::RELATION_USER_EXTERNO,
        ];
    }

    private function abortar($preRegistro)
    {
        // Inserir para não aceitar se já esta num status que não pode mais editar o formulario
        if(!isset($preRegistro)/* || ()*/)
            abort(401, 'Não autorizado a acessar a solicitação de registro');
    }

    private function getRTGerenti(GerentiRepositoryInterface $gerentiRepository, $cpf)
    {
        $resultadosGerenti = $gerentiRepository->gerentiBusca("", null, $cpf);
        $ass_id = null;
        $nome = null;
        $gerenti = array();

        // Para testar colocar 5 em "ASS_TP_ASSOC" ao buscar em GerentiRepositoryMock
        if(count($resultadosGerenti) > 0)
            foreach($resultadosGerenti as $resultado)
            {
                $naoCancelado = $resultado['CANCELADO'] == "F";
                $ativo = $resultado['ASS_ATIVO'] == "T";
                $tipo = $resultado["ASS_TP_ASSOC"] == 5;

                if($naoCancelado && $ativo && $tipo)
                {
                    $ass_id = $resultado["ASS_ID"];
                    $gerenti['nome'] = $resultado["ASS_NOME"];
                    $gerenti['registro'] = $resultado["ASS_REGISTRO"];
                }
            }
        
        if(isset($ass_id))
        {
            // Confirmar se necessita de mais dados para o RT
            $resultadosGerenti = utf8_converter($gerentiRepository->gerentiDadosGeraisPF($ass_id));

            $gerenti['nome_mae'] = $resultadosGerenti['Nome da mãe'];
            $gerenti['nome_pai'] = $resultadosGerenti['Nome do pai'];
            $gerenti['identidade'] = $resultadosGerenti['identidade'];
            $gerenti['orgao_emissor'] = $resultadosGerenti['emissor'];
            $gerenti['dt_expedicao'] = $resultadosGerenti['expedicao'];
            $gerenti['dt_nascimento'] = $resultadosGerenti['Data de nascimento'];
            $gerenti['sexo'] = $resultadosGerenti['Sexo'];
            $gerenti['cpf'] = $cpf;
        }

        return $gerenti;
    }

    public function getNomesCampos()
    {
        $classes = $this->getNomeClasses();

        return [
            $classes[0] => 'path',
            $classes[1] => 'nome_contabil,cnpj_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            $classes[4] => 'registro_secundario,ramo_atividade,segmento,idregional,cep,bairro,logradouro,numero,complemento,cidade,uf,tipo_telefone,telefone,tipo_telefone_1,telefone_1',
            $classes[2] => 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade,nome_mae,nome_pai,identidade,orgao_emissor,dt_expedicao',
            $classes[3] => 'razao_social,capital_social,nire,tipo_empresa,dt_inicio_atividade,inscricao_estadual,inscricao_municipal,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            $classes[5] => 'nome_rt,nome_social_rt,registro,sexo_rt,dt_nascimento_rt,cpf_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt'
        ];
    }

    public function verificacao(GerentiRepositoryInterface $gerentiRepository)
    {
        $externo = auth()->guard('user_externo')->user();
        $resultadosGerenti = $gerentiRepository->gerentiBusca("", null, $externo->cpf_cnpj);
        $gerenti = null;

        // Registro não pode estar cancelado, deve estar ativo e se for pf busca pelo codigo de pf, rt e pj
        if(count($resultadosGerenti) > 0)
            foreach($resultadosGerenti as $resultado)
            {
                $naoCancelado = $resultado['CANCELADO'] == "F";
                $ativo = $resultado['ASS_ATIVO'] == "T";
                $pf = $externo->isPessoaFisica() && (($resultado["ASS_TP_ASSOC"] == 2) || ($resultado["ASS_TP_ASSOC"] == 5));
                $pj = !$externo->isPessoaFisica() && ($resultado["ASS_TP_ASSOC"] == 1);
                if($naoCancelado && $ativo && ($pf || $pj))
                    $gerenti = $resultado['ASS_REGISTRO'];
            }

        $preRegistro = isset($gerenti) ? null : $externo->preRegistro;

        return [
            'gerenti' => $gerenti,
            'resultado' => $preRegistro,
        ];
    }

    public function getPreRegistro(MediadorServiceInterface $service, $resultado)
    {
        // Falta logExterno
        if(!isset($resultado))
        {
            $resultado = $externo->preRegistro()->create();
            $externo->isPessoaFisica() ? $resultado->pessoaFisica()->create() : $resultado->pessoaJuridica()->create();
        }

        return [
            'resultado' => $resultado,
            'codigos' => $this->getCodigos(),
            'regionais' => $service->getService('Regional')
                ->all()
                ->splice(0, 13)
                ->sortBy('regional'),
            'classes' => $this->getNomeClasses(),
            'totalFiles' => $this->totalFiles,
        ];
    }

    public function saveSiteAjax($request, GerentiRepositoryInterface $gerentiRepository)
    {
        $preRegistro = auth()->guard('user_externo')->user()->preRegistro;

        // Inserir para não aceitar se já esta num status que não pode mais editar o formulario
        $this->abortar($preRegistro);

        $resultado = null;
        $objeto = collect();
        $classeCriar = array_search($request['classe'], $this->getRelacoes());

        if(($request['classe'] != PreRegistroService::RELATION_ANEXOS) && ($request['classe'] != PreRegistroService::RELATION_PRE_REGISTRO))
            $objeto = $preRegistro->whereHas($request['classe'])->get();
        
        $request['campo'] = $this->limparNomeCamposAjax($request['classe'], $request['campo']);
        $gerenti = ($request['classe'] == PreRegistroService::RELATION_RT) && ($request['campo'] == 'cpf') ? 
            $this->getRTGerenti($gerentiRepository, $request['valor']) : null;

        if(($request['classe'] == PreRegistroService::RELATION_PRE_REGISTRO) || $objeto->isNotEmpty())
            $resultado = $preRegistro->atualizarAjax($request['classe'], $request['campo'], $request['valor'], $gerenti);
        else
            $resultado = $preRegistro->criarAjax($classeCriar, $request['classe'], $request['campo'], $request['valor'], $gerenti);

        return $resultado;
    }

    public function saveSite($request)
    {
        // Falta logExterno
        $externo = auth()->guard('user_externo')->user();
        $preRegistro = $externo->preRegistro;

        // Inserir para não aceitar se já esta num status que não pode mais editar o formulario
        $this->abortar($preRegistro);

        $camposLimpos = $this->limparNomeCampos($externo, $request);

        foreach($camposLimpos as $key => $arrayCampos)
        {
            $objeto = null;
            if($key != PreRegistroService::RELATION_PRE_REGISTRO)
            {
                $objeto = $preRegistro->whereHas($key)->get();
                $resultado = $objeto->isNotEmpty() ? $preRegistro->salvar($key, $arrayCampos) : 
                    $preRegistro->salvar($key, $arrayCampos, array_search($key, $this->getRelacoes()));
            } else
                $resultado = $preRegistro->salvar($key, $arrayCampos);
            
            if(!isset($resultado))
                abort(500);
        }

        $resultado = $preRegistro->update(['status' => $preRegistro::STATUS_ANALISE_INICIAL]);

        if(!isset($resultado))
            abort(500);
        
        return [
            'message' => '<i class="icon fa fa-check"></i> Solicitação de registro enviada para análise!',
            'class' => 'alert-success'
        ];
    }

    public function downloadAnexo($id)
    {
        $preRegistro = auth()->guard('user_externo')->user()->preRegistro;

        // Inserir para não aceitar se já esta num status que não pode mais editar o formulario
        $this->abortar($preRegistro);

        $anexo = $preRegistro->anexos()->where('id', $id)->first();

        if(isset($anexo) && Storage::exists($anexo->path))
            return response()->file(Storage::path($anexo->path), ["Cache-Control" => "no-cache"]);
        
        return null;
    }

    public function excluirAnexo($id)
    {
        // Falta logExterno
        $preRegistro = auth()->guard('user_externo')->user()->preRegistro;

        // Inserir para não aceitar se já esta num status que não pode mais editar o formulario
        $this->abortar($preRegistro);

        $anexo = $preRegistro->anexos()->where('id', $id)->first();

        if(isset($anexo) && Storage::exists($anexo->path))
        {
            if(Storage::delete($anexo->path));
                $anexo->delete();
            return $id;
        }

        return null;
    }
}