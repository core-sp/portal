<?php

namespace App;

use App\Representante;
use Illuminate\Database\Eloquent\Model;

class Certidao extends Model
{
    protected $table = "certidoes";
    protected $guarded = [];
    public $timestamps = false;

    // Tipos de certidões.
    const REGULARIDADE = 'Regularidade';
    const PARCELAMENTO = 'Parcelamento';

    /**
     * Método que retorna todos os tipos de certidões.
     */
    public static function tipos()
    {
        return [
            Certidao::REGULARIDADE,
            Certidao::PARCELAMENTO
        ];
    }

    // /**
    //  * Formata o código da Certidão para XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX.
    //  */
    // public function codigoFormatado() 
    // {
    //     if(isset($this->codigo)) {
    //         $codigoFormatado = substr($this->codigo, 0, 8);

    //         for ($i = 8; $i < strlen($this->codigo); $i = $i+8) {
    //             $codigoFormatado .= "-";

    //             $codigoFormatado .= substr($this->codigo, $i, 8);
    //         }
    //     }

    //     return $codigoFormatado;
    // }

    /**
     * Gera declaração da certidão de Regularidade (para o PDF).
     */
    public static function declaracaoRegularidade($dadosRepresentante, $numero, $codigo, $data, $hora, $dataValidade) 
    {
        setlocale(LC_TIME, 'pt_BR.utf8');
        $dataEmissao = [
            'mes' => strftime('%B', strtotime($data)),
            'ano' => strftime('%Y', strtotime($data))
        ];

        // Título da certidão
        $declaracao = '<h1 class="centro">Certidão de Reguralidade</h1>';

        switch($dadosRepresentante['tipo_pessoa']) {
            case Representante::PESSOA_FISICA:
                // Texto da certidão para PF
                $declaracao .=  '<p class="texto-certidao"><span class="tab">O <b>CORE-SP</b> certifica, atendendo ao requerimento do interessado, para fins de documentar-se, que revendo os assentamentos do Serviço de Registro, consta registrado(a), como pessoa natural, o(a) Sr(a). <b>' . $dadosRepresentante['nome'] . ',</b> sob o nº <b>' . $dadosRepresentante['registro_core'] . ',</b> desde <b>' . $dadosRepresentante['data_inscricao'] . ',</b> inscrito(a) no CPF/MF sob o nº <b>' . $dadosRepresentante['cpf_cnpj'] . ',</b> residente na <b>' . $dadosRepresentante['endereco']  . '.</b> O(A) referido(a) Representante Comercial pagou contribuições a este Conselho Regional até o mês de <b>' . $dataEmissao['mes'] . '</b> de <b>' . $dataEmissao['ano'] . '.</b></p>';
            break;

            case Representante::PESSOA_JURIDICA:
                // Texto da certidão para PJ
                $declaracao .= '<p class="texto-certidao"><span class="tab">O <b>CORE-SP</b> certifica, atendendo ao requerimento do(a) interessado(a), para fins de documentar-se, que, revendo os assentamentos do Serviço de Registro, deles consta registrada como <b>' . $dadosRepresentante['tipo_empresa'] . ' - ' . $dadosRepresentante['nome'] . ',</b> sob o nº <b>' . $dadosRepresentante['registro_core'] . ',</b> desde de <b>' . $dadosRepresentante['data_inscricao'] . ',</b> inscrita no CNPJ sob o nº <b>' . $dadosRepresentante['cpf_cnpj'] . ',</b> com sede na <b>' . $dadosRepresentante['endereco']  . '.</b></p>';
  
                if(!empty($dadosRepresentante['resp_tecnico'])) {
                    $declaracao .= 'Tendo como Responsável Técnico o(a) sr.(a) <b>' . $dadosRepresentante['resp_tecnico'] . ',</b> registrado(a) sob o número <b>' . $dadosRepresentante['resp_tecnico_registro_core'] . '.</b> '; 
                }

                $declaracao .= 'A mencionada empresa pagou contribuições a este Conselho Regional até o mês de <b>' . $dataEmissao['mes'] . '</b> de <b>' . $dataEmissao['ano'] . '.</b>';
            break;
        }

        // Rodapé da certidão
        $declaracao .=  '<p class="texto-certidao">Esta certidão possui o número <b>' . $numero . '</b>, emitida em <b>' . $data . ', </b>às<b> ' . $hora . '</b> e é válida até <b>' . $dataValidade . '</b>. Para verificar a autenticidade deste documento entre no site do CORE-SP https://www.core-sp.org.br/certidao/consulta e utilize o código abaixo:<p>';

        // código da certidão
        $declaracao .=  '<p class="centro"><b>' . $codigo . '</b><p>';

        return  $declaracao;
    }

    /**
     * Gera declaração da certidão de Parcelamento (para o PDF).
     */
    public static function declaracaoParcelamento($dadosRepresentante, $endereco, $numeroParcelas, $anosParcelas, $primeiroPagamento) 
    {
        switch($dadosRepresentante["tipo_pessoa"]) {
            case Representante::PESSOA_FISICA:
                $declaracao =  "<span class='tab'>O <b>CORE-SP</b> certifica, atendendo ao requerimento do interessado, para fins de documentar-se, que revendo os assentamentos do Serviço de Registro, consta registrado(a), como pessoa natural, o(a) Sr(a). <b>" . $dadosRepresentante["nome"] . ",</b> sob o nº <b>" . $dadosRepresentante["registro_core"] . ",</b> desde <b>" . $dadosRepresentante["data_inscricao"] . ",</b> inscrito(a) no CPF/MF sob o nº <b>" . $dadosRepresentante["cpf_cnpj"] . ",</b> residente na <b>" . $endereco . ".</b> O(A) referido(a) Representante Comercial firmou Acordo de Parcelamento referente à(s) anuidade(s) de <b>" . $dadosParcelamento["parcelamento_ano"] . ",</b> em <b>" . $dadosParcelamento["numero_parcelas"] . "</b> parcelas fixas e mensais, efetuando o primeiro pagamento em <b>" . $dadosParcelamento["data_primeiro_pagamento"] . ".</b>";
            break;

            case Representante::PESSOA_JURIDICA:
                $declaracao = "<span class='tab'>O <b>CORE-SP</b> certifica, atendendo ao requerimento do(a) interessado(a), para fins de documentar-se, que, revendo os assentamentos do Serviço de Registro, deles consta registrada como <b>" . $dadosRepresentante["tipo_empresa"] . " - " . $dadosRepresentante["nome"] . ",</b> sob o nº <b>" . $dadosRepresentante["registro_core"] . ",</b> desde de <b>" . $dadosRepresentante["data_inscricao"] . ",</b> inscrita no CNPJ sob o nº <b>" . $dadosRepresentante["cpf_cnpj"] . ",</b> com sede na <b>" . $endereco . ".</b> ";                 
  
                if(!empty($dadosRepresentante["resp_tecnico"])) {
                    $declaracao .= "Tendo como Responsável Técnico o(a) sr.(a) <b>" . $dadosRepresentante["resp_tecnico"] . ",</b> registrado(a) sob o número <b>" . $dadosRepresentante["resp_tecnico_registro_core"] . ".</b> "; 
                }

                $declaracao .= "A mencionada empresa firmou Acordo de Parcelamento referente à(s) anuidade(s) de <b>" . $anosParcelas . ",</b>  em <b>" . $numeroParcelas . "</b> parcelas fixas e mensais, efetuando o primeiro pagamento em <b>" . $primeiroPagamento . ".</b>";
            break;
        }

        return $declaracao;
    }
}
