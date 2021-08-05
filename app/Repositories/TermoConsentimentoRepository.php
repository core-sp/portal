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

    public function getByEmail($email)
    {
        return TermoConsentimento::where('email', $email)->first();
    }

    public function create($ip, $email) 
    {
        return TermoConsentimento::create([
            "ip" => $ip,
            "email" => $email
        ]);
    }

    public function getListaTermosAceitos()
    {
        return TermoConsentimento::select('email','created_at')->whereNotNull('email')->get();
    }
}