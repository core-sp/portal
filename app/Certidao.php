<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certidao extends Model
{
    protected $table = "certidoes";
    protected $guarded = [];
    public $timestamps = false;

    static $tipo_regularidade = 'Regularidade';
    static $tipo_parcelamento = 'Parcelamento';

    /**
     * Formata o código da Certidão para XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX
     */
    public function codigoFormatado() 
    {
        if(isset($this->codigo)) {
            $codigoFormatado = substr($this->codigo, 0, 8);

            for ($i = 8; $i < strlen($this->codigo); $i = $i+8) {
                $codigoFormatado .= "-";

                $codigoFormatado .= substr($this->codigo, $i, 8);
            }
        }

        return $codigoFormatado;
    }

    /**
     * Gera declaração da certidão de Regularidade (para o PDF e para salvar no banco)
     */
    public static function declaracaoRegularidade($html, $tipoPessoa, $dadosRepresentante, $endereco, $data_emissao) 
    {
        setlocale(LC_TIME, "pt_BR.utf8");
        $data = [
            "mes" => strftime("%B", strtotime($data_emissao)),
            "ano" => strftime("%Y",  strtotime($data_emissao))
        ];

        switch($tipoPessoa) {
            case "PF":
                if($html) {
                    $declaracao =  "<span class='tab'>O <b>CORE-SP</b> certifica, atendendo ao requerimento do interessado, para fins de documentar-se, que revendo os assentamentos do Serviço de Registro, consta registrado(a), como pessoa natural, o(a) Sr(a). <b>" . $dadosRepresentante["nome"] . ",</b> sob o nº <b>" . $dadosRepresentante["registro_core"] . ",</b> desde <b>" . $dadosRepresentante["data_inscricao"] . ",</b> inscrito(a) no CPF/MF sob o nº <b>" . $dadosRepresentante["cpf_cnpj"] . ",</b> residente na <b>" . $endereco . ".</b> O(A) referido(a) Representante Comercial pagou contribuições a este Conselho Regional até o mês de <b>" . $data["mes"] . "</b> de <b>" . $data['ano'] . ".</b>";
                }
                else {
                    $declaracao = "O CORE-SP certifica, atendendo ao requerimento do interessado, para fins de documentar-se, que revendo os assentamentos do Serviço de Registro, consta registrado(a), como pessoa natural, o(a) Sr(a). " . $dadosRepresentante["nome"] . ", sob o nº " . $dadosRepresentante["registro_core"] . ", desde " . $dadosRepresentante["data_inscricao"] . ", inscrito(a) no CPF/MF sob o nº " . $dadosRepresentante["cpf_cnpj"] . ", residente na " . $endereco . ". O(A) referido(a) Representante Comercial pagou contribuições a este Conselho Regional até o mês de " . $data["mes"] . " de " . $data['ano'] . "."; 
                } 
            break;

            case "PJ":
                if($html) {
                    $declaracao = "<span class='tab'>O <b>CORE-SP</b> certifica, atendendo ao requerimento do(a) interessado(a), para fins de documentar-se, que, revendo os assentamentos do Serviço de Registro, deles consta registrada como <b>" . $dadosRepresentante["tipo_empresa"] . " - " . $dadosRepresentante["nome"] . ",</b> sob o nº <b>" . $dadosRepresentante["registro_core"] . ",</b> desde de <b>" . $dadosRepresentante["data_inscricao"] . ",</b> inscrita no CNPJ sob o nº <b>" . $dadosRepresentante["cpf_cnpj"] . ",</b> com sede na <b>" . $endereco  . ".</b> ";
  
                    if(!empty($dadosRepresentante["resp_tecnico"])) {
                        $declaracao .= "Tendo como Responsável Técnico o(a) sr.(a) <b>" . $dadosRepresentante["resp_tecnico"] . ",</b> registrado(a) sob o número <b>" . $dadosRepresentante["resp_tecnico_registro_core"] . ".</b> "; 
                    }

                    $declaracao .= "A mencionada empresa pagou contribuições a este Conselho Regional até o mês de <b>" . $data["mes"] . "</b> de <b>" . $data["ano"] . ".</b>";
                }
                else {
                    $declaracao = "O CORE-SP certifica, atendendo ao requerimento do(a) interessado(a), para fins de documentar-se, que, revendo os assentamentos do Serviço de Registro, deles consta registrada como " . $dadosRepresentante["tipo_empresa"] . " - " . $dadosRepresentante["nome"] . ", sob o nº " . $dadosRepresentante["registro_core"] . ", desde de " . $dadosRepresentante["data_inscricao"] . ", inscrita no CNPJ sob o nº " . $dadosRepresentante["cpf_cnpj"] . ", com sede na " . $endereco  . ". ";
  
                    if(!empty($dadosRepresentante["resp_tecnico"])) {
                        $declaracao .= "Tendo como Responsável Técnico o(a) sr.(a) " . $dadosRepresentante["resp_tecnico"] . ", registrado(a) sob o número " . $dadosRepresentante["resp_tecnico_registro_core"] . ". "; 
                    }

                    $declaracao .= "A mencionada empresa pagou contribuições a este Conselho Regional até o mês de " . $data["mes"] . " de " . $data["ano"] . ".";      
                } 
            break;
        }

        return  $declaracao;
    }

    /**
     * Gera declaração da certidão de Parcelamento (para o PDF e para salvar no banco)
     */
    public static function declaracaoParcelamento($html, $tipoPessoa, $dadosRepresentante, $endereco, $dadosParcelamento) 
    {
        switch($tipoPessoa) {
            case "PF":
                if($html) {
                    $declaracao =  "<span class='tab'>O <b>CORE-SP</b> certifica, atendendo ao requerimento do interessado, para fins de documentar-se, que revendo os assentamentos do Serviço de Registro, consta registrado(a), como pessoa natural, o(a) Sr(a). <b>" . $dadosRepresentante["nome"] . ",</b> sob o nº <b>" . $dadosRepresentante["registro_core"] . ",</b> desde <b>" . $dadosRepresentante["data_inscricao"] . ",</b> inscrito(a) no CPF/MF sob o nº <b>" . $dadosRepresentante["cpf_cnpj"] . ",</b> residente na <b>" . $endereco . ".</b> O(A) referido(a) Representante Comercial firmou Acordo de Parcelamento referente à(s) anuidade(s) de <b>" . $dadosParcelamento["parcelamento_ano"] . ",</b> em <b>" . $dadosParcelamento["numero_parcelas"] . "</b> parcelas fixas e mensais, efetuando o primeiro pagamento em <b>" . $dadosParcelamento["data_primeiro_pagamento"] . ".</b>";
                }
                else {
                    $declaracao = "O CORE-SP certifica, atendendo ao requerimento do interessado, para fins de documentar-se, que revendo os assentamentos do Serviço de Registro, consta registrado(a), como pessoa natural, o(a) Sr(a). " . $dadosRepresentante["nome"] . ", sob o nº " . $dadosRepresentante["registro_core"] . ", desde " . $dadosRepresentante["data_inscricao"] . ", inscrito(a) no CPF/MF sob o nº " . $dadosRepresentante["cpf_cnpj"] . ", residente na " . $endereco . ". O(A) referido(a) Representante Comercial firmou Acordo de Parcelamento referente à(s) anuidade(s) de " . $dadosParcelamento["parcelamento_ano"] . ", em " . $dadosParcelamento["numero_parcelas"] . " parcelas fixas e mensais, efetuando o primeiro pagamento em " . $dadosParcelamento["data_primeiro_pagamento"] . ".";
                } 
            break;

            case "PJ":
                if($html) {
                    $declaracao = "<span class='tab'>O <b>CORE-SP</b> certifica, atendendo ao requerimento do(a) interessado(a), para fins de documentar-se, que, revendo os assentamentos do Serviço de Registro, deles consta registrada como <b>" . $dadosRepresentante["tipo_empresa"] . " - " . $dadosRepresentante["nome"] . ",</b> sob o nº <b>" . $dadosRepresentante["registro_core"] . ",</b> desde de <b>" . $dadosRepresentante["data_inscricao"] . ",</b> inscrita no CNPJ sob o nº <b>" . $dadosRepresentante["cpf_cnpj"] . ",</b> com sede na <b>" . $endereco . ".</b> ";                 
  
                    if(!empty($dadosRepresentante["resp_tecnico"])) {
                        $declaracao .= "Tendo como Responsável Técnico o(a) sr.(a) <b>" . $dadosRepresentante["resp_tecnico"] . ",</b> registrado(a) sob o número <b>" . $dadosRepresentante["resp_tecnico_registro_core"] . ".</b> "; 
                    }

                    $declaracao .= "A mencionada empresa firmou Acordo de Parcelamento referente à(s) anuidade(s) de <b>" . $dadosParcelamento["parcelamento_ano"] . ",</b>  em <b>" . $dadosParcelamento["numero_parcelas"] . "</b> parcelas fixas e mensais, efetuando o primeiro pagamento em <b>" . $dadosParcelamento["data_primeiro_pagamento"] . ".</b>";
                }
                else {
                    $declaracao = "O CORE-SP certifica, atendendo ao requerimento do(a) interessado(a), para fins de documentar-se, que, revendo os assentamentos do Serviço de Registro, deles consta registrada como " . $dadosRepresentante["tipo_empresa"] . " - " . $dadosRepresentante["nome"] . ", sob o nº " . $dadosRepresentante["registro_core"] . ", desde de " . $dadosRepresentante["data_inscricao"] . ", inscrita no CNPJ sob o nº " . $dadosRepresentante["cpf_cnpj"] . ", com sede na " . $endereco . ". ";                 
  
                    if(!empty($dadosRepresentante["resp_tecnico"])) {
                        $declaracao .= "Tendo como Responsável Técnico o(a) sr.(a) " . $dadosRepresentante["resp_tecnico"] . ", registrado(a) sob o número " . $dadosRepresentante["resp_tecnico_registro_core"] . ". "; 
                    }

                    $declaracao .= "A mencionada empresa firmou Acordo de Parcelamento referente à(s) anuidade(s) de " . $dadosParcelamento["parcelamento_ano"] . ",  em " . $dadosParcelamento["numero_parcelas"] . " parcelas fixas e mensais, efetuando o primeiro pagamento em " . $dadosParcelamento["data_primeiro_pagamento"] . ".";
                } 
            break;
        }
    }
}
