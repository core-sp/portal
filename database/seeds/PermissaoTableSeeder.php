<?php

use Illuminate\Database\Seeder;
use App\Permissao;

class PermissaoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissao = new Permissao();
        $permissao->controller = 'UserController';
        $permissao->metodo = 'index';
        $permissao->perfis = '1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'UserController';
        $permissao->metodo = 'create';
        $permissao->perfis = '1';
        $permissao->save();
    }
}
