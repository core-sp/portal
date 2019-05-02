<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoOportunidade;
use App\BdoEmpresa;
use App\Regional;
use App\Http\Controllers\ControleControllers;

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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function resultados()
    {
        $resultados = BdoOportunidade::orderBy('idoportunidade', 'DESC')->paginate(10);
        return $resultados;
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
            $acoes = '<a href="/admin/bdo/editar/'.$resultado->idoportunidade.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/bdo/apagar/'.$resultado->idoportunidade.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a oportunidade?\')" />';
            $acoes .= '</form>';
            if(isset($resultado->vagaspreenchidas))
                $relacaovagas = $resultado->vagaspreenchidas.' / '.$resultado->vagasdisponiveis;
            else
                $relacaovagas = 'X / '.$resultado->vagasdisponiveis;
            $conteudo = [
                $resultado->idoportunidade,
                $resultado->empresa->razaosocial,
                $resultado->segmento,
                $relacaovagas,
                $resultado->status,
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

    public function index()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $id = Input::get('empresa');
        $empresa = BdoEmpresa::find($id);
        $regioes = Regional::all();
        if (isset($empresa)) {
            $variaveis = (object) $this->variaveis;
            return view('admin.crud.criar', compact('empresa', 'regioes', 'variaveis'));
        } else {
            abort(401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ControleController::autoriza($this->class, 'create');
        $regras = [
            'vagasdisponiveis' => 'required',
            'descricao' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $regioes = implode(',',$request->input('regiaoatuacao'));
        $oportunidade = new BdoOportunidade();
        $oportunidade->idempresa = $request->input('empresa');
        $oportunidade->titulo = $request->input('titulo');
        $oportunidade->segmento = $request->input('segmento');
        $oportunidade->regiaoatuacao = $regioes;
        $oportunidade->descricao = $request->input('descricao');
        $oportunidade->vagasdisponiveis = $request->input('vagasdisponiveis');
        $oportunidade->vagaspreenchidas = $request->input('vagaspreenchidas');
        $oportunidade->status = $request->input('status');
        if ($request->input('status') === "Em andamento") {
            $oportunidade->datainicio = now();
        } else {
            $oportunidade->datainicio = null;
        }
        $oportunidade->idusuario = $request->input('idusuario');
        $save = $oportunidade->save();
        if(!$save)
            abort(500);
        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Oportunidade cadastrada com sucesso!')
            ->with('class', 'alert-success');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = BdoOportunidade::find($id);
        $variaveis = (object) $this->variaveis;
        $regioes = Regional::all();
        $regioesEdit = explode(',', $resultado->regiaoatuacao);
        $regioesEdit = Regional::findMany(array_values($regioesEdit));
        return view('admin.crud.editar', compact('resultado', 'variaveis', 'regioes', 'regioesEdit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $regras = [
            'vagasdisponiveis' => 'required',
            'descricao' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $regioes = implode(',',$request->input('regiaoatuacao'));
        $oportunidade = BdoOportunidade::find($id);
        $oportunidade->idempresa = $request->input('empresa');
        $oportunidade->titulo = $request->input('titulo');
        $oportunidade->segmento = $request->input('segmento');
        $oportunidade->regiaoatuacao = $regioes;
        $oportunidade->descricao = $request->input('descricao');
        $oportunidade->vagasdisponiveis = $request->input('vagasdisponiveis');
        $oportunidade->vagaspreenchidas = $request->input('vagaspreenchidas');
        $oportunidade->status = $request->input('status');
        if ($oportunidade->datainicio === null && $request->input('status') === "Em andamento") {
            $oportunidade->datainicio = now();
        } else {
            $oportunidade->datainicio = null;
        }
        $oportunidade->idusuario = $request->input('idusuario');
        $update = $oportunidade->update();
        if(!$update)
            abort(500);
        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Oportunidade editada com sucesso!')
            ->with('class', 'alert-success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = BdoOportunidade::find($id);
        $delete = $resultado->delete();
        if(!$delete)
            abort(500);
        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Oportunidade deletada com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function busca()
    {
        ControleController::autoriza($this->class, 'index');
        $busca = Input::get('q');
        $oportunidades = BdoOportunidade::where('descricao','LIKE','%'.$busca.'%')
            ->paginate(10);
        if (count($oportunidades) > 0) 
            return view('admin.bdo.home', compact('oportunidades', 'busca'));
        else
            return view('admin.bdo.home')->withMessage('Nenhum curso encontrado');
    }
}
