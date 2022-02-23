<?php

namespace App\Services;

use App\Contracts\MediadorServiceInterface;
use App\Contracts\SuporteServiceInterface;
use App\Contracts\PlantaoJuridicoServiceInterface;
use App\Contracts\RegionalServiceInterface;

class MediadorService implements MediadorServiceInterface {

    private $service;

    // Adicione o Serviço e o nome do Model
    public function __construct(
        SuporteServiceInterface $suporteService,
        PlantaoJuridicoServiceInterface $plantaoJuridicoService,
        RegionalServiceInterface $regionalService
    )
    {
        $this->service = [
            'Suporte' => $suporteService,
            'PlantaoJuridico' => $plantaoJuridicoService,
            'Regional' => $regionalService,
        ];
    }

    public function getService($nomeModel)
    {
        return isset($this->service[$nomeModel]) ? $this->service[$nomeModel] : abort(500, 'Serviço '.$nomeModel.' não encontrado no MediadorService');
    }
}