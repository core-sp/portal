<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoOportunidade;
use App\BdoEmpresa;
use App\Regional;
use App\Http\Controllers\ControleControllers;
use App\Events\CrudEvent;

class BdoOportunidadeController extends Controller
{
    // Nome da Classe
    private $class = 'BdoOportunidadeController';
    // Variáveis
    public $variaveis = [
        'singular' => 'oportunidade',
        'singulariza' => 'a oportunidade',
        'plural' => 'oportunidade',
        'pluraliza' => 'oportunidades',
        'titulo_criar' => 'Cadastrar nova oportunidade',
        'form' => 'bdooportunidade',
        'busca' => 'bdo',
        'slug' => 'bdo'
    ];

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
    }

    public function resultados()
    {
        $resultados = BdoOportunidade::orderBy('idoportunidade', 'DESC')->paginate(10);
        return $resultados;
    }

    protected function statusNoAdmin($status)
    {
        switch ($status) {
            case 'Sob Análise':
                return '<strong><i>Sob Análise</i></strong>';
            break;

            case 'Recusado':
                return '<strong class="text-danger">Recusado</strong>';
            break;

            case 'Concluído':
                return '<strong class="text-warning">Concluído</strong>';
            break;

            case 'Em andamento':
                return '<strong class="text-success">Em andamento</strong>';
            break;
            
            default:
                return $status;
            break;
        }
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Empresa',
            'Segmento',
            'Vagas',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            if(ControleController::mostra($this->class, 'edit'))
                $acoes = '<a href="/admin/bdo/editar/'.$resultado->idoportunidade.'" class="btn btn-sm btn-primary">Editar</a> ';
            else
                $acoes = '';
            if(ControleController::mostra($this->class, 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/bdo/apagar/'.$resultado->idoportunidade.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a oportunidade?\')" />';
                $acoes .= '</form>';
            }
            if(empty($acoes))
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            if(isset($resultado->vagaspreenchidas))
                $relacaovagas = $resultado->vagaspreenchidas.' / '.$resultado->vagasdisponiveis;
            else
                $relacaovagas = 'X / '.$resultado->vagasdisponiveis;
            if(isset($resultado->empresa->razaosocial))
                $razaosocial = $resultado->empresa->razaosocial;
            else
                $razaosocial = '';
            $conteudo = [
                $resultado->idoportunidade,
                $razaosocial,
                $resultado->segmento,
                $relacaovagas,
                $this->statusNoAdmin($resultado->status),
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

    protected function regras()
    {
        return [
            'titulo' => 'required|max:191',
            'segmento' => 'max:191',
            'vagasdisponiveis' => 'required',
            'descricao' => 'required',
            'status' => 'max:191',
            'observacao' => 'max:500',
        ];
    }

    protected function mensagens()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'vagasdisponiveis.required' => 'Informe o número de vagas disponíveis',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
    }

    public function index()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $id = Input::get('empresa');
        $empresa = BdoEmpresa::findOrFail($id);
        $regioes = Regional::all();
        if (isset($empresa)) {
            $variaveis = (object) $this->variaveis;
            return view('admin.crud.criar', compact('empresa', 'regioes', 'variaveis'));
        } else {
            abort(401);
        }
    }

    public function store(Request $request)
    {
        ControleController::autoriza($this->class, 'create');
        $erros = $request->validate($this->regras(), $this->mensagens());
        $regioes = ','.implode(',',request('regiaoatuacao')).',';
        if (request('status') === "Em andamento") {
            $datainicio = now();
        } else {
            $datainicio = null;
        }

        $save = BdoOportunidade::create([
            'idempresa' => request('idempresa'),
            'titulo' => request('titulo'),
            'segmento' => request('segmento'),
            'regiaoatuacao' => $regioes,
            'descricao' => request('descricao'),
            'vagasdisponiveis' => request('vagasdisponiveis'),
            'vagaspreenchidas' => request('vagaspreenchidas'),
            'status' => request('status'),
            'observacao' => request('observacao'),
            'datainicio' => $datainicio,
            'idusuario' => request('idusuario')
        ]);

        if(!$save)
            abort(500);
        event(new CrudEvent('oportunidade (Balcão de Oportunidades)', 'criou', $save->idoportunidade));
        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Oportunidade cadastrada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = BdoOportunidade::findOrFail($id);
        $variaveis = (object) $this->variaveis;
        $regioes = Regional::all();
        $regioesEdit = explode(',', $resultado->regiaoatuacao);
        $regioesEdit = Regional::findMany(array_values($regioesEdit));
        return view('admin.crud.editar', compact('resultado', 'variaveis', 'regioes', 'regioesEdit'));
    }

    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $erros = $request->validate($this->regras(), $this->mensagens());
        $regioes = ','.implode(',',request('regiaoatuacao')).',';
        if (request('datainicio') === null && request('status') === "Em andamento") {
            $datainicio = now();
        } else {
            $datainicio = null;
        }

        $update = BdoOportunidade::findOrFail($id)->update([
            'idempresa' => request('idempresa'),
            'titulo' => request('titulo'),
            'segmento' => request('segmento'),
            'regiaoatuacao' => $regioes,
            'descricao' => request('descricao'),
            'vagasdisponiveis' => request('vagasdisponiveis'),
            'vagaspreenchidas' => request('vagaspreenchidas'),
            'status' => request('status'),
            'observacao' => request('observacao'),
            'datainicio' => $datainicio,
            'idusuario' => request('idusuario')
        ]);

        if(!$update)
            abort(500);
        event(new CrudEvent('oportunidade (Balcão de Oportunidades)', 'editou', $id));
        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Oportunidade editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = BdoOportunidade::findOrFail($id);
        $delete = $resultado->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('oportunidade (Balcão de Oportunidades)', 'apagou', $resultado->idoportunidade));
        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Oportunidade deletada com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function busca()
    {
        ControleController::autoriza($this->class, 'index');
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = BdoOportunidade::where('descricao','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
