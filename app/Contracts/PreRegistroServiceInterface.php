<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface PreRegistroServiceInterface {

    public function verificacao();
    
    public function getPreRegistro(MediadorServiceInterface $service);

    public function saveSiteAjax($request);
}