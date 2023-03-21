<?php

namespace App\Services;

use App\Contracts\MediadorServiceInterface;

class MediadorService implements MediadorServiceInterface {

    // Nome do servico identico ao nome no Contrato na pasta App\Contracts.
    public function getService($nomeModel)
    {
        try{
            return resolve('App\Contracts\\' . $nomeModel . 'ServiceInterface');
        }catch(\Exception $e){
            \Log::error('[Erro: Serviço ' . $nomeModel . ' não encontrado no MediadorService.], [Código: 500], [Arquivo: App\Contracts\MediadorServiceInterface]');
            abort(500, 'Serviço '.$nomeModel.' não encontrado no Sistema');
        }
    }
}