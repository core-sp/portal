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
        $admin = Perfil::where('nome', 'Admin')->first();
        $editor = Perfil::where('nome', 'Editor')->first();
        $atendimento = Perfil::where('nome', 'Atendimento')->first();
        $gestaoDeAtendimento = Perfil::where('nome', 'Gestão de Atendimento')->first();

        $usuario = new User();
        $usuario->nome = 'Admin';
        $usuario->email = 'desenvolvimento@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('Senha102030');
        $usuario->save();
        $usuario->perfil()->attach($admin);
        
        $usuario = new User();
        $usuario->nome = 'Márcio';
        $usuario->email = 'comunicacao@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('comunicacao102030');
        $usuario->save();
        $usuario->perfil()->attach($editor);
        
        $usuario = new User();
        $usuario->nome = 'Fraelli Brito';
        $usuario->email = 'fraelli@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('fraelli102030');
        $usuario->save();
        $usuario->perfil()->attach($atendimento);

        $usuario = new User();
        $usuario->nome = 'Luciana';
        $usuario->email = 'luciana@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('luciana102030');
        $usuario->save();
        $usuario->perfil()->attach($gestaoDeAtendimento);

        $usuario = new User();
        $usuario->nome = 'Edson';
        $usuario->email = 'edson@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('edson102030');
        $usuario->save();
        $usuario->perfil()->attach($admin);

        $usuario = new User();
        $usuario->nome = 'Ricardo';
        $usuario->email = 'ricardo.tejada@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('ricardo102030');
        $usuario->save();
        $usuario->perfil()->attach($admin);
    }
}
