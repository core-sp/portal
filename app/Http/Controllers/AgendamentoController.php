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
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoMailGuest;
use App\Events\CrudEvent;
use Redirect;

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

    public function resultados($idregional = null)
    {
        $date = new \DateTime();
        $dia = $date->format('Y-m-d');
        if($idregional !== '999') {
            $resultados = Agendamento::where('dia','=',$dia)
                ->where('idregional',$idregional)
                ->orderBy('dia','ASC')
                ->orderBy('hora','ASC')
                ->paginate(25);
        } else {
            $resultados = Agendamento::where('dia','=',$dia)
                ->orderBy('dia','ASC')
                ->orderBy('hora','ASC')
                ->paginate(25);
        }
        return $resultados;
    }

    public function checaFiltros()
    {
        ControleController::autoriza($this->class, 'index');
        if(Input::has('mindia')) {
            if(!empty(Input::get('mindia'))) {
                $mindia = Input::get('mindia');
                $replace = str_replace('/','-',$mindia);
                $mindia = new \DateTime($replace);
                $mindia = $mindia->format('Y-m-d');
            } else {
                $date = new \DateTime();
                $mindia = $date->format('Y-m-d');
            }
        } else {
            $date = new \DateTime();
            $mindia = $date->format('Y-m-d');
        }
        if(Input::has('maxdia')) {
            if(!empty(Input::get('maxdia'))) {
                $maxdia = Input::get('maxdia');
                $replace = str_replace('/','-',$maxdia);
                $maxdia = new \DateTime($replace);
                $maxdia = $maxdia->format('Y-m-d');
            } else {
                $date = new \DateTime();
                $maxdia = $date->format('Y-m-d');
            }
        } else {
            $date = new \DateTime();
            $maxdia = $date->format('Y-m-d');
        }
        if(Input::has('regional')) {
            if(Input::get('regional') !== '999') {
                $regional = Input::get('regional');
            } else {
                $regional = '';
            }
        } else {
            $regional = Regional::select('idregional')->find(Auth::user()->idregional);
            $regional = $regional->idregional;
        }
        if(Input::has('status')) {
            if(!empty(Input::get('status'))) {
                $status = Input::get('status');
                if($status === 'Qualquer') {
                    $status = null;
                }
            } else {
                $status = '';
            }
        } else {
            $status = '';
        }
        // Puxa os resultados
        if(isset($status)) {
            if(empty($regional)) {
                $resultados = Agendamento::where('idregional','LIKE','%%')
                    ->where('status','LIKE',$status)
                    ->whereBetween('dia',[$mindia,$maxdia])
                    ->orderBy('idregional','ASC')
                    ->orderBy('dia','DESC')
                    ->orderBy('hora','ASC')
                    ->limit(50)
                    ->paginate(25);
            } else {
                $resultados = Agendamento::where('idregional','LIKE',$regional)
                    ->where('status','LIKE',$status)
                    ->whereBetween('dia',[$mindia,$maxdia])
                    ->orderBy('idregional','ASC')
                    ->orderBy('dia','DESC')
                    ->orderBy('hora','ASC')
                    ->limit(50)
                    ->paginate(25);
            }    
        } else {
            if(empty($regional)) {
                $resultados = Agendamento::where('idregional','LIKE','%%')
                    ->where(function($q){
                        $q->where('status','LIKE','%%')
                            ->orWhereNull('status');
                    })->whereBetween('dia',[$mindia,$maxdia])
                    ->orderBy('idregional','ASC')
                    ->orderBy('dia','DESC')
                    ->orderBy('hora','ASC')
                    ->limit(50)
                    ->paginate(25);
            } else {
                $resultados = Agendamento::where('idregional','LIKE',$regional)
                    ->where(function($q){
                        $q->where('status','LIKE','%%')
                            ->orWhereNull('status');
                    })->whereBetween('dia',[$mindia,$maxdia])
                    ->orderBy('idregional','ASC')
                    ->orderBy('dia','DESC')
                    ->orderBy('hora','ASC')
                    ->limit(50)
                    ->paginate(25);
            }
        }
        return $resultados;
    }

    public function index()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $regional = Auth::user()->idregional;
        // Checa se tem filtro
        if(Input::get('filtro') === 'sim') {
            $temFiltro = true;
            if(!empty(Input::get('mindia'))) {
                $mindiaArray = explode('/',Input::get('mindia'));
                $checaMindia = checkdate($mindiaArray[1], $mindiaArray[0], $mindiaArray[2]);
                if($checaMindia === false) {
                    return Redirect::back()->with('message', '<i class="icon fa fa-ban"></i>Data de início do filtro inválida')
                        ->with('class', 'alert-danger');
                }
            } 
            if(!empty(Input::get('maxdia'))) {
                $maxdiaArray = explode('/',Input::get('maxdia'));
                $checaMaxdia = checkdate($maxdiaArray[1], $maxdiaArray[0], $maxdiaArray[2]);
                if($checaMaxdia === false) {
                    return Redirect::back()->with('message', '<i class="icon fa fa-ban"></i>Data de término do filtro inválida')
                        ->with('class', 'alert-danger');
                }
            }
            $resultados = $this->checaFiltros();
            $this->variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
        } else {
            $temFiltro = null;
            $diaFormatado = date('d\/m\/Y');
            $resultados = $this->resultados($regional);
            $regionalId = Regional::find(Auth::user()->idregional);
            $regionalNome = $regionalId->regional;
            $this->variaveis['continuacao_titulo'] = 'em <strong>'.$regionalNome.' - '.$diaFormatado.'</strong>';
        }
        // Monta tabela com resultados
        $tabela = $this->tabelaCompleta($resultados);
        // Variáveis globais
        $variaveis = $this->variaveis;
        $variaveis['filtro'] = $this->filtros();
        $variaveis['mostraFiltros'] = true;
        $variaveis = (object) $variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro'));
    }

    public function filtros()
    {
        $regionais = Regional::all();
        $select = '<form method="GET" action="/admin/agendamentos/filtro" id="filtroAgendamento" class="mb-0">';
        $select .= '<div class="form-row filtroAge">';
        $select .= '<input type="hidden" name="filtro" value="sim" />';
        if(ControleController::mostra($this->class, 'edit') && session('idperfil') !== 12) {
            $select .= '<div class="form-group mb-0 col">';
            $select .= '<label>Seccional</label>';
            $select .= '<select class="custom-select custom-select-sm mr-2" id="regional" name="regional">';
            if(Input::get('regional') === '999') {
                $select .= '<option value="999" selected>Todas</option>';
            } else {
                $select .= '<option value="999">Todas</option>';
            }
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
            $select .= '</div>';
        }
        $select .= '<div class="form-group mb-0 col">';
        $select .= '<label>Status</label>';
        $select .= '<select class="custom-select custom-select-sm" name="status">';
        if(Input::get('status') === 'Qualquer')
            $select .= '<option value="Qualquer" selected>Qualquer</option>';
        else
            $select .= '<option value="Qualquer">Qualquer</option>';
        // Pega os status
        $status = AgendamentoControllerHelper::status();
        foreach($status as $s) {
            if(Input::has('status')) {
                if(Input::get('status') === $s) {
                    $select .= '<option value="'.$s.'" selected>'.$s.'</option>';
                } else {
                    $select .= '<option value="'.$s.'">'.$s.'</option>';
                }
            } else {
                $select .= '<option value="'.$s.'">'.$s.'</option>';
            }
        }
        $select .= '</select>';
        $select .= '</div>';
        $select .= '<div class="form-group mb-0 col">';
        $hoje = date('d\/m\/Y');
        $select .= '<label>De</label>';
        if(Input::has('mindia')) {
            $mindia = Input::get('mindia');
            $select .= '<input type="text" class="form-control d-inline-block dataInput form-control-sm" name="mindia" id="mindiaFiltro" placeholder="dd/mm/aaaa" value="'.$mindia.'" />';
        } else {
            $select .= '<input type="test" class="form-control d-inline-block dataInput form-control-sm" name="mindia" id="mindiaFiltro" placeholder="dd/mm/aaaa" value="'.$hoje.'" />';
        }
        $select .= '</div>';
        $select .= '<div class="form-group mb-0 col">';
        $select .= '<label>Até</label>';
        if(Input::has('maxdia')) {
            $maxdia = Input::get('maxdia');
            $select .= '<input type="text" class="form-control d-inline-block dataInput form-control-sm" name="maxdia" id="maxdiaFiltro" placeholder="dd/mm/aaaa" value="'.$maxdia.'" />';
        } else {
            $select .= '<input type="test" class="form-control d-inline-block dataInput form-control-sm" name="maxdia" id="maxdiaFiltro" placeholder="dd/mm/aaaa" value="'.$hoje.'" />';
        }
        $select .= '</div>';
        $select .= '<div class="form-group mb-0 col-auto align-self-end">';
        $select .= '<input type="submit" class="btn btn-sm btn-default" value="Filtrar" />';
        $select .= '</div>';
        $select .= '</div>';
        $select .= '</form>';
        return $select;
    }

    public function status($status, $id, $usuario = null)
    {
        if(Input::has('regional') && session('idperfil') === 8) {
            if(Input::get('regional') !== Auth::user()->idregional)
                abort(401);
        }
        switch ($status) {
            case 'Cancelado':
                $btn = "<strong>Cancelado</strong>";
                if(ControleController::mostra($this->class, 'edit'))
                    $btn .= "&nbsp;&nbsp;<a href='/admin/agendamentos/editar/".$id."' class='btn btn-sm btn-default'>Editar</a>";
                return $btn;
            break;

            case 'Compareceu':
                $string = "<p class='d-inline'><i class='fas fa-check checkIcone'></i>&nbsp;&nbsp;Compareceu&nbsp;&nbsp;</p>";
                if(ControleController::mostra($this->class, 'edit'))
                    $string .= "<a href='/admin/agendamentos/editar/".$id."' class='btn btn-sm btn-default'>Editar</a>";
                if(isset($usuario))
                    $string .= "<small class='d-block'>Atendido por: <strong>".$usuario."</strong></small>";
                return $string;
            break;

            case 'Não Compareceu':
                $btn = "<strong>Não Compareceu</strong>";
                if(ControleController::mostra($this->class, 'edit'))
                    $btn .= "&nbsp;&nbsp;<a href='/admin/agendamentos/editar/".$id."' class='btn btn-sm btn-default'>Editar</a>";
                return $btn;
            break;

            default:
                $acoes = '<form method="POST" id="statusAgendamento" action="/admin/agendamentos/status" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" id="tokenStatusAgendamento" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="PUT" id="method" />';
                $acoes .= '<input type="hidden" name="idagendamento" value="'.$id.'" />';
                $acoes .= '<button type="submit" name="status" id="btnSubmit" class="btn btn-sm btn-primary" value="Compareceu">Confirmar</button>';
                $acoes .= '<button type="submit" name="status" id="btnSubmit" class="btn btn-sm btn-danger ml-1" value="Não Compareceu">Não Compareceu</button>';
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
            'Horário/Dia',
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
                $resultado->hora.'<br><small><strong>'.Helper::onlyDate($resultado->dia).'</strong></small>',
                $resultado->tiposervico.'<br><small>('.$resultado->regional->regional.')',
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-bordered',
            'table-striped'
        ];
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function updateStatus()
    {
        $idusuario = Auth::user()->idusuario;
        $idagendamento = $_POST['idagendamento'];
        $status = $_POST['status'];
        $agendamento = Agendamento::findOrFail($idagendamento);
        $agendamento->status = $status;
        $agendamento->idusuario = $idusuario;
        $update = $agendamento->update();
        if(!$update)
            abort(500);
        if($status === 'Compareceu') {
            event(new CrudEvent('agendamento', 'confirmou presença', $agendamento->idagendamento));
        } else {
            event(new CrudEvent('agendamento', 'confirmou falta', $agendamento->idagendamento));
        }
        return Redirect::back()
            ->with('message', '<i class="icon fa fa-check"></i>Status editado com sucesso!')
            ->with('class', 'alert-success');
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
            ->paginate(25);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = Agendamento::findOrFail($id);
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
            'email' => 'required|max:191',
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
        // Guarda dados no banco
        if(empty($request->input('atendente')))
            $status = $request->input('status');
        else
            $status = 'Compareceu';
        $agendamento = Agendamento::findOrFail($id);
        $agendamento->nome = $request->input('nome');
        $agendamento->email = $request->input('email');
        $agendamento->cpf = $request->input('cpf');
        $agendamento->celular = $request->input('celular');
        $agendamento->idregional = $request->input('regional');
        $agendamento->idusuario = $request->input('atendente');
        $agendamento->status = $status;
        $update = $agendamento->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('agendamento', 'editou', $agendamento->idagendamento));
        return redirect('/admin/agendamentos')
            ->with('message', '<i class="icon fa fa-check"></i>Agendamento editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function reenviarEmail($id)
    {
        $resultado = Agendamento::findOrFail($id);
        // Mensagem do email
        $agradece = "<strong>Seu atendimento foi agendado com sucesso!</strong>";
        $agradece .= "<br>";
        $agradece .= "Por favor, compareça ao escritório do CORE-SP com no mínimo 15 minutos de antecedência e com o número de protocolo em mãos.";
        $agradece .= "<br><br>";
        $agradece .= "<strong>Protocolo:</strong> ".$resultado->protocolo;
        $agradece .= "<br><br>";
        $agradece .= "<strong>Detalhes do agendamento</strong><br>";
        $agradece .= "Nome: ".$resultado->nome."<br>";
        $agradece .= "CPF: ".$resultado->cpf."<br>";
        $agradece .= "Dia: ".Helper::onlyDate($resultado->dia)."<br>";
        $agradece .= "Horário: ".$resultado->hora."<br>";
        $agradece .= "Cidade: ".$resultado->regional->regional."<br>";
        $agradece .= "Endereço: ".$resultado->regional->endereco.", ".$resultado->regional->numero;
        $agradece .= " - ".$resultado->regional->complemento."<br>";
        $agradece .= "Serviço: ".$resultado->tiposervico.'<br>';
        // Manda o email
        Mail::to($resultado->email)->send(new AgendamentoMailGuest($agradece));
        return redirect('/admin/agendamentos')
            ->with('message', '<i class="icon fa fa-check"></i>Email enviado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function pendentes()
    {
        ControleController::autoriza($this->class, 'index');
        $now = date('Y-m-d');
        if(session('idperfil') === 6 || session('idperfil') === 1) {
            $resultados = Agendamento::where('dia','<',$now)
                ->whereNull('status')
                ->orderBy('dia','DESC')
                ->paginate(10);
        } elseif(session('idperfil') === 12 ) {
            $resultados = Agendamento::where('dia','<',$now)
                ->where('idregional',1)
                ->whereNull('status')
                ->orderBy('dia','DESC')
                ->paginate(10);
        } elseif(session('idperfil') === 13) {
            $resultados = Agendamento::where('dia','<',$now)
                ->where('idregional','!=',1)
                ->whereNull('status')
                ->orderBy('dia','DESC')
                ->paginate(10);
        } elseif(session('idperfil') === 8) {
            $resultados = Agendamento::where('dia','<',$now)
                ->where('idregional','=',Auth::user()->idregional)
                ->whereNull('status')
                ->orderBy('dia','DESC')
                ->paginate(10);
        } else {
            abort(401);
        }
        if($resultados->isEmpty()) {
            $resultados = [];
        }
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = $this->variaveis;
        $variaveis['continuacao_titulo'] = 'pendentes de validação';
        $variaveis['plural'] = 'agendamentos pendentes';
        $variaveis['btn_criar'] = '<a class="btn btn-primary" href="/admin/agendamentos">Lista de Agendamentos</a>';
        $variaveis = (object) $variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }
}
