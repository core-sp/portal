<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Agendamento;
use App\User;

class AdminController extends Controller
{
    public function __construct()
    {
    	$this->middleware('auth');
    }

    public function index()
    {
    	return view('admin.home');
    }

    public static function alertas()
    {
        $alertas = [];
        $count = 0;
        // Alerta de atendimentos nulos
        $hoje = date('Y-m-d');
        if(session('idperfil') === 12) {
            $count = Agendamento::where('dia','<',$hoje)
                ->where('status','=',null)
                ->where('idregional','=',1)
                ->count();
            $alertas['agendamentoCount'] = $count;
        } elseif(session('idperfil') === 13) {
            $count = Agendamento::where('dia','<',$hoje)
                ->where('status','=',null)
                ->where('idregional','!=',1)
                ->count();
            $alertas['agendamentoCount'] = $count;
        } elseif(session('idperfil') === 6 || session('idperfil') === 1) {
            $count = Agendamento::where('dia','<',$hoje)
                ->where('status','=',null)
                ->count();
            $alertas['agendamentoCount'] = $count;
        } elseif(session('idperfil') === 8) {
            $count = Agendamento::where('dia','<',$hoje)
                ->where('status','=',null)
                ->where('idregional','=',Auth::user()->idregional)
                ->count();
            $alertas['agendamentoCount'] = $count;
        }
        if($count < 1) {
            $alertas = [];
        }
        return $alertas;
    }

    public static function countAtendimentos()
    {
        $array = Agendamento::select('idusuario')
                            ->with(['user' => function ($q) {
                                $q->select('idusuario');
                            }])->where('status','Compareceu')
                            ->where('idregional',1)
                            ->get()
                            ->toArray();
        $count = [];
        foreach($array as $a) {
            array_push($count, $a['idusuario']);
        }
        $countPerUser = array_count_values($count);
        $users = User::select('idusuario','nome')
                     ->where('idregional',1)
                     ->where('idperfil',8)
                     ->orderBy('nome','DESC')
                     ->get()
                     ->toArray();
        $tabela = '<table class="table table-bordered table-striped">';
        $tabela .= '<thead>';
        $tabela .= '<tr>';
        $tabela .= '<th>Atendente</th>';
        $tabela .= '<th>Atendimentos</th>';
        $tabela .= '</tr>';
        $tabela .= '</thead>';
        $tabela .= '<tbody>';
        foreach($users as $user) {
            $tabela .= '<tr>';
            $tabela .= '<td>'.$user["nome"].'</td>';
            if(isset($countPerUser[$user["idusuario"]]))
                $tabela .= '<td>'.$countPerUser[$user["idusuario"]].'</td>';
            else
            $tabela .= '<td>0</td>';
            $tabela .= '</tr>';
        }
        $tabela .= '</tbody>';
        $tabela .= '</table>';
        return $tabela;
    }
}
