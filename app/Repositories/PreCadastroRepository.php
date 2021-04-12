<?php

namespace App\Repositories;

use App\PreCadastro;

class PreCadastroRepository {

    public function getToTable()
    {
        return PreCadastro::orderBy('id','DESC')
            ->paginate(10);
    }

    public function getById($id)
    {
        return PreCadastro::findOrFail($id);
    }

    public function store($request, $nomeAnexo1, $nomeAnexo2)
    {
        return PreCadastro::create([
            'cpf' => $request->cpf,
            'cnpj' => $request->cnpj,
            'tipo' => PreCadastro::TIPO_PF,
            'nome' => $request->nome,
            'email' => $request->email,
            'anexo1' => $nomeAnexo1,
            'anexo2' => $nomeAnexo2,
            'status' => PreCadastro::STATUS_PEDENTE
        ]);
    }

    public function updateStatus($preCadastro, $status, $motivo = null) 
    {
        $preCadastro->status = $status;

        if($motivo != null) {
            $preCadastro->motivo = $motivo;
        }

        $preCadastro->update();
    }
}