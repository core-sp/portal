<?php

namespace App\Services;

use App\Contracts\MediadorServiceInterface;
use App\Contracts\SuporteServiceInterface;
use App\Contracts\RegionalServiceInterface;

class MediadorService implements MediadorServiceInterface {

    private $service;

    // Adicione o Serviço e o nome do Model
    public function __construct(
        SuporteServiceInterface $suporteService,
        RegionalServiceInterface $regionalService
    )
    {
        $this->service = [
            'Suporte' => $suporteService,
            'Regional' => $regionalService,
        ];
    }

    public function getService($nomeModel)
    {
        return isset($this->service[$nomeModel]) ? $this->service[$nomeModel] : abort(500, 'Serviço '.$nomeModel.' não encontrado no MediadorService');
    }
}