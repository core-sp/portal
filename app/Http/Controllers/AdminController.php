<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        if(session('idusuario') === 9) {
            $count = Agendamento::where('dia','<',$hoje)
                ->where('status','=',null)
                ->where('idregional','=',1)
                ->count();
            $alertas['agendamentoCount'] = $count;
        } elseif(session('idusuario') === 10) {
            $count = Agendamento::where('dia','<',$hoje)
                ->where('status','=',null)
                ->where('idregional','!=',1)
                ->count();
            $alertas['agendamentoCount'] = $count;
        } elseif(session('idusuario') === 8) {
            $count = Agendamento::where('dia','<',$hoje)
                ->where('status','=',null)
                ->count();
            $alertas['agendamentoCount'] = $count;
        }
        if($count < 1) {
            $alertas = [];
        }
        return $alertas;
    }
}
