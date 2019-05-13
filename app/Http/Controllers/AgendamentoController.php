<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agendamento;
use App\Regional;
use App\User;
use Carbon\Carbon;
use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Helpers\AgendamentoControllerHelper;
use App\Http\Controllers\ControleController;
use Illuminate\Support\Facades\Auth;
use App\Events\CrudEvent;

class AgendamentoController extends Controller
{
    // Nome da classe
    private $class = 'AgendamentoController';
    // Variáveis extras da página
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

    public function resultados($dia, $idregional = null)
    {
        $resultados = Agendamento::where('dia','=',$dia)
            ->where('idregional',$idregional)
            ->orderBy('dia','ASC')
            ->orderBy('hora','ASC')
            ->paginate(10);
        return $resultados;
    }

    public function checaFiltros()
    {
        // Filtra dia da busca
        if(Input::has('dia')) {
            if(!empty(Input::get('dia'))) {
                $dia = Input::get('dia');
                $replace = str_replace('/','-',$dia);
                $dia = new \DateTime($replace);
                $dia = $dia->format('Y-m-d');
            } else {
                $date = new \DateTime();
                $dia = $date->format('Y-m-d');
            }
        } else {
            $date = new \DateTime();
            $dia = $date->format('Y-m-d');
        }
        if(Input::has('regional')) {
            $regional = Input::get('regional');
            $regionalId = Regional::find($regional);
            $regionalNome = $regionalId->regional;
        } else {
            $regional = Regional::find(Auth::user()->idregional);
            $regionalNome = $regional->regional;
        }
        ControleController::autoriza($this->class, 'edit');
        // Puxa os resultados
        if(Input::has(['regional','status'])) {
            $resultados = $this->resultadosFiltro($dia, Input::get('regional'), Input::get('status'));
        } elseif (Input::has('regional')) {
            $resultados = $this->resultados($dia, Input::get('regional'));
        } elseif(Input::has('status')) {
            $resultados = $this->resultadosFiltro($dia, $regional->idregional, Input::get('status'));
        } elseif(Input::has('dia')) {
            $resultados = $this->resultados($dia, $regional->idregional);
        }
        // Pega dia atual e cospe no título
        $dia = Helper::onlyDate($dia);
        $this->variaveis['continuacao_titulo'] = 'em '.$regionalNome.' - '.$dia;
        return $resultados;
    }

