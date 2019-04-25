<?php

use Illuminate\Database\Seeder;
use App\Agendamento;

class AgendamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $date = new \DateTime();
        $dia = $date->format('Y-m-d');

        $agendamento = new Agendamento();
        $agendamento->nome = "Lucas Arbex M. Brazão";
        $agendamento->cpf = "395.697.988-58";
        $agendamento->email = "lucasarbexmb@gmail.com";
        $agendamento->celular = "(11) 98999-9907";
        $agendamento->dia = $dia;
        $agendamento->hora = "09:30";
        $agendamento->protocolo = "AGE-000000";
        $agendamento->tiposervico = "Refis para PF";
        $agendamento->idregional = 1;
        $agendamento->idusuario = 1;
        $agendamento->status = "Compareceu";
        $agendamento->save();

        $agendamento = new Agendamento();
        $agendamento->nome = "José Silva";
        $agendamento->cpf = "568.847.750-41";
        $agendamento->email = "josesilva@gmail.com";
        $agendamento->celular = "(11) 97855-9932";
        $agendamento->dia = $dia;
        $agendamento->hora = "10:30";
        $agendamento->protocolo = "AGE-000000";
        $agendamento->tiposervico = "Refis para PJ";
        $agendamento->idregional = 1;
        $agendamento->save();

        $agendamento = new Agendamento();
        $agendamento->nome = "Maria Silva";
        $agendamento->cpf = "968.133.940-10";
        $agendamento->email = "mariasilva@gmail.com";
        $agendamento->celular = "(11) 96666-9932";
        $agendamento->dia = $dia;
        $agendamento->hora = "10:00";
        $agendamento->protocolo = "AGE-000000";
        $agendamento->tiposervico = "Refis para PF";
        $agendamento->idregional = 4;
        $agendamento->save();
    }
}
