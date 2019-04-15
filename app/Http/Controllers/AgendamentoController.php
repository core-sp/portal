<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agendamento;
use App\Regional;
use Carbon\Carbon;
use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\Input;
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
        $date = new \DateTime();
        $diaAtual = $date->format('Y-m-d');
        $resultados = Agendamento::where('idregional',$idregional)
            ->where('dia','=',$diaAtual)
            ->orderBy('dia','ASC')
            ->orderBy('hora','ASC')
            ->paginate(10);
        return $resultados;
    }

    public function checaFiltros($request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Gestão de Atendimento']);
        if(Input::has(['regional','status'])) {
            $resultados = $this->resultadosFiltro(Input::get('regional'), Input::get('status'));
        } elseif (Input::has('regional')) {
            $resultados = $this->resultados(Input::get('regional'));
        } elseif(Input::has('status')) {
            $resultados = $this->resultadosFiltro($regional, Input::get('status'));
        }
        return $resultados;
    }

    public function resultadosFiltro($idregional, $status)
    {
        $date = new \DateTime();
        $diaAtual = $date->format('Y-m-d');
        switch ($status) {
            case 'Compareceu':
                $status = 'Compareceu';
            break;
            default:
                $status = null;
            break;
        }
        if($status === null) {
            $resultados = Agendamento::where('idregional',$idregional)
                ->where('dia','=',$diaAtual)
                ->whereNull('status')
                ->orderBy('dia','ASC')
                ->orderBy('hora','ASC')
                ->get();
        } else {
            $resultados = Agendamento::where('idregional',$idregional)
                ->where('dia','=',$diaAtual)
                ->where('status','LIKE',$status)
                ->orderBy('dia','ASC')
                ->orderBy('hora','ASC')
                ->get();
        }
        return $resultados;
    }

    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Atendimento', 'Gestão de Atendimento']);
        $regional = $request->user()->idregional;
        // Checa se tem filtro
        if(Input::get('filtro') == 'sim') {
            $temFiltro = true;
            $resultados = $this->checaFiltros($request);
        } else {
            $temFiltro = null;
            $resultados = $this->resultados($regional);
        }
        // Monta tabela com resultados
        $tabela = $this->tabelaCompleta($resultados);
        // Pega dia atual e cospe no título
        $date = new \DateTime();
        $diaAtual = $date->format('d\/m\/Y');
        $this->variaveis['continuacao_titulo'] = 'em '.$request->user()->regional->regional.' - '.$diaAtual;
        // Variáveis globais
        $variaveis = $this->variaveis;
        $variaveis['filtro'] = $this->filtros();
        $variaveis['mostraFiltros'] = [
            'Admin',
            'Gestão de Atendimento'
        ];
        $variaveis = (object) $variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro'));
    }

    public function filtros()
    {
        $regionais = Regional::all();
        $select = '<form method="GET" action="/admin/agendamentos/filtro" class="d-inline">';
        $select .= '<input type="hidden" name="filtro" value="sim" />';
        $select .= '<select class="d-inline w-auto custom-select custom-select-sm mr-2" name="regional">';
        $select .= '<option disabled selected>Seccional *</option>';
        foreach($regionais as $regional) 
            $select .= '<option value="'.$regional->idregional.'">'.$regional->regional.'</option>';
        $select .= '</select>';
        $select .= '<select class="d-inline w-auto custom-select custom-select-sm" name="status">';
        $select .= '<option disabled selected>Status</option>';
        $select .= '<option value="Compareceu">Compareceram</option>';
        $select .= '<option value="NaoCompareceu">Não Compareceram</option>';
        $select .= '</select>';
        $select .= '<input type="submit" class="btn btn-sm btn-primary ml-2" value="Filtrar" />';
        $select .= '</form>';
        return $select;
    }

    public function status($status, $id)
    {
        switch ($status) {
            case 'Cancelado':
                return "<strong>Cancelado</strong>";
            break;

            case 'Compareceu':
                return "<p><i class='fas fa-check checkIcone'></i>&nbsp;&nbsp;Compareceu</p>";
            break;

            default:
                $acoes = '<form method="POST" id="statusAgendamento" action="/admin/agendamentos/status" class="form-inline">';
                $acoes .= '<input type="hidden" name="_token" id="tokenStatusAgendamento" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="PUT" id="method" />';
                $acoes .= '<input type="hidden" name="idagendamento" value="'.$id.'" />';
                $acoes .= '<input type="hidden" name="status" id="status" value="Compareceu" />';
                $acoes .= '<input type="submit" value="Confirmar presença" id="btnSubmit" class="btn btn-sm ml-1 btn-primary" />';
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
            'table-hover'
        ];
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
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
        return redirect()->route('agendamentos.lista');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Agendamento::where('nome','LIKE','%'.$busca.'%')
            ->orWhere('cpf','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->orWhere('protocolo','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
