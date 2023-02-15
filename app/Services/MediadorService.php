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
use App\Contracts\NoticiaServiceInterface;
use App\Contracts\AvisoServiceInterface;

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
        PostServiceInterface $postService,
        NoticiaServiceInterface $noticiaService,
        AvisoServiceInterface $avisoService
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
            'Post' => $postService,
            'Noticia' => $noticiaService,
            'Aviso' => $avisoService,
        ];
    }

    public function getService($nomeModel)
    {
        return isset($this->service[$nomeModel]) ? $this->service[$nomeModel] : abort(500, 'Serviço '.$nomeModel.' não encontrado no MediadorService');
    }
}