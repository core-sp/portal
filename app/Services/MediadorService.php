<?php

namespace App\Services;

use App\Contracts\MediadorServiceInterface;

class MediadorService implements MediadorServiceInterface {

    private $service = [
        'Suporte' => 'App\Contracts\SuporteServiceInterface',
        'PlantaoJuridico' => 'App\Contracts\PlantaoJuridicoServiceInterface',
        'Regional' => 'App\Contracts\RegionalServiceInterface',
        'TermoConsentimento' => 'App\Contracts\TermoConsentimentoServiceInterface',
        'Agendamento' => 'App\Contracts\AgendamentoServiceInterface',
        'Licitacao' => 'App\Contracts\LicitacaoServiceInterface',
        'Fiscalizacao' => 'App\Contracts\FiscalizacaoServiceInterface',
        'Post' => 'App\Contracts\PostServiceInterface',
        'Noticia' => 'App\Contracts\NoticiaServiceInterface',
        'Cedula' => 'App\Contracts\CedulaServiceInterface',
        'Representante' => 'App\Contracts\RepresentanteServiceInterface',
    ];

    public function getService($nomeModel)
    {
        if(isset($this->service[$nomeModel]))
            return resolve($this->service[$nomeModel]);
        \Log::error('[Erro: Serviço ' . $nomeModel . ' não encontrado no MediadorService.], [Código: 500], [Arquivo: App\Contracts\MediadorServiceInterface]');
        abort(500, 'Serviço '.$nomeModel.' não encontrado no Sistema');
    }
}