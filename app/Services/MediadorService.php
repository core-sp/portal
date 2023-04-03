<?php

namespace App\Services;

use App\Contracts\MediadorServiceInterface;

class MediadorService implements MediadorServiceInterface {

    // Nome do servico identico ao nome no Contrato na pasta App\Contracts. Subservices não são permitidos.
    public function getService($nomeModel)
    {
        try{
            if(preg_match('/Sub$/', $nomeModel) == 1)
                throw new \Exception('Sub Service deve ser chamado pelo serviço principal', 500);
            return resolve('App\Contracts\\' . $nomeModel . 'ServiceInterface');
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Serviço '.$nomeModel.' não encontrado no Sistema');
        }
    }
}