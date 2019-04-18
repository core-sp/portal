<?php

use Illuminate\Database\Seeder;
use App\Curso;

class CursoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $curso = new Curso();
        $curso->tipo = "Curso";
        $curso->tema = "Básico em Marketing";
        $curso->img = "/imagens/curso-01.png";
        $curso->datarealizacao = "2019-04-01 12:00:00";
        $curso->datatermino = "2019-04-01 14:00:00";
        $curso->endereco = "Rua Vergueiro, 256";
        $curso->nrvagas = 2;
        $curso->descricao = "<h4>Objetivo</h4>
        <p>Capacitar o profissional que ingressa ou pretende ingressar na &aacute;rea a aplicar t&eacute;cnicas de marketing por meio da an&aacute;lise do ambiente mercadol&oacute;gico e do desenvolvimento do composto de marketing (produto, canais de disrtibui&ccedil;&atilde;o, pre&ccedil;o e comunica&ccedil;&atilde;o), contribuindo nas decis&otilde;es que envolvam o cicle do vida de produtos e servi&ccedil;os no mercado competitico atual.</p>
        <p><strong>Carga hor&aacute;ria: 16 horas</strong></p>
        <p><strong>Pr&eacute;-requisito:&nbsp;</strong>Escolaridade m&iacute;nima - cursando o ensino m&eacute;dio.</p>
        <p>A participa&ccedil;&atilde;o nas atividades previstas requer que o participante demonstre forte interesse pelas &aacute;reas comerciais das organiza&ccedil;&oacute;es e/ou relacionamento entre seus p&uacute;blico-alvo.</p>
        <p>Al&eacute;m disso, pressup&otilde;e-se que dever&aacute; ter racioc&iacute;nio l&oacute;gico e abstrato, externado pela express&atilde;o oral, escrita pr&oacute;pria, leitura de textos e capacidade para elaborar equa&ccedil;&otilde;es matem&aacute;ticas elementares.</p>";
        $curso->resumo = "Saiba como aplicar t&eacute;cnicas de marketing por meio da an&aacute;lise do ambiente mercadol&oacute;gico e do desenvolvimento do composto de marketing.";
        $curso->publicado = 'Sim';
        $curso->idregional = 7;
        $curso->idusuario = 1;
        $curso->save();

        $curso = new Curso();
        $curso->tipo = "Palestra";
        $curso->tema = "Técnica em Vendas";
        $curso->img = "/imagens/curso-02.png";
        $curso->datarealizacao = "2019-05-20 12:00:00";
        $curso->datatermino = "2019-05-20 16:00:00";
        $curso->endereco = "Avenida Paulista, 120";
        $curso->nrvagas = 2;
        $curso->resumo = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tincidunt et justo non hendrerit. Nulla venenatis vestibulum arcu consectetur adipiscing elit.";
        $curso->descricao = "<h4>Objetivo</h4>
        <p>Capacitar o profissional que ingressa ou pretende ingressar na &aacute;rea a aplicar t&eacute;cnicas de marketing por meio da an&aacute;lise do ambiente mercadol&oacute;gico e do desenvolvimento do composto de marketing (produto, canais de disrtibui&ccedil;&atilde;o, pre&ccedil;o e comunica&ccedil;&atilde;o), contribuindo nas decis&otilde;es que envolvam o cicle do vida de produtos e servi&ccedil;os no mercado competitico atual.</p>
        <p><strong>Carga hor&aacute;ria: 16 horas</strong></p>
        <p><strong>Pr&eacute;-requisito:&nbsp;</strong>Escolaridade m&iacute;nima - cursando o ensino m&eacute;dio.</p>
        <p>A participa&ccedil;&atilde;o nas atividades previstas requer que o participante demonstre forte interesse pelas &aacute;reas comerciais das organiza&ccedil;&oacute;es e/ou relacionamento entre seus p&uacute;blico-alvo.</p>
        <p>Al&eacute;m disso, pressup&otilde;e-se que dever&aacute; ter racioc&iacute;nio l&oacute;gico e abstrato, externado pela express&atilde;o oral, escrita pr&oacute;pria, leitura de textos e capacidade para elaborar equa&ccedil;&otilde;es matem&aacute;ticas elementares.</p>";
        $curso->publicado = 'Sim';
        $curso->idregional = 12;
        $curso->idusuario = 1;
        $curso->save();

        $curso = new Curso();
        $curso->tipo = "Workshop";
        $curso->tema = "Marketing Digital";
        $curso->img = "/imagens/curso-03.png";
        $curso->datarealizacao = "2019-05-21 14:00:00";
        $curso->datatermino = "2019-05-21 17:00:00";
        $curso->endereco = "Avenida Brigadeiro Luís Antônio, 613";
        $curso->nrvagas = 20;
        $curso->resumo = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ex leo, rutrum eu odio ut, posuere dapibus nunc. Nunc dapibus tellus a elit cursus auctor.";
        $curso->descricao = "<h4>Objetivo</h4>
        <p>Capacitar o profissional que ingressa ou pretende ingressar na &aacute;rea a aplicar t&eacute;cnicas de marketing por meio da an&aacute;lise do ambiente mercadol&oacute;gico e do desenvolvimento do composto de marketing (produto, canais de disrtibui&ccedil;&atilde;o, pre&ccedil;o e comunica&ccedil;&atilde;o), contribuindo nas decis&otilde;es que envolvam o cicle do vida de produtos e servi&ccedil;os no mercado competitico atual.</p>
        <p><strong>Carga hor&aacute;ria: 16 horas</strong></p>
        <p><strong>Pr&eacute;-requisito:&nbsp;</strong>Escolaridade m&iacute;nima - cursando o ensino m&eacute;dio.</p>
        <p>A participa&ccedil;&atilde;o nas atividades previstas requer que o participante demonstre forte interesse pelas &aacute;reas comerciais das organiza&ccedil;&oacute;es e/ou relacionamento entre seus p&uacute;blico-alvo.</p>
        <p>Al&eacute;m disso, pressup&otilde;e-se que dever&aacute; ter racioc&iacute;nio l&oacute;gico e abstrato, externado pela express&atilde;o oral, escrita pr&oacute;pria, leitura de textos e capacidade para elaborar equa&ccedil;&otilde;es matem&aacute;ticas elementares.</p>";
        $curso->publicado = 'Sim';
        $curso->idregional = 8;
        $curso->idusuario = 1;
        $curso->save();
    }
}
