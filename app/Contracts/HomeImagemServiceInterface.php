<?php

namespace App\Contracts;

interface HomeImagemServiceInterface {

    public function carrossel($array = null);

    public function itensHome($dados = null);

    public function getItens();

    public function itensHomeStorage($file = null);

    public function uploadFileStorage($file);
}