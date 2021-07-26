<?php

namespace App\Repositories;

use App\SolicitaCedula;

class SolicitaCedulaRepository 
{
    public function getAll()
    {
        return SolicitaCedula::orderBy('id','DESC')
            ->paginate(10);
    }

    public function getById($id)
    {
        return SolicitaCedula::findOrFail($id);
    }

    public function create($idrepresentante, $endereco) 
    {
        return SolicitaCedula::create([
            "idrepresentante" => $idrepresentante,
            "cep" => $endereco["cep"],
            "bairro" => $endereco["bairro"],
            "logradouro" => $endereco["logradouro"],
            "numero" => $endereco["numero"],
            "complemento" => $endereco["complemento"],
            "estado" => $endereco["estado"],
            "municipio" => $endereco["municipio"],
            "status" => SolicitaCedula::STATUS_EM_ANDAMENTO
        ]);
    }

    public function updateStatusAprovado($id)
    {
        return SolicitaCedula::findOrFail($id)
            ->update(["status" => SolicitaCedula::STATUS_APROVADO]);
    }

    public function updateStatusReprovado($id, $justificativa)
    {
        return SolicitaCedula::findOrFail($id)
            ->update(["status" => SolicitaCedula::STATUS_REPROVADO, "justificativa" => $justificativa]);
    }

    public function getBusca($busca)
    {
        return SolicitaCedula::where('id', $busca)
            ->orWhere('status','LIKE','%'.$busca.'%')
            ->orWhereHas(
                'representante', function ($query) use ($busca) {
                    $query->where('cpf_cnpj', 'LIKE','%'.$busca.'%')
                    ->orWhere('registro_core','LIKE','%'.$busca.'%');
                }
            )
            ->paginate(10);
    }
}