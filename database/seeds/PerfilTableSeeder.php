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
        $perfil->nome = 'Admin';
        $perfil->save();

        $perfil = new Perfil();
        $perfil->nome = 'Procuradoria';
        $perfil->save();

        $perfil = new Perfil();
        $perfil->nome = 'Editor';
        $perfil->save();

        $perfil = new Perfil();
        $perfil->nome = 'Jurídico';
        $perfil->save();

        $perfil = new Perfil();
        $perfil->nome = 'Transparência';
        $perfil->save();

        $perfil = new Perfil();
        $perfil->nome = 'Coordenadoria de Atendimento';
        $perfil->save();

        $perfil = new Perfil();
        $perfil->nome = 'Gestão de Atendimento';
        $perfil->save();

        $perfil = new Perfil();
        $perfil->nome = 'Atendimento';
        $perfil->save();
    }
}
