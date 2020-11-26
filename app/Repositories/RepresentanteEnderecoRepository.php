<?php

namespace App\Repositories;

use App\RepresentanteEndereco;

class RepresentanteEnderecoRepository {

    public function getCountByAssId($assId)
    {
        return RepresentanteEndereco::where("ass_id", $assId)
            ->where("status", "Aguardando confirmação")
            ->count();
    }

    public function create($assId, $endereco, $image, $imageDois = null) 
    {
        return RepresentanteEndereco::create([
            "ass_id" => $assId,
            "cep" => $endereco["cep"],
            "bairro" => $endereco["bairro"],
            "logradouro" => $endereco["logradouro"],
            "numero" => $endereco["numero"],
            "complemento" => $endereco["complemento"],
            "estado" => $endereco["estado"],
            "municipio" => $endereco["municipio"],
            "crimage" => $image,
            "crimagedois" => $imageDois,
            "status" => "Aguardando confirmação"
        ]);
    }
}