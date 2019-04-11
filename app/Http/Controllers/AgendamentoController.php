<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agendamento;
use App\Regional;
use Carbon\Carbon;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Helpers\AgendamentoControllerHelper;

class AgendamentoController extends Controller
{
    public $variaveis = [
        'singular' => 'agendamento',
        'singulariza' => 'o agendamento',
        'plural' => 'agendamentos',
        'pluraliza' => 'agendamentos'
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function resultados($idregional = null)
    {
        $date = new \DateTime('+1 day');
        $diaAtual = $date->format('Y-m-d');
        $resultados = Agendamento::where('idregional',$idregional)
            ->where('dia','=',$diaAtual)
            ->orderBy('dia','ASC')
            ->orderBy('hora','ASC')
            ->get();
        return $resultados;
    }

    public function status($status, $id)
    {
        switch ($status) {
            case 'Cancelado':
                return "<strong>Cancelado</strong>";
            break;

            case 'Compareceu':
                return "<strong><i class='fas fa-check'></i></strong>";
            break;

            default:
                $acoes = '<form method="POST" id="statusAgendamento" class="form-inline">';
                $acoes .= '<input type="hidden" name="_token" id="tokenStatusAgendamento" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="PUT" id="method" />';
                $acoes .= '<input type="hidden" name="idagendamento" value="'.$id.'" />';
                $acoes .= '<input type="hidden" name="status" id="status" value="Compareceu" />';
                $acoes .= '<input type="submit" value="Compareceu" class="btn btn-sm ml-1 btn-primary" />';
                $acoes .= '</form>';
                return $acoes;
            break;
        }
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Protocolo',
            'Nome/CPF',
            'Horário',
            'Serviço',
            'Status'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            // Ações possíveis com cada resultado
            $acoes = $this->status($resultado->status, $resultado->idagendamento);
            // Mostra dados na tabela
            $conteudo = [
                $resultado->protocolo,
                $resultado->nome.'<br>'.$resultado->cpf,
                $resultado->hora,
                $resultado->tiposervico,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hovered'
        ];
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }
    
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Atendimento']);
        $regional = $request->user()->idregional;
        $resultados = $this->resultados($regional);
        $tabela = $this->tabelaCompleta($resultados);
        // Pega dia atual e cospe no título
        $date = new \DateTime('+1 day');
        $diaAtual = $date->format('d\/m\/Y');
        $this->variaveis['continuacao_titulo'] = 'em '.$request->user()->regional->regional.' - '.$diaAtual;
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function updateStatus(Request $request)
    {
        $idusuario = $request->user()->idusuario;
        $idagendamento = $_POST['idagendamento'];
        $status = $_POST['status'];
        $agendamento = Agendamento::find($idagendamento);
        $agendamento->status = $status;
        $agendamento->idusuario = $idusuario;
        $update = $agendamento->update();
        if(!$update)
            abort(500);
    }
}
