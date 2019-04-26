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
        $usuario = new User();
        $usuario->nome = 'Admin';
        $usuario->email = 'desenvolvimento@core-sp.org.br';
        $usuario->perfil = 'Admin';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('Senha102030');
        $usuario->save();
        
        $usuario = new User();
        $usuario->nome = 'Márcio';
        $usuario->email = 'comunicacao@core-sp.org.br';
        $usuario->perfil = 'Editor';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('comunicacao102030');
        $usuario->save();
        
        $usuario = new User();
        $usuario->nome = 'Fraelli Brito';
        $usuario->email = 'fraelli@core-sp.org.br';
        $usuario->perfil = 'Atendimento';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('fraelli102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Luciana';
        $usuario->email = 'luciana@core-sp.org.br';
        $usuario->perfil = 'Gestão de Atendimento';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('luciana102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Edson';
        $usuario->email = 'edson@core-sp.org.br';
        $usuario->perfil = 'Admin';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('edson102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Ricardo';
        $usuario->email = 'ricardo.tejada@core-sp.org.br';
        $usuario->perfil = 'Admin';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('ricardo102030');
        $usuario->save();
    }
}
