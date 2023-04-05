<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface AgendamentoSiteSubServiceInterface {

    public function view(MediadorServiceInterface $service);

    public function save($dados, $ip, MediadorServiceInterface $service);

    public function consulta($dados);

    public function cancelamento($dados);

    public function getDiasHorasAjax($dados);
}