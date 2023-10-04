<?php

namespace App\Contracts;

use App\Contracts\MediadorServiceInterface;

interface SalaReuniaoServiceInterface {
    
    public function getHorasPeriodo($periodo);

    public function getItensByTipo($tipo);

    public function listar($user, MediadorServiceInterface $service);

    public function view($id);

    public function save($dados, $id, $user);

    public function salasAtivas($tipo = null);

    public function getDiasHoras($tipo, $id, $dia = null, $user = null);

    public function getTodasHorasById($id);

    public function getHorarioFormatadoById($id, $arrayHorarios, $final_manha = null, $final_tarde = null);

    public function site();

    public function agendados();

    public function bloqueio();

    public function suspensaoExcecao();
}