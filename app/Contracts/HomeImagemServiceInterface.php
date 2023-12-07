<?php

namespace App\Contracts;

interface HomeImagemServiceInterface {

    public function carrossel($array = null);

    public function itensHome($dados = null);

    public function getItens();

    public function itensHomeStorage($folder = null, $file = null);

    public function uploadFileStorage($file);

    public function downloadFileStorage($folder, $arquivo);
}