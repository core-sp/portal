<?php

namespace App\Repositories;

use App\TermoConsentimento;

class TermoConsentimentoRepository 
{
    public function getAll()
    {
        return TermoConsentimento::orderBy('id','DESC')
            ->paginate(10);
    }

    public function getById($id)
    {
        return TermoConsentimento::findOrFail($id);
    }

    public function create($ip, $registro_core) 
    {
        return TermoConsentimento::create([
            "ip" => $ip,
            "registro_core" => $registro_core
        ]);
    }
}