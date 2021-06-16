<?php

namespace App\Repositories;

use App\Certidao;

class CertidaoRepository 
{
    /**
     * Método salva a certidão.
     */
    public function store($tipo, $declaracao, $numero, $codigo, $data, $hora, $dataValidade, $dadosRepresentante)
    {
        $certidao = Certidao::create([
            'tipo' => $tipo,
            'nome' => $dadosRepresentante['nome'],
            'cpf_cnpj' => $dadosRepresentante['cpf_cnpj'],
            'registro_core' => $dadosRepresentante['registro_core'],
            'declaracao' => $declaracao,
            'numero' => $numero,
            'codigo' =>  $codigo,
            'data_emissao' => date('Y-m-d', strtotime($data)),
            'hora_emissao' => date('H:i', strtotime($hora)),
            'data_validade' => date('Y-m-d', strtotime($dataValidade)),
        ]);

        return $certidao;
    }

    /**
     * Método usado para fazer stream da certidão existente no banco de dados.
     */
    public function recuperaCertidao($numero)
    {
        return Certidao::where('numero', $numero)->first();
    }
}