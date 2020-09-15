<?php

namespace App\Repositories;

use App\Certidao;

class CertidaoRepository 
{
    public function store($tipo, $tipoPessoa, $dadosRepresentante)
    {
        $certidao = Certidao::create([
            "tipo" => $tipo,
            "tipo_pessoa" => $tipoPessoa,
            "nome" => $dadosRepresentante["nome"], 
            "cpf_cnpj" => $dadosRepresentante["cpf_cnpj"],
            "registro_core" => $dadosRepresentante["registro_core"],
            "data_inscricao" => date('Y-m-d', strtotime(str_replace('/', '-', $dadosRepresentante["data_inscricao"]))),
            "tipo_empresa" => $dadosRepresentante["tipo_empresa"],
            "resp_tecnico" => $dadosRepresentante["resp_tecnico"],
            "resp_tecnico_registro_core" => $dadosRepresentante["resp_tecnico_registro_core"],
            "hora_emissao" => date("H:i"),
            "data_emissao" => date("Y-m-d")
        ]);

        // Construir o código da certidão. Encriptado com MD5 (32 caracteres hexadecimais)
        $idCodificado = strtoupper(md5(uniqid() . "-" . $certidao->id));

        // Atualiza a certidão com o código gerado
        $certidao->update(["codigo" => $idCodificado]);

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