<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AgendamentoBloqueio;
use App\Regional;
use App\Http\Controllers\Helper;

class AgendamentoBloqueioController extends Controller
{
    public $variaveis = [
        'singular' => 'bloqueio',
        'singulariza' => 'o bloqueio',
        'plural' => 'bloqueios de agendamento',
        'pluraliza' => 'bloqueios',
        'form' => 'agendamentobloqueio',
        'titulo_criar' => 'Cadastrar novo bloqueio',
        'btn_criar' => '<a href="/admin/agendamentos/bloqueios/criar" class="btn btn-primary mr-1">Novo Bloqueio</a>',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function resultados()
    {
        $resultados = AgendamentoBloqueio::orderBy('idagendamentobloqueio')
            ->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Regional',
            'Duração',
            'Horas Bloqueadas',
            'Ações',
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            if($resultado->diainicio == '2000-01-01') {
                $duracao = 'Início: Indefinido<br />';
            } else {
                $duracao = 'Início: '.Helper::onlyDate($resultado->diainicio).'<br />';
            }
            if($resultado->diatermino == '2100-01-01') {
                $duracao .= 'Término: Indefinido';
            } else {
                $duracao .= 'Término: '.Helper::onlyDate($resultado->diatermino);
            }
            $acoes = '<a href="/admin/agendamentos/bloqueios/editar/'.$resultado->idagendamentobloqueio.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/agendamentos/bloqueios/apagar/'.$resultado->idagendamentobloqueio.'" class="d-inline-block">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar" onclick="return confirm(\'Tem certeza que deseja cancelar o bloqueio?\')" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado->idagendamentobloqueio,
                $resultado->regional->regional,
                $duracao,
                'Das '.$resultado->horainicio.' às '.$resultado->horatermino,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        // Monta e retorna tabela        
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Gestão de Atendimento']);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Gestão de Atendimento']);
        $variaveis = (object) $this->variaveis;
        $regionais = Regional::all();
        return view('admin.crud.criar', compact('variaveis', 'regionais'));
    }

    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Gestão de Atendimento']);
        $regras = [
            'horainicio' => 'required',
            'horatermino' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);
        // Formata DateTime
        if(empty($request->input('diainicio')) && empty($request->input('diatermino'))) {
            $diainicio = '2000-01-01';
            $diainicio = new \DateTime($diainicio);
            $diatermino = '2100-01-01';
            $diatermino = new \DateTime($diatermino);
        } elseif(empty($request->input('diainicio'))) {
            $diainicio = '2000-01-01';
            $diainicio = new \DateTime($diainicio);
            $diatermino = Helper::retornaDate($request->input('diatermino'));
        } elseif(empty($request->input('diatermino'))) {
            $diainicio = Helper::retornaDate($request->input('diainicio'));
            $diatermino = '2100-01-01';
            $diatermino = new \DateTime($diatermino);
        } else {
            $diainicio = Helper::retornaDate($request->input('diainicio'));
            $diatermino = Helper::retornaDate($request->input('diatermino'));
        }
        // Inputa no BD
        $bloqueio = new AgendamentoBloqueio();
        $bloqueio->diainicio = $diainicio;
        $bloqueio->diatermino = $diatermino;
        $bloqueio->horainicio = $request->input('horainicio');
        $bloqueio->horatermino = $request->input('horatermino');
        $bloqueio->idregional = $request->input('idregional');
        $bloqueio->idusuario = $request->input('idusuario');
        $save = $bloqueio->save();
        if(!$save)
            abort(500);
        return redirect()->route('agendamentobloqueios.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Bloqueio cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['Admin', 'Gestão de Atendimento']);
        $resultado = AgendamentoBloqueio::find($id);
        $variaveis = (object) $this->variaveis;
        $regionais = Regional::all();
        return view('admin.crud.editar', compact('resultado', 'variaveis', 'regionais'));
    }

    public function update(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['Admin', 'Gestão de Atendimento']);
        $regras = [
            'horainicio' => 'required',
            'horatermino' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);
        // Formata DateTime
        if(empty($request->input('diainicio')) && empty($request->input('diatermino'))) {
            $diainicio = '2000-01-01';
            $diainicio = new \DateTime($diainicio);
            $diatermino = '2100-01-01';
            $diatermino = new \DateTime($diatermino);
        } elseif(empty($request->input('diainicio'))) {
            $diainicio = '2000-01-01';
            $diainicio = new \DateTime($diainicio);
            $diatermino = Helper::retornaDate($request->input('diatermino'));
        } elseif(empty($request->input('diatermino'))) {
            $diainicio = Helper::retornaDate($request->input('diainicio'));
            $diatermino = '2100-01-01';
            $diatermino = new \DateTime($diatermino);
        } else {
            $diainicio = Helper::retornaDate($request->input('diainicio'));
            $diatermino = Helper::retornaDate($request->input('diatermino'));
        }
        // Inputa no BD
        $bloqueio = AgendamentoBloqueio::find($id);
        $bloqueio->diainicio = $diainicio;
        $bloqueio->diatermino = $diatermino;
        $bloqueio->horainicio = $request->input('horainicio');
        $bloqueio->horatermino = $request->input('horatermino');
        $bloqueio->idregional = $request->input('idregional');
        $bloqueio->idusuario = $request->input('idusuario');
        $update = $bloqueio->update();
        if(!$update)
            abort(500);
        return redirect()->route('agendamentobloqueios.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Bloqueio editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['Admin', 'Gestão de Atendimento']);
        $bloqueio = AgendamentoBloqueio::find($id);
        $delete = $bloqueio->delete();
        if(!$delete)
            abort(500);
        return redirect()->route('agendamentobloqueios.lista')
            ->with('message', '<i class="icon fa fa-danger"></i>Bloqueio cancelado com sucesso!')
            ->with('class', 'alert-danger');
    }
}