    public function resultadosFiltro($dia = null, $idregional = null, $status = null)
    {
        if(!isset($dia)) {
            $date = new \DateTime();
            $dia = $date->format('Y-m-d');
        }
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
                ->where('dia','=',$dia)
                ->whereNull('status')
                ->orderBy('dia','ASC')
                ->orderBy('hora','ASC')
                ->get();
        } else {
            $resultados = Agendamento::where('idregional',$idregional)
                ->where('dia','=',$dia)
                ->where('status','LIKE',$status)
                ->orderBy('dia','ASC')
                ->orderBy('hora','ASC')
                ->get();
        }
        return $resultados;
    }

    public function index()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $regional = Auth::user()->idregional;
        // Checa se tem filtro
        if(Input::get('filtro') == 'sim') {
            $temFiltro = true;
            $resultados = $this->checaFiltros();
        } else {
            $temFiltro = null;
            $date = new \DateTime();
            $dia = $date->format('Y-m-d');
            $diaFormatado = $date->format('d\/m\/Y');
            $resultados = $this->resultados($dia, $regional);
            $regionalId = Regional::find(Auth::user()->idregional);
            $regionalNome = $regionalId->regional;
            $this->variaveis['continuacao_titulo'] = 'em '.$regionalNome.' - '.$diaFormatado;
        }
        // Monta tabela com resultados
        $tabela = $this->tabelaCompleta($resultados);
        // Variáveis globais
        $variaveis = $this->variaveis;
        $variaveis['filtro'] = $this->filtros();
        if(ControleController::mostra($this->class, 'edit'))
            $variaveis['mostraFiltros'] = true;
        $variaveis = (object) $variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro'));
    }

    public function filtros()
    {
        $regionais = Regional::all();
        $select = '<form method="GET" action="/admin/agendamentos/filtro" class="mb-0">';
        $select .= '<input type="hidden" name="filtro" value="sim" />';
        $select .= '<select class="d-inline w-auto custom-select custom-select-sm mr-2" name="regional">';
        $select .= '<option disabled selected>Seccional *</option>';
        foreach($regionais as $regional) {
            if(Input::has('regional')) {
                if($regional->idregional == Input::get('regional')) {
                    $select .= '<option value="'.$regional->idregional.'" selected>'.$regional->regional.'</option>';
                } else {
                    $select .= '<option value="'.$regional->idregional.'">'.$regional->regional.'</option>';
                }
            } else {
                $select .= '<option value="'.$regional->idregional.'">'.$regional->regional.'</option>';
            }
        }
        $select .= '</select>';
        $select .= '<select class="d-inline w-auto custom-select custom-select-sm" name="status">';
        $select .= '<option disabled selected>Status</option>';
        if(Input::has('status')) {
            if(Input::get('status') == "Compareceu") {
                $select .= '<option value="Compareceu" selected>Compareceram</option>';
                $select .= '<option value="null">Não Compareceram</option>';
            } else {
                $select .= '<option value="Compareceu">Compareceram</option>';
                $select .= '<option value="null" selected>Não Compareceram</option>';
            }
        } else {
            $select .= '<option value="Compareceu">Compareceram</option>';
            $select .= '<option value="null">Não Compareceram</option>';
        }
        $select .= '</select>';
        $select .= '<div class="d-inline-block mr-2 ml-2">';
        if(Input::has('dia')) {
            $dia = Input::get('dia');
            $select .= '<input type="text" class="form-control d-inline-block dataInput form-control-sm" name="dia" placeholder="dd/mm/aaaa" value="'.$dia.'" />';
        } else {
            $select .= '<input type="test" class="form-control d-inline-block dataInput form-control-sm" name="dia" placeholder="dd/mm/aaaa" />';
        }
        $select .= '</div>';
        $select .= '<input type="submit" class="btn btn-sm btn-default" value="Filtrar" />';
        $select .= '</form>';
        return $select;
    }

    public function status($status, $id, $usuario = null)
    {
        switch ($status) {
            case 'Cancelado':
                return "<strong>Cancelado</strong>";
            break;

            case 'Compareceu':
                $string = "<p class='d-inline'><i class='fas fa-check checkIcone'></i>&nbsp;&nbsp;Compareceu&nbsp;&nbsp;</p>";
                if(ControleController::mostra($this->class, 'edit'))
                    $string .= "<a href='/admin/agendamentos/editar/".$id."' class='btn btn-sm btn-default'>Editar</a>";
                if(isset($usuario))
                    $string .= "<small class='d-block'>Atendido por: <strong>".$usuario."</strong></small>";
                return $string;
            break;

            default:
                $acoes = '<form method="POST" id="statusAgendamento" action="/admin/agendamentos/status" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" id="tokenStatusAgendamento" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="PUT" id="method" />';
                $acoes .= '<input type="hidden" name="idagendamento" value="'.$id.'" />';
                $acoes .= '<input type="hidden" name="status" id="status" value="Compareceu" />';
                $acoes .= '<input type="submit" value="Confirmar presença" id="btnSubmit" class="btn btn-sm ml-1 btn-primary" />';
                $acoes .= '</form>';
                if(ControleController::mostra($this->class, 'edit'))
                    $acoes .= " <a href='/admin/agendamentos/editar/".$id."' class='btn btn-sm btn-default'>Editar</a>";
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
            if(isset($resultado->user->nome))
                $nomeusuario = $resultado->user->nome;
            else
                $nomeusuario = null;
            $acoes = $this->status($resultado->status, $resultado->idagendamento, $nomeusuario);
            // Mostra dados na tabela
            $conteudo = [
                $resultado->protocolo.'<br><small>Código: '.$resultado->idagendamento.'</small>',
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

    public function updateStatus()
    {
        $idusuario = Auth::user()->idusuario;
        $idagendamento = $_POST['idagendamento'];
        $status = $_POST['status'];
        $agendamento = Agendamento::find($idagendamento);
        $agendamento->status = $status;
        $agendamento->idusuario = $idusuario;
        $update = $agendamento->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('agendamento', 'confirmou presença', $agendamento->idagendamento));
        return redirect()->route('agendamentos.lista');
    }

    public function busca()
    {
        ControleController::autoriza($this->class, 'index');
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

    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = Agendamento::find($id);
        $atendentes = User::select('idusuario','nome')
            ->where('idregional',$resultado->idregional)
            ->whereHas('perfil', function($q) {
                $q->where('nome','=','Atendimento');
            })->get();
        $regionais = Regional::select('idregional','regional')->get();
        $variaveis = $this->variaveis;
        $variaveis['cancela_idusuario'] = true;
        $variaveis = (object) $variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis', 'atendentes', 'regionais'));
    }

    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $regras = [
            'nome' => 'required|max:191',
            'cpf' => 'required|max:191',
            'celular' => 'required|max:191',
            'regional' => 'max:191',
            'atendente' => 'max:191',
            'status' => 'max:191',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'min' => 'O campo :attribute não possui o mínimo de caracteres obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);

        $agendamento = Agendamento::find($id);
        $agendamento->nome = $request->input('nome');
        $agendamento->cpf = $request->input('cpf');
        $agendamento->celular = $request->input('celular');
        $agendamento->idregional = $request->input('regional');
        $agendamento->idusuario = $request->input('atendente');
        $agendamento->status = $request->input('status');
        $update = $agendamento->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('agendamento', 'editou', $agendamento->idagendamento));
        return redirect('/admin/agendamentos')
            ->with('message', '<i class="icon fa fa-check"></i>Agendamento editado com sucesso!')
            ->with('class', 'alert-success');
    }
}
