<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agendamento;
use App\Regional;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Helpers\AgendamentoControllerHelper;
use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoMailGuest;
use App\Rules\Cpf;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Validator;
use Redirect;

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
        $contagem = Regional::select('ageporhorario')->where('idregional',1)->first()->ageporhorario;
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
            if($checaAgendamento < $contagem)
                return true;
            else
                return false;
        } elseif($contagem < 1) {
            return false;
        }
    }

    public function checaHorariosDisponiveis($dia, $idregional)
    {
        $agendamentos = Agendamento::where('dia',$dia)
            ->where('idregional',$idregional)
            ->whereNull('status')
            ->get();
        $horarios = [];
        $contagem = Regional::select('ageporhorario')->where('idregional',1)->first()->ageporhorario;
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
        $idregional = $_POST['idregional'];
        $dia = $_POST['dia'];
        $dia = str_replace('/', '-', $_POST['dia']);
        $dia = date('Y-m-d', strtotime($dia));
        $horarios = AgendamentoControllerHelper::horas($idregional, $dia);
        // Checa pela contagem
        $contagem = Regional::select('ageporhorario')->where('idregional',1)->first()->ageporhorario;
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
            $horariosJaCheios = [];
            foreach($valores as $chave => $numero) {
                if($numero >= $contagem)
                    array_push($horariosJaCheios, $chave);
            }
            $horariosPossiveis = array_diff($horarios, $horariosJaCheios);
            foreach($horariosPossiveis as $h) {
                echo "<option value='".$h."'>".$h."</option>";
            }
            return $horariosPossiveis;
        } elseif($contagem < 1) {
            $horarios = AgendamentoControllerHelper::todasHoras();
            return $horarios;
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
            'nome' => 'required|max:191',
            'cpf' => ['required', 'max:191', new Cpf],
            'email' => 'required|email|max:191',
            'celular' => 'max:191',
            'dia' => 'required',
            'hora' => 'required|max:191',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'dia.required' => 'Informe o dia do atendimento',
            'hora.required' => 'Informe o horário do atendimento',
            'email' => 'Email inválido',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $validation = Validator::make($request->all(), $regras, $mensagens);
        if($validation->fails()) {
            return Redirect::back()->withErrors($validation)->withInput($request->all());
        }
        // Organiza dados de dia e hora
        $regional = $request->input('idregional');
        $dia_inalterado = $request->input('dia');
        $dia = str_replace('/', '-', $request->input('dia'));
        $dia = date('Y-m-d', strtotime($dia));
        $diaAtual = date('Y-m-d');
        if(!preg_match('/^[0-9-]+$/', $dia))
            abort(500);
        if($dia <= $diaAtual) 
            abort(500);
        $hora = $request->input('hora');
        $cpf = $request->input('cpf');
        if(!$this->permiteAgendamento($dia, $hora, $regional))
            abort(500);
        // Limita em até dois atendimentos por CPF por dia
        if(!$this->limiteCpf($dia, $cpf))
            abort(500, 'É permitido apenas 2 agendamentos por CPF por dia!');
        // Monta a string de tipo de serviço
        $tiposervico = $request->input('servico').' para '.$request->input('pessoa');
        // Gera a HASH (protocolo) aleatória
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVXZ0123456789';
        do {
            $random = substr(str_shuffle($characters), 0, 6);
            $random = 'AGE-'.$random;
            $checaProtocolo = Agendamento::where('protocolo',$random)->get();
        } while(!$checaProtocolo->isEmpty()); 
        $emailUser = $request->input('email');
        $nomeUser = $request->input('nome');
        //Inputa os dados
        $agendamento = new Agendamento();
        $agendamento->nome = $nomeUser;
        $agendamento->cpf = $cpf;
        $agendamento->email = $emailUser;
        $agendamento->celular = $request->input('celular');
        $agendamento->dia = $dia;
        $agendamento->hora = $hora;
        $agendamento->protocolo = $random;
        $agendamento->tiposervico = $tiposervico;
        $agendamento->idregional = $regional;
        $save = $agendamento->save();
        if(!$save)
            abort(500);
        // Gera evento de agendamento
        $string = $nomeUser." (CPF: ".$cpf.")";
        $string .= " *agendou* atendimento em *".$agendamento->regional->regional;
        $string .= "* no dia ".$dia_inalterado;
        event(new ExternoEvent($string));
        // Gera mensagem de agradecimento
        $agradece = "<strong>Seu atendimento foi agendado com sucesso!</strong>";
        $agradece .= "<br>";
        $agradece .= "Por favor, compareça ao escritório do CORE-SP com no mínimo 15 minutos de antecedência e com o número de protocolo em mãos.";
        $agradece .= "<br><br>";
        $agradece .= "<strong>Protocolo:</strong> ".$random;
        $agradece .= "<br><br>";
        $agradece .= "<strong>Detalhes do agendamento</strong><br>";
        $agradece .= "Nome: ".$nomeUser."<br>";
        $agradece .= "CPF: ".$cpf."<br>";
        $agradece .= "Dia: ".$dia_inalterado."<br>";
        $agradece .= "Horário: ".$agendamento->hora."<br>";
        $agradece .= "Cidade: ".$agendamento->regional->regional."<br>";
        $agradece .= "Endereço: ".$agendamento->regional->endereco.", ".$agendamento->regional->numero;
        $agradece .= " - ".$agendamento->regional->complemento."<br>";
        $agradece .= "Serviço: ".$tiposervico.'<br>';
        $adendo = '<i>* As informações serão enviadas ao email cadastrado no formulário</i>';
        // Texto suplementar ao email de Agendamento
        $sup = AgendamentoControllerHelper::textoSuplementarMail();
        $body = $agradece.$sup;
        Mail::to($emailUser)->queue(new AgendamentoMailGuest($body));

        // Retorna view de agradecimento
        return view('site.agradecimento')->with([
            'agradece' => $agradece,
            'adendo' => $adendo
        ]);
    }

    public function limiteCPF($dia, $cpf)
    {
        $count = Agendamento::where('dia',$dia)
            ->where('cpf',$cpf)
            ->count();
        if($count >= 2)
            return false;
        else
            return true;
    }

    public function consultaView()
    {
        return view('site.agendamento-consulta');
    }

    public function consulta()
    {
        $protocolo = Input::get('protocolo');
        if (!empty($protocolo)){
            $busca = true;
        } else {
            $busca = false;
        }
        $now = date('Y-m-d');
        $protocolo = 'AGE-'.$protocolo;
        $resultado = Agendamento::where('protocolo','LIKE',$protocolo)
            ->where('dia','>=',$now)
            ->first();
        return view('site.agendamento-consulta', compact('resultado', 'busca'));
    }

    public function cancelamento(Request $request)
    {
        $id = $request->input('idagendamento');
        $cpf = $request->input('cpf');
        $protocolo = $request->input('protocolo');
        // Define as regras de validação
        $regras = [
            'cpf' => 'required|max:191'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        //Chama o banco
        $agendamento = Agendamento::find($id);
        if($agendamento->cpf != $cpf){
            return redirect('/agendamento-consulta')
                ->with('message', '<i class="icon fa fa-ban"></i>O CPF informado não corresponde ao protocolo. Por favor, pesquise novamente o agendamento')
                ->with('class', 'alert-danger');
        } else {
            $now = date('Y-m-d');
            if($now < $agendamento->dia) {
                $agendamento->status = "Cancelado";
                $update = $agendamento->update();
                if(!$update)
                    abort(500);
                // Gera evento de agendamento
                $string = $agendamento->nome." (CPF: ".$agendamento->cpf.")";
                $string .= " *cancelou* atendimento em *".$agendamento->regional->regional;
                $string .= "* no dia ".Helper::onlyDate($agendamento->dia);
                event(new ExternoEvent($string));
                // Gera mensagem de agradecimento
                $agradece = "Agendamento cancelado com sucesso!";
                return view('site.agradecimento')->with('agradece', $agradece);
            } else {
                return redirect('/agendamento-consulta')
                    ->with('message', '<i class="icon fa fa-ban"></i>Não é possível cancelar o agendamento no dia do atendimento')
                    ->with('class', 'alert-danger');
            }
        }
    }

}
