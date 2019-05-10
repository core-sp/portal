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
        $licitacao->modalidade = "Pregão Eletrônico SRP";
        $licitacao->uasg = "926753";
        $licitacao->edital = "/arquivos/PREGAO_002_2019.pdf";
        $licitacao->nrlicitacao = "02/2019";
        $licitacao->nrprocesso = "027/19";
        $licitacao->situacao = "Homologado";
        $licitacao->titulo = "Produção, impressão e entrega (postagem) de notificações e boletos de cobrança";
        $licitacao->datarealizacao = "2019-02-22 10:00:00";
        $licitacao->objeto = "<p>O objeto da presente licita&ccedil;&atilde;o &eacute; a escolha da proposta mais vantajosa para a contrata&ccedil;&atilde;o de servi&ccedil;os de empresa especializada em produ&ccedil;&atilde;o, impress&atilde;o e entrega (postagem) de notifica&ccedil;&otilde;es e boletos de cobran&ccedil;a banc&aacute;ria, homologados por banco registrado juntos ao CORE-SP, referentes &agrave;s anuidades das pessoas f&iacute;sicas e jur&iacute;dicas registradas neste Conselho, conforme condi&ccedil;&otilde;es, quantidades e exig&ecirc;ncias estabelecidas neste Edital e seus anexos.</p>";
        $licitacao->idusuario = 32;
        $licitacao->save();

        $licitacao = new Licitacao();
        $licitacao->modalidade = "Pregão Eletrônico Tradicional";
        $licitacao->uasg = "926753";
        $licitacao->edital = "/arquivos/PREGAO_001_2019.pdf";
        $licitacao->nrlicitacao = "01/2019";
        $licitacao->nrprocesso = "115/18";
        $licitacao->situacao = "Homologado";
        $licitacao->titulo = "Vale Refeição";
        $licitacao->datarealizacao = "2019-01-16 10:00:00";
        $licitacao->objeto = "<p>Contrata&ccedil;&atilde;o de empresa para presta&ccedil;&atilde;o de servi&ccedil;os de implementa&ccedil;&atilde;o, gerenciamento e administra&ccedil;&atilde;o de Aux&iacute;lios Refei&ccedil;&atilde;o, via cart&atilde;o cart&atilde;o eletr&ocirc;nico com senha num&eacute;rica individual e CHIP de seguran&ccedil;a para valida&ccedil;&atilde;o das transa&ccedil;&otilde;es e respectivas recargas mensais de cr&eacute;dito para o Conselho Regional dos Representantes Comerciais no Estado de S&atilde;o Paulo - <strong>CORE-SP</strong>, com sede em S&atilde;o Paulo/SP e demais Unidades Descentralizadas.</p>";
        $licitacao->idusuario = 32;
        $licitacao->save();

        $licitacao = new Licitacao();
        $licitacao->modalidade = "Pregão Eletrônico SRP";
        $licitacao->uasg = "926753";
        $licitacao->edital = "/arquivos/2019-05/edital_de_pregao_eletronico_- meios de pagamento.pdf";
        $licitacao->nrlicitacao = "03/2019";
        $licitacao->nrprocesso = "031/2019";
        $licitacao->situacao = "Homologado";
        $licitacao->titulo = "Meios de pagamento";
        $licitacao->datarealizacao = "2019-04-30 10:00:00";
        $licitacao->objeto = '<p style="text-align: justify;">Contrata&ccedil;&atilde;o de empresa para presta&ccedil;&atilde;o de servi&ccedil;os de meios de pagamento. Transfer&ecirc;ncia eletr&ocirc;nica de fundos, concilia&ccedil;&atilde;o, captura, transmiss&atilde;o, processamento e liquida&ccedil;&atilde;o de transa&ccedil;&otilde;es eletr&ocirc;nicas e manuais com cart&otilde;es de debito e credito, com aceita&ccedil;&atilde;o m&iacute;nima das bandeiras VISA, VISA ELECTRON, MASTERCARD, MASTERCARD MAESTRO E REDESHOP, nos receb&iacute;veis oriundos das anuidades e multas devidas pelos respectivos profissionais vinculados ao Core-SP, com fornecimento de terminais fixos de captura de transa&ccedil;&otilde;es, com plataforma informatizada que disponibilize sistema de API para que seja integrada ao <em>sistema Gerenti</em><strong>,</strong> conforme condi&ccedil;&otilde;es, quantidades e exig&ecirc;ncias estabelecidas neste Edital e seus anexos.</p>
        <p style="text-align: justify;"><a title="Publica&ccedil;&atilde;o no Di&aacute;rio Oficial da Uni&atilde;o" href="/arquivos/2019-05/Preg&atilde;o eletr&ocirc;nico 032019/DOU.pdf">Publica&ccedil;&atilde;o no Di&aacute;rio Oficial da Uni&atilde;o</a></p>
        <p><a title="Anexo I - Termo de Refer&ecirc;ncia" href="/arquivos/2019-05/Preg&atilde;o eletr&ocirc;nico 032019/anexo I - Termo de referencia cartao de credito - 08abril2019.pdf">Anexo I - Termo de Refer&ecirc;ncia</a></p>
        <p><a title="Anexo II - Minuta da Ata de Registro de Pre&ccedil;os" href="/arquivos/2019-05/Preg&atilde;o eletr&ocirc;nico 032019/anexo II - minuta da ata_de_registro_de_precos_.pdf">Anexo II - Minuta da Ata de Registro de Pre&ccedil;os</a></p>
        <p><a title="Anexo III - Minuta do Termo de Contrato" href="/arquivos/2019-05/Preg&atilde;o eletr&ocirc;nico 032019/anexo III - minuta do termo de contrato.pdf">Anexo III - Minuta do Termo de Contrato</a></p>
        <p><a title="Anexo IV - Estudos Preliminares" href="/arquivos/2019-05/Preg&atilde;o eletr&ocirc;nico 032019/anexo IV - estudos preliminares.pdf">Anexo IV - Estudos Preliminares</a></p>
        <p>&nbsp;</p>';
        $licitacao->idusuario = 32;
        $licitacao->save();
    }
}
