<?php

use Illuminate\Database\Seeder;
use App\Perfil;

class PerfilTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $perfil = new Perfil();
        $perfil->nome = "Admin";
        $perfil->descricao = "Usuário administrador";
        $perfil->save();
    }
}
