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
        $usuario->nome = 'Lucas Arbex M. Brazão';
        $usuario->email = 'desenvolvimento@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 1;
        $usuario->password = bcrypt('Senha102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Administrador';
        $usuario->email = 'admin@admin';
        $usuario->idregional = 1;
        $usuario->idperfil = 1;
        $usuario->password = bcrypt('Admin102030!@#');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Edson Yassudi Miyashiro';
        $usuario->email = 'edson@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 1;
        $usuario->password = bcrypt('edson102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Ricardo Tejada';
        $usuario->email = 'ricardo.tejada@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 1;
        $usuario->password = bcrypt('ricardo102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'José dos Santos Junior';
        $usuario->email = 'suporte@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 9;
        $usuario->password = bcrypt('jose102030');
        $usuario->save();
        
        $usuario = new User();
        $usuario->nome = 'Márcio C. Gonçalez';
        $usuario->email = 'comunicacao@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 3;
        $usuario->password = bcrypt('comunicacao102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Paulo Porto';
        $usuario->email = 'paulo.porto@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 2;
        $usuario->password = bcrypt('paulo102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Gustavo Crema';
        $usuario->email = 'assessoriajuridica@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 2;
        $usuario->password = bcrypt('gustavo102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Luciana Keli Pereira';
        $usuario->email = 'coordenadoria.atendimento@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 6;
        $usuario->password = bcrypt('luciana102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Merielen Silva B. dos Santos';
        $usuario->email = 'atendimento.sede@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 7;
        $usuario->password = bcrypt('merielen102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Mirella D. Andrea Moreno';
        $usuario->email = 'atendimento.seccionais@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 7;
        $usuario->password = bcrypt('mirella102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Fraelli Carollini B. Brito';
        $usuario->email = 'fraelli@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('fraelli102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Emanuelle M. de Paiva Araújo';
        $usuario->email = 'emanuelle@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('emanuele102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Cícero M. da Silva Junior';
        $usuario->email = 'cicero@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('cicero102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Ana Karolina A. Santana';
        $usuario->email = 'anakarolina@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('karol102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Eduardo Alvarenga Paula';
        $usuario->email = 'seccional.campinas@core-sp.org.br';
        $usuario->idregional = 2;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('eduardo102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'João Gilberto Ribeiro';
        $usuario->email = 'seccional.campinas2@core-sp.org.br';
        $usuario->idregional = 2;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('joao102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Wendel Fernando da Silva';
        $usuario->email = 'seccional.bauru@core-sp.org.br';
        $usuario->idregional = 3;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('wendel102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Paula Tazinaffo Tavares';
        $usuario->email = 'seccional.ribeiraopreto@core-sp.org.br';
        $usuario->idregional = 4;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('paula102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Deise Michele Vilela';
        $usuario->email = 'seccional.sjcampos@core-sp.org.br';
        $usuario->idregional = 5;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('deise102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Matheus Braga Ribeiro';
        $usuario->email = 'seccional.sjcampos2@core-sp.org.br';
        $usuario->idregional = 5;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('matheus102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Doacir Francisco Fagundes';
        $usuario->email = 'seccional.sjriopreto@core-sp.org.br';
        $usuario->idregional = 6;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('doacir102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Cleber Junior Falquete';
        $usuario->email = 'seccional.sjriopreto2@core-sp.org.br';
        $usuario->idregional = 6;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('cleber102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Sidileni de Abreu T. Caseiro';
        $usuario->email = 'seccional.presidenteprudente@core-sp.org.br';
        $usuario->idregional = 7;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('sidileni102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Amanda Rotondo Piffer';
        $usuario->email = 'seccional.araraquara@core-sp.org.br';
        $usuario->idregional = 8;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('amanda102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Débora Paschoal Papa';
        $usuario->email = 'seccional.sorocaba@core-sp.org.br';
        $usuario->idregional = 9;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('debora102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Barbara Arruda';
        $usuario->email = 'seccional.santos@core-sp.org.br';
        $usuario->idregional = 10;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('barbara102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Kátia Aparecida Panini';
        $usuario->email = 'seccional.aracatuba@core-sp.org.br';
        $usuario->idregional = 11;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('katia102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Marta Helena Gonçalvez Bueno';
        $usuario->email = 'seccional.rioclaro@core-sp.org.br';
        $usuario->idregional = 12;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('marta102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Alyne Colombo M. Magalhães';
        $usuario->email = 'seccional.marilia@core-sp.org.br';
        $usuario->idregional = 13;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('alyne102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Tânia Lúcia S. G. Delarco';
        $usuario->email = 'seccional.marilia2@core-sp.org.br';
        $usuario->idregional = 13;
        $usuario->idperfil = 8;
        $usuario->password = bcrypt('tania102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Samuel dos Santos';
        $usuario->email = 'informacoes@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 5;
        $usuario->password = bcrypt('samuel102030');
        $usuario->save();

        $usuario = new User();
        $usuario->nome = 'Maike André Marques';
        $usuario->email = 'licitacoes@core-sp.org.br';
        $usuario->idregional = 1;
        $usuario->idperfil = 4;
        $usuario->password = bcrypt('maike102030');
        $usuario->save();
    }
}
