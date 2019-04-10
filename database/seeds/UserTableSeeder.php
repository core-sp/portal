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
        $usuario->nome = 'Cícero';
        $usuario->email = 'cicero@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('cicero102030');
        $usuario->save();
        $usuario->perfil()->attach($atendimento);

        $usuario = new User();
        $usuario->nome = 'Karol';
        $usuario->email = 'karol@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('karol102030');
        $usuario->save();
        $usuario->perfil()->attach($atendimento);

        $usuario = new User();
        $usuario->nome = 'Emanuele';
        $usuario->email = 'emanuele@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('emanuele102030');
        $usuario->save();
        $usuario->perfil()->attach($atendimento);

        $usuario = new User();
        $usuario->nome = 'Fraeli';
        $usuario->email = 'fraeli@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->password = bcrypt('fraeli102030');
        $usuario->save();
        $usuario->perfil()->attach($atendimento);

        $usuario = new User();
        $usuario->nome = 'Wendel';
        $usuario->email = 'wendel@core-sp.org.br';
        $usuario->idregional = 4;
        $usuario->password = bcrypt('wendel102030');
        $usuario->save();
        $usuario->perfil()->attach($atendimento);
    }
}
