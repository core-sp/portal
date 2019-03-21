<?php

use Illuminate\Database\Seeder;
use App\Perfil;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $perfil = Perfil::where('nome', 'admin')->first();

        $usuario = new User();
        $usuario->nome = 'Admin';
        $usuario->email = 'admin@admin.com.br';
        $usuario->password = bcrypt('admin102030');
        $usuario->save();

        $usuario->perfil()->attach($perfil);
    }
}
