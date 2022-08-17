<?php

namespace App\Services;

use App\Contracts\MediadorServiceInterface;
use App\Contracts\SuporteServiceInterface;
use App\Contracts\PlantaoJuridicoServiceInterface;
use App\Contracts\RegionalServiceInterface;
use App\Contracts\TermoConsentimentoServiceInterface;
use App\Contracts\AgendamentoServiceInterface;
use App\Contracts\LicitacaoServiceInterface;
use App\Contracts\FiscalizacaoServiceInterface;
use App\Contracts\PostServiceInterface;

class MediadorService implements MediadorServiceInterface {

    private $service;

    // Adicione o Serviço e o nome do Model
    public function __construct(
        SuporteServiceInterface $suporteService,
        PlantaoJuridicoServiceInterface $plantaoJuridicoService,
        RegionalServiceInterface $regionalService,
        TermoConsentimentoServiceInterface $termoConsentimentoService,
        AgendamentoServiceInterface $agendamentoService,
        LicitacaoServiceInterface $licitacaoService,
        FiscalizacaoServiceInterface $fiscalizacaoService,
        PostServiceInterface $postService
    )
    {
        $this->service = [
            'Suporte' => $suporteService,
            'PlantaoJuridico' => $plantaoJuridicoService,
            'Regional' => $regionalService,
            'TermoConsentimento' => $termoConsentimentoService,
            'Agendamento' => $agendamentoService,
            'Licitacao' => $licitacaoService,
            'Fiscalizacao' => $fiscalizacaoService,
            'Post' => $postService
        ];
    }

    public function getService($nomeModel)
    {
        return isset($this->service[$nomeModel]) ? $this->service[$nomeModel] : abort(500, 'Serviço '.$nomeModel.' não encontrado no MediadorService');
    }
}