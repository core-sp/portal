<?php

namespace App\Services;

use App\Contracts\PlantaoJuridicoServiceInterface;
use Carbon\Carbon;

class PlantaoJuridicoService implements PlantaoJuridicoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'plantão jurídico',
            'singulariza' => 'o plantão jurídico',
            'plural' => 'plantões jurídicos',
            'pluraliza' => 'plantão jurídico',
            'form' => 'plantao_juridico'
        ];
    }
}