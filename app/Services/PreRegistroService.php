<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Database\Eloquent\Builder;

class PreRegistroService implements PreRegistroServiceInterface {

    public function __construct()
    {
        
    }

    private function getCodigos()
    {
        $codigos = array();
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
            $codigos[$key] = $key::codigosPreRegistro();
        
        return $codigos;
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
                ->sortBy('regional')
        ];
    }

    public function saveSiteAjax($request)
    {
        // limpar nome dos campos
        // verificacao dos campos; se ja existe o objeto, atualiza; senao, cria
        // anexo só cria ou remove
        // $externo->preRegistro->update(['numero' => $request['teste']]);

        $teste = null;
        $preRegistro = auth()->guard('user_externo')->user()->preRegistro;
        $resultado = null;

        if($request['classe'] == 'preRegistro')
            $resultado = $preRegistro->update([$request['campo'] => $request['valor']]);
        else
        {
            $objeto = $preRegistro->whereHas($request['classe'])->get();
            if($objeto->isNotEmpty())
                $resultado = $preRegistro->atualizarRelacoesAjax($request['classe'], $request['campo'], $request['valor']);
            else
                $resultado = $preRegistro->criarRelacoesAjax($request['classe'], $request['campo'], $request['valor']);
        }

        return $resultado;
    }
}