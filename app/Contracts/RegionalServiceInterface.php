<?php

namespace App\Contracts;

interface RegionalServiceInterface {

    public function index();

    public function view($id);

    public function save($validated, $id);

    public function viewSite($id);

    public function buscar($busca);

    public function all();

    public function getById($id);

    /**
     * 
     * Métodos abaixo temporários até refatorar suas respectivas classes
     * Apenas copia e cola do repositório
     * 
    */

    public function getRegionaisAgendamento();

    public function getAgeporhorarioById($id);

    public function getHorariosAgendamento($id, $dia);

    public function getToList();

    public function getByName($regional);
}