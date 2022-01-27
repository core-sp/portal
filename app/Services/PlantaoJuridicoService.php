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
            'form' => 'plantao_juridico',
            'titulo_criar' => 'Criar plantão jurídico',
            // 'btn_criar' => '<a href="'.route('plantao.juridico.create').'" class="btn btn-primary mr-1">Novo Plantão Jurídico</a>',
        ];
    }

    public function listar()
    {
        return $dados = [
            'resultados' => null,
            'variaveis' => (object) $this->variaveis
        ];
    }
}