<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agendamento;
use App\Regional;
use App\Http\Controllers\Helpers\AgendamentoControllerHelper;

class AgendamentoSiteController extends Controller
{
    public function formView()
    {
        $regionais = Regional::all();
        return view('site.agendamento', compact('regionais'));
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
        $contagem = AgendamentoControllerHelper::countAtendentes($idregional);
        if($contagem == 1) {
            foreach($agendamentos as $agendamento) {
                array_push($horarios,$agendamento->hora);
            }
            return $horarios;
        } elseif($contagem > 1) {
            foreach($agendamentos as $agendamento) {
                array_push($horarios,$agendamento->hora);
            }
            return $horarios;
        }
    }

    public function checaHorarios(Request $request)
    {
        $horarios = AgendamentoControllerHelper::horas();
        $idregional = $_POST['idregional'];
        $dia = $_POST['dia'];
        $dia = str_replace('/', '-', $_POST['dia']);
        $dia = date('Y-m-d', strtotime($dia));
        // Checa pela contagem
        $contagem = AgendamentoControllerHelper::countAtendentes($idregional);
        if($contagem == 1) {
            $horariosJaMarcados = $this->checaHorariosDisponiveis($dia,$idregional);
            $horariosPossiveis = array_diff($horarios, $horariosJaMarcados);
            foreach($horariosPossiveis as $h) {
                echo "<option value='".$h."'>".$h."</option>";
            }
            return $horariosPossiveis;
        } elseif($contagem > 1) {
            $horariosJaMarcados = $this->checaHorariosDisponiveis($dia,$idregional);
            $valores = array_count_values($horariosJaMarcados);
            $atendimentos = $contagem - 1;
            $horariosJaCheios = [];
            foreach($valores as $chave => $numero) {
                if($numero >= $atendimentos)
                    array_push($horariosJaCheios, $chave);
            }
            $horariosPossiveis = array_diff($horarios, $horariosJaCheios);
            foreach($horariosPossiveis as $h) {
                echo "<option value='".$h."'>".$h."</option>";
            }
            return $horariosPossiveis;
        } else {
            foreach($horarios as $h) {
                echo "<option value='".$h."'>".$h."</option>";
            }
            return $horarios;
        }
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
        $dia_inalterado = $request->input('dia');
        $dia = str_replace('/', '-', $request->input('dia'));
        $dia = date('Y-m-d', strtotime($dia));
        $hora = $request->input('hora');
        if(!$this->permiteAgendamento($dia, $hora, $regional))
            abort(500);
        // Monta a string de tipo de serviço
        $tiposervico = $request->input('servico').' para '.$request->input('pessoa');
        // Gera a HASH (protocolo) aleatória
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVXZ0123456789';
        do {
            $random = substr(str_shuffle($characters), 0, 6);
            $random = 'AGE'.$random;
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
            abort(500);
        // Gera mensagem de agradecimento
        $agradece = "<strong>Seu atendimento foi agendado com sucesso!</strong>";
        $agradece .= "<br>";
        $agradece .= "Por favor, compareça ao escritório do CORE-SP com no mínimo 15 minutos de antecedência.";
        $agradece .= "<br><br><strong>Detalhes do agendamento</strong><br>";
        $agradece .= "Dia: ".$dia_inalterado."<br>";
        $agradece .= "Horário: ".$agendamento->hora."<br>";
        $agradece .= "Cidade: ".$agendamento->regional->regional."<br>";
        $agradece .= "Endereço: ".$agendamento->regional->endereco.", ".$agendamento->regional->numero;
        $agradece .= " - ".$agendamento->regional->complemento."<br>";
        $agradece .= "Serviço: ".$tiposervico.'<br>';
        $agradece .= "Protocolo: ".$random;

        // Retorna view de agradecimento
        return view('site.agradecimento')->with('agradece', $agradece);
    }
}
