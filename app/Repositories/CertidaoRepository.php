<?php

namespace App\Repositories;

use App\Certidao;

class CertidaoRepository 
{
    /**
     * Método salva a certidão e gera um código para a mesma.
     */
    public function store($tipo, $declaracao, $numero, $codigo, $data, $hora, $dataValidade)
    {
        // Certidão é criada no banco primeiro para obter gerar a data de emissão e o ID.
        $certidao = Certidao::create([
            'tipo' => $tipo,
            'declaracao' => $declaracao,
            'numero' => $numero,
            'codigo' =>  $codigo,
            'data_emissao' => date('Y-m-d', strtotime($data)),
            'hora_emissao' => date('H:i', strtotime($hora)),
            'data_validade' => date('Y-m-d', strtotime($dataValidade)),
        ]);

        return $certidao;
    }

    // /**
    //  * Método usado para verificar a autenticidade da certidão.
    //  * Usa-se o código, hora e data de emissão.
    //  * Validade de 30 dias.
    //  */
    // public function autenticaCertidao($codigo, $hora, $data)
    // {
    //     return Certidao::where("codigo", $codigo)
    //         ->where("hora_emissao", date("H:i", strtotime($hora)))
    //         ->where("data_emissao", date("Y-m-d", strtotime(str_replace("/", "-", $data))))
    //         ->whereBetween("data_emissao", [date('Y-m-d', strtotime('-30 days')), date("Y-m-d")])
    //         ->First();
    // }

    // /**
    //  * Método usado verificar se o Representante Comercial emitiu uma certidão nos últimos 30 dias.
    //  */
    // public function consultaCertidao($cpf_cnpj, $tipo)
    // {
    //     return Certidao::where("cpf_cnpj", $cpf_cnpj)
    //         ->where("tipo", $tipo)
    //         ->whereBetween("data_emissao", [date('Y-m-d', strtotime('-30 days')), date("Y-m-d")])
    //         ->orderBy('data_emissao','DESC')
    //         ->First();
    // }

    /**
     * Método usado para fazer stream da certidão existente no banco de dados.
     */
    public function recuperaCertidao($numero)
    {
        return Certidao::where('numero', $numero)->First();
    }
}