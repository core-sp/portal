<?php

namespace App\Repositories;

use App\Perfil;

class PerfilRepository {
    
    public function findOrFail($id)
    {
        return Perfil::findOrFail($id);
    }

    /** Busca usada para listar perfis no portal.admin */
    public function getToTable()
    {
        $resultados = Perfil::select('idperfil','nome')
            ->withCount('user')
            ->orderBy('nome','ASC')
            ->paginate(10);

        return $resultados;
    }

    public function store($request)
    {
        return Perfil::create([
            'nome' => $request->nome
        ]);
    }

    public function destroy($id)
    {
        return Perfil::findOrFail($id)->delete();
    }
}