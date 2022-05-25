<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface PreRegistroServiceInterface {

    public function getNomesCampos();
    
    public function verificacao();
    
    public function getPreRegistro(MediadorServiceInterface $service);

    public function saveSiteAjax($request);

    public function saveSite($request);

    public function downloadAnexo($id);

    public function excluirAnexo($id);
}