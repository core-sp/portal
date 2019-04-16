<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Agendamento;
use App\User;
use App\AgendamentoBloqueio;

class AgendamentoControllerHelper extends Controller
{
    public static function countAtendentes($idregional)
    {
        $atendimento = 'Atendimento';
        $getAtendentes = User::whereHas('perfil', function($q) use($atendimento) {
            $q->where('nome', $atendimento);
        })->get();
        $count = $getAtendentes->where('idregional', $idregional)->count();
        return $count;
    }

    public static function horas($regional, $dia)
    {
        $horas = [
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '12:00',
            '12:30',
            '13:00',
            '13:30',
            '14:00',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00',
            '17:30',
        ];
        $checaBloqueio = AgendamentoBloqueio::where('idregional',$regional)
            ->whereDate('diainicio','<=',$dia)
            ->whereDate('diatermino','>=',$dia)
            ->first();
        if($checaBloqueio) {
            $horaInicio = $checaBloqueio->horainicio;
            $horaTermino = $checaBloqueio->horatermino;
            $keyHoraInicio = array_search($horaInicio, $horas);
            $keyHoraTermino = array_search($horaTermino, $horas);
            for($i = $keyHoraInicio; $i <= $keyHoraTermino; $i++)
                unset($horas[$i]);
        }
        return $horas;
    }

    public static function servicos()
    {
        $servicos = [
            'Refis',
        ];
        return $servicos;
    }

    public static function pessoas()
    {
        $pessoas = [
            'Pessoa Física' => 'PF',
            'Pessoa Jurídica' => 'PJ',
            'Ambas' => 'PF e PJ'
        ];
        return $pessoas;
    }
}
