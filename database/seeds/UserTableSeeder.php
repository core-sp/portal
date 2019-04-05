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
        $admin = Perfil::where('nome', 'admin')->first();
        $editor = Perfil::where('nome', 'editor')->first();

        $usuario = new User();
        $usuario->nome = 'Admin';
        $usuario->email = 'desenvolvimento@core-sp.org.br';
        $usuario->password = bcrypt('Senha102030');
        $usuario->save();
        $usuario->perfil()->attach($admin);
        
        $usuario = new User();
        $usuario->nome = 'Márcio';
        $usuario->email = 'comunicacao@core-sp.org.br';
        $usuario->password = bcrypt('comunicacao102030');
        $usuario->save();
        $usuario->perfil()->attach($editor);
    }
}
