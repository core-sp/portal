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
        $curso->datarealizacao = "2019-05-18 12:00:00";
        $curso->datatermino = "2019-05-18 14:00:00";
        $curso->duracao = 2;
        $curso->cargahoraria = "Segunda-feira: 12h às 14hrs";
        $curso->endereco = "Rua Vergueiro, 256";
        $curso->nrvagas = 5;
        $curso->descricao = "<p>Saiba como aplicar t&eacute;cnicas de marketing por meio da an&aacute;lise do ambiente mercadol&oacute;gico e do desenvolvimento do composto de marketing.</p>";
        $curso->idregional = 7;
        $curso->idusuario = 1;
        $curso->save();

        $curso = new Curso();
        $curso->tipo = "Palestra";
        $curso->tema = "Técnica em Vendas";
        $curso->datarealizacao = "2019-05-20 12:00:00";
        $curso->datatermino = "2019-05-20 16:00:00";
        $curso->duracao = 4;
        $curso->cargahoraria = "Segunda-feira: 12h às 16hrs";
        $curso->endereco = "Avenida Paulista, 120";
        $curso->nrvagas = 10;
        $curso->descricao = "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tincidunt et justo non hendrerit. Nulla venenatis vestibulum arcu.</p>";
        $curso->idregional = 12;
        $curso->idusuario = 1;
        $curso->save();

        $curso = new Curso();
        $curso->tipo = "Workshop";
        $curso->tema = "Marketing Digital";
        $curso->datarealizacao = "2019-05-21 14:00:00";
        $curso->datatermino = "2019-05-21 17:00:00";
        $curso->duracao = 3;
        $curso->cargahoraria = "Segunda-feira: 12h às 15hrs";
        $curso->endereco = "Avenida Brigadeiro Luís Antônio, 613";
        $curso->nrvagas = 20;
        $curso->descricao = "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ex leo, rutrum eu odio ut, posuere dapibus nunc. Nunc dapibus tellus a elit cursus auctor.</p>";
        $curso->idregional = 8;
        $curso->idusuario = 1;
        $curso->save();
    }
}
