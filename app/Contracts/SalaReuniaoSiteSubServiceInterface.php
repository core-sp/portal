<?php

namespace App\Contracts;

interface SalaReuniaoSiteSubServiceInterface {

    public function verificaPodeAgendar($user);
    
    public function save($dados, $user);

    public function verificaPodeEditar($id, $user);
}