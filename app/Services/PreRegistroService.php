<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Contracts\MediadorServiceInterface;

class PreRegistroService implements PreRegistroServiceInterface {

    const RELATION_ANEXOS = "anexos";
    const RELATION_CONTABIL = "contabil";
    const RELATION_PF = "pessoaFisica";
    const RELATION_PJ = "pessoaJuridica";
    const RELATION_PRE_REGISTRO = "preRegistro";
    const RELATION_RT = "pessoaJuridica.responsavelTecnico";
    const RELATION_USER_EXTERNO = 'userExterno';

    public function __construct()
    {
        
    }

    private function getCodigos()
    {
        $codigos = array();
        $relacoes = [
            'App\Anexo' => PreRegistroService::RELATION_ANEXOS,
            'App\Contabil' => PreRegistroService::RELATION_CONTABIL,
            'App\PreRegistroCpf' => PreRegistroService::RELATION_PF,
            'App\PreRegistroCnpj' => PreRegistroService::RELATION_PJ,
            'App\PreRegistro' => PreRegistroService::RELATION_PRE_REGISTRO,
            'App\ResponsavelTecnico' => PreRegistroService::RELATION_RT,
            'App\UserExterno' => PreRegistroService::RELATION_USER_EXTERNO,
        ];
        $models = [
            'App\UserExterno' => null,
            'App\PreRegistro' => null,
            'App\PreRegistroCpf' => null,
            'App\PreRegistroCnpj' => null,
            'App\Contabil' => null,
            'App\ResponsavelTecnico' => null,
            'App\Anexo' => null
        ];

        foreach($models as $key => $model)
            $codigos[$relacoes[$key]] = $key::codigosPreRegistro();
        
        return $codigos;
    }

    private function limparNomeCamposAjax($classe, $campo)
    {
        $chave = false;
        $campos = $this->getCodigos()[$classe];
        $siglas = [
            'anexos' => null,
            'preRegistro' => null,
            'pessoaFisica' => null,
            'pessoaJuridica' => '_empresa',
            'contabil' => '_contabil',
            'pessoaJuridica.responsavelTecnico' => '_rt',
            'userExterno' => null,
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

    public function getNomeClasses()
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
            'classes' => $this->getNomeClasses()
        ];
    }

    public function saveSiteAjax($request)
    {
        $preRegistro = auth()->guard('user_externo')->user()->preRegistro;
        $resultado = null;
        $objeto = collect();

        if(($request['classe'] != PreRegistroService::RELATION_ANEXOS) && ($request['classe'] != PreRegistroService::RELATION_PRE_REGISTRO))
            $objeto = $preRegistro->whereHas($request['classe'])->get();
        
        $request['campo'] = $this->limparNomeCamposAjax($request['classe'], $request['campo']);

        if(($request['classe'] == PreRegistroService::RELATION_PRE_REGISTRO) || $objeto->isNotEmpty())
            $resultado = $preRegistro->atualizarAjax($request['classe'], $request['campo'], $request['valor']);
        elseif(isset($request['valor']) && strlen($request['valor']) > 0)
            $resultado = $preRegistro->criarAjax($request['classe'], $request['campo'], $request['valor']);

        return $request['campo'];
    }
}