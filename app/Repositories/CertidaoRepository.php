<?php

namespace App\Repositories;

use App\Certidao;

class CertidaoRepository 
{
    public function store($tipo, $tipoPessoa, $dadosRepresentante, $endereco, $dadosParcelamento = null)
    {
        // Certidão é criada no abnco primeiro para obter gerar a data de emissão e o ID
        $certidao = Certidao::create([
            "tipo" => $tipo,
            "cpf_cnpj" => preg_replace('/[^0-9]+/', '', $dadosRepresentante["cpf_cnpj"]),
            "hora_emissao" => date("H:i"),
            "data_emissao" => date("Y-m-d")
        ]);

        // Gera a declaração
        switch($tipo) {
            case Certidao::$tipo_regularidade:
                $declaracao = Certidao::declaracaoRegularidade(false, $tipoPessoa, $dadosRepresentante, $endereco, $certidao->data_emissao);
            break;

            case Certidao::$tipo_parcelamento:
                $declaracao = Certidao::declaracaoParcelamento(false, $tipoPessoa, $dadosRepresentante, $endereco, $dadosParcelamento);
            break;
        }

        // Construir o código da certidão. Encriptado com MD5 (32 caracteres hexadecimais)
        $idCodificado = strtoupper(md5(uniqid() . "-" . $certidao->id));

        // Atualiza a certidão com o código gerado
        $certidao->update(["codigo" => $idCodificado, "declaracao" => $declaracao]);

        return $certidao;
    }

    public function consultaCertidao($codigo, $hora, $data)
    {
        return Certidao::where("codigo", $codigo)
            ->where("hora_emissao", date("H:i", strtotime($hora)))
            ->where("data_emissao", date("Y-m-d", strtotime(str_replace("/", "-", $data))))
            ->whereBetween("data_emissao", [date('Y-m-d', strtotime('-30 days')), date("Y-m-d")])
            ->First();
    }
}