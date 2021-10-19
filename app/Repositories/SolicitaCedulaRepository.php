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

    public function getByStatusEmAndamento($idrepresentante)
    {
        return SolicitaCedula::where('status', '=', 'Em andamento')->where('idrepresentante', '=', $idrepresentante)->first();
    }

    public function getAllByIdRepresentante($id)
    {
        return SolicitaCedula::where('idrepresentante', $id)->orderBy('id','DESC')->paginate(5);
    }

    public function create($idrepresentante, $idregional, $endereco) 
    {
        return SolicitaCedula::create([
            "idrepresentante" => $idrepresentante,
            "idregional" => $idregional,
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

    public function updateStatusAceito($id, $iduser)
    {
        return SolicitaCedula::findOrFail($id)
            ->update(["status" => SolicitaCedula::STATUS_ACEITO, 'idusuario' => $iduser]);
    }

    public function updateStatusRecusado($id, $justificativa, $iduser)
    {
        return SolicitaCedula::findOrFail($id)
            ->update(["status" => SolicitaCedula::STATUS_RECUSADO, "justificativa" => $justificativa, 'idusuario' => $iduser]);
    }

    public function getBusca($busca)
    {
        return SolicitaCedula::where('id', $busca)
            ->orWhere('status','LIKE','%'.$busca.'%')
            ->orWhereHas(
                'representante', function ($query) use ($busca) {
                    $query->where('cpf_cnpj', 'LIKE','%'.$busca.'%')
                    ->orWhere('nome','LIKE','%'.$busca.'%')
                    ->orWhere('registro_core','LIKE','%'.$busca.'%');
                })
            ->orWhereHas(
                'regional', function ($query) use ($busca) {
                    $query->where('regional', 'LIKE','%'.$busca.'%');
                }
            )
            ->paginate(10);
    }

    public function getToTableFilter($mindia, $maxdia)
    {
        return SolicitaCedula::whereDate('created_at', '>=', $mindia)->whereDate('created_at', '<=', $maxdia)
            ->orderBy('id','ASC')
            ->limit(50)
            ->paginate(15);
    }
}