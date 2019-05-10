<?php

use Illuminate\Database\Seeder;
use App\Noticia;

class NoticiaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $noticia = new Noticia();
        $noticia->titulo = "NO TRÂNSITO, O SENTIDO É A VIDA! CORE-SP APOIA ESSA IDEIA!";
        $noticia->slug = "no-transito-o-sentido-e-a-vida-core-sp-apoia-essa-ideia";
        $noticia->img = "/imagens/2019-05/maio-amarelo-horizontal-baixa-264-800x560m1.jpg";
        $noticia->conteudo = "<p style='text-align: justify;'>NO TRÂNSITO, O SENTIDO É A VIDA foi o tema escolhido para a sexta edição do Movimento Maio Amarelo, que propõe o envolvimento direto da sociedade nas ações e uma reflexão sobre uma nova forma de encarar a mobilidade. Trata-se de um estímulo a todos os condutores, seja de caminhões, ônibus, vans, automóveis, motocicletas ou bicicletas, e aos pedestres e passageiros, a optarem por um trânsito mais seguro.
        O objetivo do movimento é uma ação coordenada entre o Poder Público e a sociedade civil. A intenção é colocar em pauta o tema segurança viária e mobilizar toda a sociedade, envolvendo os mais diversos segmentos: órgãos de governos, empresas, entidades de classe, associações, federações e sociedade civil organizada para, fugindo das falácias cotidianas e costumeiras, efetivamente discutir o tema, engajar-se em ações e propagar o conhecimento, abordando toda a amplitude que a questão do trânsito exige, nas mais diferentes esferas.
        <strong>O QUE É?</strong>    
        É um movimento internacional de conscientização para redução de acidentes de trânsito. O trânsito deve ser seguro para todos em qualquer situação.
        <strong>OBJETIVO</strong>
        Colocar em pauta, para a sociedade, o tema trânsito. Estimular a participação da população, empresas, governos e entidades.
        <strong>POR QUE MAIO?</strong>
        Em 11 de maio de 2011, a ONU decretou a Década de Ação para Segurança no Trânsito. Com isso, o mês de maio se tornou referência mundial para balanço das ações que o mundo inteiro realiza.
        <strong>POR QUE AMARELO?</strong>
        O amarelo simboliza atenção e também a sinalização e advertência no trânsito. 
        Mais informações: www.maioamarelo.com</p>";
        $noticia->publicada = "Sim";
        $noticia->idusuario = 5;
        $noticia->save();
    }
}
