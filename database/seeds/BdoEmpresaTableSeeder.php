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
        $empresa->segmento = "Mobiliário";
        $empresa->cnpj = "10.377.035/0001-06";
        $empresa->razaosocial = "Teste Ltda.";
        $empresa->capitalsocial = "Maior que R$ 500.000,00";
        $empresa->descricao = "Lorem ipsum dolor sit amet.";
        $empresa->endereco = "Rua Vergueiro, 256";
        $empresa->email = "teste@teste.com.br";
        $empresa->telefone = "(11) 3243-5500";
        $empresa->site = "www.teste.com.br";
        $empresa->contatonome = "Teste Silva";
        $empresa->contatotelefone = "(11) 3243-5511";
        $empresa->contatoemail = "contato@teste.com.br";
        $empresa->idusuario = 1;
        $empresa->save();
    }
}
