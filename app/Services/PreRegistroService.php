<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Facades\Storage;

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

            if($classe)
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

    public function verificacao()
    {
        $externo = auth()->guard('user_externo')->user();
        // Verificar via Gerenti se já existe o cpf ou cnpj como representante
        // Caso sim, dar uma mensagem mostrando o registro dele e a atual situação (em dia, bloqueado etc)
        // Caso não, permitr a solicitação de registro

        return 'dados caso possua registro ou mensagem que pode iniciar a solicitação de registro';
    }

    public function getPreRegistro(MediadorServiceInterface $service)
    {
        $externo = auth()->guard('user_externo')->user();
        // Verificar com o metodo verificacao() para impedir de acessar o formulario
        // Caso não, verificar se já tem o pre registro salvo no banco
        $resultado = $externo->preRegistro;

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

    public function saveSiteAjax($request)
    {
        $preRegistro = auth()->guard('user_externo')->user()->preRegistro;
        $resultado = null;
        $objeto = collect();
        $classeCriar = array_search($request['classe'], $this->getRelacoes());

        if(($request['classe'] != PreRegistroService::RELATION_ANEXOS) && ($request['classe'] != PreRegistroService::RELATION_PRE_REGISTRO))
            $objeto = $preRegistro->whereHas($request['classe'])->get();
        
        $request['campo'] = $this->limparNomeCamposAjax($request['classe'], $request['campo']);

        if(($request['classe'] == PreRegistroService::RELATION_PRE_REGISTRO) || $objeto->isNotEmpty())
            $resultado = $preRegistro->atualizarAjax($request['classe'], $request['campo'], $request['valor']);
        else
            $resultado = $preRegistro->criarAjax($classeCriar, $request['classe'], $request['campo'], $request['valor']);

        return $resultado;
    }

    public function saveSite($request)
    {
        $externo = auth()->guard('user_externo')->user();
        $preRegistro = $externo->preRegistro;
        $camposLimpos = $this->limparNomeCampos($externo, $request);

        foreach($camposLimpos as $key => $arrayCampos)
        {
            $objeto = null;
            if($key != PreRegistroService::RELATION_PRE_REGISTRO)
            {
                $objeto = $preRegistro->whereHas($key)->get();
                if($objeto->isNotEmpty())
                    $resultado = $preRegistro->salvar($key, $arrayCampos);
                else
                    $resultado = $preRegistro->salvar($key, $arrayCampos, array_search($key, $this->getRelacoes()));
            } else
                $resultado = $preRegistro->salvar($key, $arrayCampos);
            
            // se falhar o update
        }

        $preRegistro->update(['status' => $preRegistro::STATUS_ANALISE_INICIAL]);
    }

    public function downloadAnexo($id)
    {
        $anexo = auth()->guard('user_externo')->user()->preRegistro->anexos()->where('id', $id)->first();
        if(isset($anexo) && Storage::exists($anexo->path))
            return response()->file(Storage::path($anexo->path), ["Cache-Control" => "no-cache"]);
    }

    public function excluirAnexo($id)
    {
        $anexo = auth()->guard('user_externo')->user()->preRegistro->anexos()->where('id', $id)->first();
        if(isset($anexo) && Storage::exists($anexo->path))
        {
            if(Storage::delete($anexo->path));
                $anexo->delete();
            return $id;
        }

        return null;
    }
}