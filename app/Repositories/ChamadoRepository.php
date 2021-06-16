<?php

namespace App\Repositories;

use App\Chamado;

class ChamadoRepository 
{
    public function getById($id)
    {
        return Chamado::findOrFail($id);
    }

    public function getByIdWithTrashed($id)
    {
        return Chamado::withTrashed()->findOrFail($id);
    }

    public function getChamadoByIdUsuario($idusuario)
    {
        return Chamado::where("idusuario", $idusuario)->withTrashed()->orderBy("created_at", "DESC")->paginate(10);
    }

    public function getAllChamados()
    {
        return Chamado::orderBy('created_at','DESC')->paginate(10);
    }

    public function getAllTrashedChamados()
    {
        return Chamado::onlyTrashed()->orderBy('idchamado','DESC')->paginate(10);
    }

    public function busca($criterio)
    {
        return Chamado::where('tipo', 'LIKE', '%' . $criterio . '%')
            ->orWhere('prioridade', 'LIKE', '%' . $criterio . '%')
            ->orWhere('mensagem', 'LIKE', '%' . $criterio . '%')
            ->paginate(10);
    }

    public function store($chamado)
    {
        return Chamado::create($chamado);
    }

    public function update($id, $chamado)
    {
        return Chamado::findOrFail($id)->update($chamado);
    }
    
    public function updateResposta($id, $resposta)
    {
        return Chamado::withTrashed()->findOrFail($id)->update(["resposta" => $resposta]);
    }

    public function delete($id)
    {
        return Chamado::findOrFail($id)->delete();
    }

    public function restore($id)
    {
        return Chamado::onlyTrashed()->findOrFail($id)->restore();
    }
}