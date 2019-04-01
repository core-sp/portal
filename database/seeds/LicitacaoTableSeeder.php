<?php

use Illuminate\Database\Seeder;
use App\Licitacao;

class LicitacaoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $licitacao = new Licitacao();
        $licitacao->modalidade = "Pregão Eletrônico";
        $licitacao->edital = "/arquivos/PREGAO_002_2019.pdf";
        $licitacao->nrlicitacao = "002/2019";
        $licitacao->nrprocesso = "27/19";
        $licitacao->situacao = "Aberto";
        $licitacao->titulo = "PREGÃO ELETRÔNICO - Contratação de serviços";
        $licitacao->datarealizacao = "2019-02-12 14:00:00";
        $licitacao->objeto = "<p>O objeto da presente licitação é a escolha da proposta mais vantajosa para a contratação de serviços de empresa especializada em produção, impressão e entrega (postagem) de notificações e boletos de cobrança bancária, homologados por banco registrado juntos ao CORE-SP, referentes às anuidades das pessoas físicas e jurídicas registradas neste Conselho, conforme condições, quantidades e exigências estabelecidas neste Edital e seus anexos.</p>";
        $licitacao->idusuario = 1;
        $licitacao->save();

        $licitacao = new Licitacao();
        $licitacao->modalidade = "Pregão Eletrônico";
        $licitacao->edital = "/arquivos/PREGAO_001_2019.pdf";
        $licitacao->nrlicitacao = "001/2019";
        $licitacao->nrprocesso = "115/18";
        $licitacao->situacao = "Aberto";
        $licitacao->titulo = "PREGÃO ELETRÔNICO - Contratação de prestação de serviços";
        $licitacao->datarealizacao = "2019-01-18 14:00:00";
        $licitacao->objeto = "<p>Contratação de empresa para prestação de serviços de implementação, gerenciamento e administração de Auxílios Refeição, via cartão cartão eletrônico com senha numérica individual e CHIP de segurança para validação das transações e respectivas recargas mensais de crédito para o Conselho Regional dos Representantes Comerciais no Estado de São Paulo - CORE-SP, com sede em São Paulo/SP e demais Unidades Descentralizadas.</p>";
        $licitacao->idusuario = 1;
        $licitacao->save();
    }
}
