<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agendamento;
use App\Regional;
use Carbon\Carbon;
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

    public function store(Request $request)
    {
        $regras = [
            'nome' => 'required',
            'cpf' => 'required',
            'email' => 'required',
            'dia' => 'required',
            'hora' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);
        // Organiza dados de dia e hora
        $regional = $request->input('idregional');
        $dia = str_replace('/', '-', $request->input('dia'));
        $dia = date('Y-m-d', strtotime($dia));
        $hora = $request->input('hora');
        if(!$this->permiteAgendamento($dia, $hora, $regional))
            abort(403);
        // Monta a string de tipo de serviço
        $tiposervico = $request->input('servico').' para '.$request->input('pessoa');
        // Gera a HASH (protocolo) aleatória
        $characters = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVxXzZ0123456789';
        do {
            $random = substr(str_shuffle($characters), 0, 8);
            $checaProtocolo = Agendamento::where('protocolo',$random)->get();
        } while(!$checaProtocolo->isEmpty());
        
        //Inputa os dados
        $agendamento = new Agendamento();
        $agendamento->nome = $request->input('nome');
        $agendamento->cpf = $request->input('cpf');
        $agendamento->email = $request->input('email');
        $agendamento->celular = $request->input('celular');
        $agendamento->dia = $dia;
        $agendamento->hora = $hora;
        $agendamento->protocolo = $random;
        $agendamento->tiposervico = $tiposervico;
        $agendamento->idregional = $regional;
        $save = $agendamento->save();
        if(!$save)
            abort(501);
        // Gera mensagem de agradecimento
        $agradece = "Seu atendimento foi agendado com sucesso";
        // Retorna view de agradecimento
        return view('site.agradecimento')->with('agradece', $agradece);
    }

    public function permiteAgendamento($dia, $hora, $idregional)
    {
        // Conta o número de atendentes da seccional
        $contagem = AgendamentoControllerHelper::countAtendentes($idregional);
        $checaAgendamento = Agendamento::where('dia',$dia)
            ->where('hora',$hora)
            ->where('idregional',$idregional)
            ->count();
        if($contagem == 1) {
            if($checaAgendamento < 1)
                return true;
            else
                return false;
        } elseif($contagem > 1) {
            if($checaAgendamento < ($contagem - 1))
                return true;
            else
                return false;
        }
    }

    public function checaHorariosDisponiveis($dia, $idregional)
    {
        $agendamentos = Agendamento::where('dia',$dia)
            ->where('idregional',$idregional)
            ->get();
        $horarios = [];
        foreach($agendamentos as $agendamento) {
            array_push($horarios,$agendamento->hora);
        }
        return $horarios;
    }

    public function checaHorarios(Request $request)
    {
        $horarios = AgendamentoControllerHelper::horas();
        $idregional = $_POST['idregional'];
        $dia = $_POST['dia'];
        $dia = str_replace('/', '-', $_POST['dia']);
        $dia = date('Y-m-d', strtotime($dia));
        $horariosJaMarcados = $this->checaHorariosDisponiveis($dia,$idregional);
        $contagem = AgendamentoControllerHelper::countAtendentes($idregional);
        if($contagem == 1) {
            $horariosPossiveis = array_diff($horarios, $horariosJaMarcados);
            foreach($horariosPossiveis as $h) {
                echo "<option value='".$h."'>".$h."</option>";
            }
            return $horariosPossiveis;
        }
    }
}
