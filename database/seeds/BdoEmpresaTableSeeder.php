<?php

use Illuminate\Database\Seeder;
use App\BdoEmpresa;

class BdoEmpresaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $empresa = new BdoEmpresa();
        $empresa->segmento = "Automobilística";
        $empresa->cnpj = "41.916.347/0001-66";
        $empresa->razaosocial = "UNIFORT LTDA";
        $empresa->capitalsocial = "Maior que R$ 500.000,00";
        $empresa->descricao = " A UNIFORT é uma empresa com mais de 31 anos no mercado, atuante no segmento de distribuição de produtos para linha automotiva, linha pesada, moto peças e industrial com produtos da mais alta tecnologia e qualidade.";
        $empresa->endereco = "Avenida Amazonas, 4333 - Cachoeira - Betim - MG - CEP: 32602-505";
        $empresa->email = "rhvendas@unifort.com.br";
        $empresa->telefone = "(31) 2191-5500";
        $empresa->site = "www.unifort.com.br";
        $empresa->contatonome = "Ana Paula Chaves";
        $empresa->contatotelefone = "(31) 2191-5521";
        $empresa->contatoemail = "ana.chaves@unifort.com.br";
        $empresa->idusuario = 31;
        $empresa->save();
    }
}
