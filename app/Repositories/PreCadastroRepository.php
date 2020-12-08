<?php

namespace App\Repositories;

use App\PreCadastro;

class PreCadastroRepository {

    public function getById($id)
    {
        return PreCadastro::findOrFail($id);
    }

    public function store($request, $nomeAnexo)
    {
        return PreCadastro::create([
            'cpf' => $request->cpf,
            'cnpj' => $request->cnpj,
            'tipo' => PreCadastro::TIPO_PF,
            'nome' => $request->nome,
            'anexo' => $nomeAnexo,
            'status' => PreCadastro::STATUS_PEDENTE
        ]);
    }
}