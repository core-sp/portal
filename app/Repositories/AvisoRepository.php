<?php

namespace App\Repositories;

use App\Aviso;

class AvisoRepository 
{
    // public function getAll()
    // {
    //     return Aviso::orderBy('id')->paginate(5);
    // }

    // public function getById($id)
    // {
    //     return Aviso::findOrFail($id);
    // }

    // public function getByArea($area)
    // {
    //     return Aviso::where('area', $area)->first();
    // }

    // public function avisoAtivado($area)
    // {
    //     $aviso = $this->getByArea($area);
    //     return isset($aviso) ? $aviso->isAtivado() : false;
    // }

    // public function update($request, $id, $user)
    // {
    //     return $this->getById($id)
    //     ->update([
    //         'titulo' => $request->titulo,
    //         'conteudo' => $request->conteudo,
    //         'cor_fundo_titulo' => $request->cor_fundo_titulo,
    //         'idusuario' => $user->idusuario
    //     ]);
    // }

    // public function updateCampoStatus($id, $user)
    // {
    //     $aviso = $this->getById($id);
    //     return $aviso->update([
    //         'status' => $aviso->isAtivado() ? Aviso::DESATIVADO : Aviso::ATIVADO,
    //         'idusuario' => $user->idusuario
    //     ]);
    // }

    // public function cores()
    // {
    //     return [
    //         'bg-light',
    //         'bg-info',
    //         'bg-warning',
    //         'bg-primary',
    //         'bg-success',
    //         'bg-danger',
    //         'bg-secondary',
    //         'bg-dark'
    //     ];
    // }
}