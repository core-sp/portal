<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agendamento;
use App\Regional;
use App\Http\Controllers\Helpers\AgendamentoControllerHelper;

class AgendamentoController extends Controller
{
    public function index()
    {
        AgendamentoControllerHelper::countAtendentes(2);
    }

    public function formView()
    {
        $regionais = Regional::all();
        return view('site.agendamento', compact('regionais'));
    }

    public function permiteAgendamento($dia)
    {

    }
}
