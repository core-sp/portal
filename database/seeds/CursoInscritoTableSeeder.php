<?php

use Illuminate\Database\Seeder;
use App\CursoInscrito;

class CursoInscritoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $inscrito = new CursoInscrito();
        $inscrito->cpf = "395.697.988-58";
        $inscrito->nome = "Lucas Arbex Martins Brazão";
        $inscrito->telefone = "(11) 98999-9907";
        $inscrito->email = "desenvolvimento@core-sp.org.br";
        $inscrito->idcurso = 2;
        $inscrito->save();

        $inscrito = new CursoInscrito();
        $inscrito->cpf = "146.278.978-12";
        $inscrito->nome = "Edson Yassudi";
        $inscrito->telefone = "(11) 3243-5500";
        $inscrito->email = "ti@core-sp.org.br";
        $inscrito->idcurso = 2;
        $inscrito->save();
    }
}
