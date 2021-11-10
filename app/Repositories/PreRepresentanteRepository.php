<?php

namespace App\Repositories;

use App\PreRepresentante;
use Illuminate\Support\Facades\Hash;

class PreRepresentanteRepository 
{
    public function store($validated, $token)
    {
        return PreRepresentante::create([
            'cpf_cnpj' => $validated->cpf_cnpj,
            'nome' => strtoupper($validated->nome),
            'email' => $validated->email,
            'verify_token' => $token,
            'password' => Hash::make($validated->password)
        ]);
    }

    public function update($id, $validated, $token)
    {
        PreRepresentante::withTrashed()->findOrFail($id)->restore();
        return PreRepresentante::findOrFail($id)->update([
            'cpf_cnpj' => $validated->cpf_cnpj,
            'nome' => strtoupper($validated->nome),
            'email' => $validated->email,
            'verify_token' => $token,
            'password' => Hash::make($validated->password)
        ]);
    }

    public function updatePosVerificarEmail($prerepresentante)
    {
        return PreRepresentante::findOrFail($prerepresentante->id)->update([
            'ativo' => 1,
            'verify_token' => null
        ]);
    }

    public function updateEditarNomeEmail($id, $validated)
    {
        return PreRepresentante::findOrFail($id)->update([
            'nome' => strtoupper($validated->nome),
            'email' => $validated->email
        ]);
    }

    public function updateSenha($id, $validated, $senhaAtual)
    {
        return Hash::check($validated->password_atual, $senhaAtual) ? PreRepresentante::findOrFail($id)->update([
                'password' => Hash::make($validated->password)
            ]) : false;
    }

    public function getDeletadoNaoAtivo($cpfCnpj)
    {
        return PreRepresentante::where('cpf_cnpj', $cpfCnpj)
        ->where('ativo', 0)
        ->withTrashed()
        ->first();
    }

    public function getByToken($token)
    {
        return PreRepresentante::where('verify_token', $token)->first();
    }

    public function getByCpfCnpj($cpfCnpj)
    {
        return PreRepresentante::where('cpf_cnpj', $cpfCnpj)->first();
    }

    public function getById($id)
    {
        return PreRepresentante::findOrFail($id);
    }
}