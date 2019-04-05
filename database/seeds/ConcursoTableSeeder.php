<?php

use Illuminate\Database\Seeder;
use App\Concurso;

class ConcursoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $concurso = new Concurso();
        $concurso->modalidade = "Concurso Público";
        $concurso->titulo = "Concurso Público do CORE-SP";
        $concurso->nrprocesso = "001/2018";
        $concurso->situacao = "Aberto";
        $concurso->datarealizacao = "2019-04-04 14:00:00";
        $concurso->objeto = "Informamos que  o Procedimento de Heteroidentificação dos Candidatos Aprovados e Classificados ao Cadastro de Reserva e deferidos nas vagas reservadas aos negros do Concurso Publico foi alterado de acordo com a Retificação nº 04, do dia 30/04/2019 (terça-feira), para o dia 05/05/2019 (domingo), sem alteração das demais datas do cronograma.";
        $concurso->linkexterno = "http://paconcursos.com.br/v1/produto/core-sp-3/#1457971168097-8ff24330-c0a3";
        $concurso->idusuario = 1;
        $concurso->save();
    }
}
