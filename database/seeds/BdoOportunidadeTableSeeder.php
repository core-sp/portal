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
        $oportunidade->titulo = "Procura-se Representante Comercial do ramo mobiliário";
        $oportunidade->segmento = "Mobiliário";
        $oportunidade->regiaoatuacao = "Capital";
        $oportunidade->descricao = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vitae felis in ipsum euismod posuere. Nulla ultricies tortor sem, vel luctus libero vehicula eu. Etiam ut placerat sem. Vestibulum sit amet dui quis ligula rutrum semper ac eget felis. Donec ut aliquet massa, sed congue metus. Quisque ac ornare purus, ac mollis neque. Nam elementum massa vel nisi mollis efficitur.";
        $oportunidade->vagasdisponiveis = 5;
        $oportunidade->status = "Em andamento";
        $oportunidade->datainicio = "2019-04-01";
        $oportunidade->idusuario = 1;
        $oportunidade->save();
    }
}
