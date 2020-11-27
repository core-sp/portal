<?php

namespace App\Repositories;

use App\RepresentanteEndereco;

class RepresentanteEnderecoRepository 
{
    public function getAll()
    {
        return RepresentanteEndereco::orderBy('id','DESC')
            ->paginate(10);
    }

    public function getById($id)
    {
        return RepresentanteEndereco::findOrFail($id);
    }

    public function getCountAguardandoConfirmacaoByAssId($assId)
    {
        return RepresentanteEndereco::where("ass_id", $assId)
            ->where("status", RepresentanteEndereco::STATUS_AGUARDANDO_CONFIRMACAO)
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
            "status" => RepresentanteEndereco::STATUS_AGUARDANDO_CONFIRMACAO
        ]);
    }

    public function updateStatusEnviado($id)
    {
        return RepresentanteEndereco::findOrFail($id)
            ->update(["status" => RepresentanteEndereco::STATUS_ENVIADO]);
    }

    public function updateStatusRecusado($id, $observacao)
    {
        return RepresentanteEndereco::findOrFail($id)
            ->update(["status" => RepresentanteEndereco::STATUS_RECUSADO, "observacao" => $observacao]);
    }
}