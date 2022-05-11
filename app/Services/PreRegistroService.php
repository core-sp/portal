<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Contracts\MediadorServiceInterface;

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

        $resultado = $externo->preRegistro == null ? $externo->preRegistro()->create() : $externo->preRegistro;

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
        $classes = [
            'Anexo',
            'Contabil',
            'PreRegistro',
            'PreRegistroCpf',
            'PreRegistroCnpj',
            'ResponsavelTecnico'
        ];

        $externo = auth()->guard('user_externo')->user();
        // $externo->preRegistro->update(['numero' => $request['teste']]);
        
        return $request;
    }
}