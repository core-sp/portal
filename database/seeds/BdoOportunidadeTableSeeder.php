<?php

use Illuminate\Database\Seeder;
use App\BdoOportunidade;

class BdoOportunidadeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $oportunidade = new BdoOportunidade();
        $oportunidade->idempresa = 1;
        $oportunidade->titulo = "REPRESENTANTE COMERCIAL";
        $oportunidade->segmento = "Automobilística";
        $oportunidade->regiaoatuacao = "5,10,11";
        $oportunidade->descricao = " Representante Comercial

        * Experiência em vendas ( automotivo, industrial ou moto peças);
        * 2º grau completo;
        * Veículo próprio.
        
        Interessados enviar currículo para: rhvendas@unifort.com.br ";
        $oportunidade->vagasdisponiveis = 5;
        $oportunidade->status = "Em andamento";
        $oportunidade->datainicio = "2019-05-06";
        $oportunidade->idusuario = 31;
        $oportunidade->save();
    }
}
