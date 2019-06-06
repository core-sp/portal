<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Agendamento;
use App\User;
use App\Http\Controllers\Helper;
use App\AgendamentoBloqueio;

class AgendamentoControllerHelper extends Controller
{
    public static function countAtendentes($idregional)
    {
        $count = User::where('idregional',$idregional)
            ->whereHas('perfil', function($q) {
                $q->where('nome','=','Atendimento');
            })->count();
        if($count === 0)
            $count = 1;
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
        ];
        $bloqueios = AgendamentoBloqueio::where('idregional',$regional)
            ->whereDate('diainicio','<=',$dia)
            ->whereDate('diatermino','>=',$dia)
            ->get();
        if($bloqueios) {
            foreach($bloqueios as $bloqueio) {
                $horaInicio = $bloqueio->horainicio;
                $horaTermino = $bloqueio->horatermino;
                $keyHoraInicio = array_search($horaInicio, $horas);
                $keyHoraTermino = array_search($horaTermino, $horas);
                if(!$keyHoraTermino) {
                    $ultimoHoras = end($horas);
                    if($horaTermino > $ultimoHoras) {
                        $keyHoraTermino = key($horas);
                    } else {
                        $horas = [];
                    }
                }
                if(!$keyHoraInicio) {
                    $primeiroHoras = reset($horas);
                    if($horaInicio < $primeiroHoras) {
                        $keyHoraInicio = 0;
                    } else {
                        $horas = [];
                    }
                }
                for($i = $keyHoraInicio; $i <= $keyHoraTermino; $i++)
                    unset($horas[$i]);
            }
            return $horas;
        }
    }

    public static function todasHoras()
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
        ];
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

    public static function status()
    {
        $status = [
            'Compareceu',
            'Cancelado'
        ];
        return $status;
    }

    public static function txtAgendamento($dia, $hora, $status, $protocolo, $id)
    {
        $now = date('Y-m-d');
        if($now > $dia) {
            if($status === 'Cancelado' || $status === null) 
                echo "<p class='mb-0'><i>* Agendamento cancelado</i></p>";
            else
                echo "<p class='mb-0'><i>* Agendamento realizado no dia ".Helper::onlyDate($dia).", às ".$hora."</i></p>";
        } else {
            // Botão de reenviar email
            $botao = '<form method="POST" action="/admin/agendamentos/reenviar-email/'.$id.'" class="d-inline">';
            $botao .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $botao .= '<input type="submit" class="btn btn-sm btn-default" value="Reenviar email de confirmação"></input>';
            $botao .= '</form>';
            $cancelar = " <a href='/agendamento-consulta/busca?protocolo=".str_replace('AGE-','',$protocolo)."' class='btn btn-sm btn-danger' target='_blank'>Cancelar agendamento</a>";
            echo $botao.$cancelar;
        }
    }
}
