<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Agendamento;

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
}
