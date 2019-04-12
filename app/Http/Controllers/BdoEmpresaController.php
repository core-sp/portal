<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoEmpresa;

class BdoEmpresaController extends Controller
{
    public $variaveis = [
        'singular' => 'empresa',
        'singulariza' => 'a empresa',
        'plural' => 'empresas',
        'pluraliza' => 'empresas',
        'titulo_criar' => 'Cadastrar nova empresa',
        'form' => 'bdoempresa',
        'btn_criar' => '<a href="/admin/bdo/empresas/criar" class="btn btn-primary mr-1">Nova Empresa</a>',
        'busca' => 'bdo/empresas',
        'slug' => 'bdo/empresas'
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
        $resultados = BdoEmpresa::orderBy('idempresa', 'DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Segmento',
            'Razão Social',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/bdo/criar?empresa='.$resultado->idempresa.'" class="btn btn-sm btn-secondary">Nova Oportunidade</a> ';
            $acoes .= '<a href="/admin/bdo/empresas/editar/'.$resultado->idempresa.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/bdo/empresas/apagar/'.$resultado->idempresa.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a empresa?\')" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado->idempresa,
                $resultado->segmento,
                $resultado->razaosocial,
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
        $request->user()->autorizarPerfis(['admin']);
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
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'cnpj' => 'required|unique:bdo_empresas',
            'razaosocial' => 'required',
            'endereco' => 'required',
            'descricao' => 'required',
            'email' => 'required',
            'telefone' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'cnpj.unique' => 'Já existe uma empresa cadastrada com este CNPJ'
        ];
        $erros = $request->validate($regras, $mensagens);

        $empresa = New BdoEmpresa();
        $empresa->segmento = $request->input('segmento');
        $empresa->cnpj = $request->input('cnpj');
        $empresa->razaosocial = $request->input('razaosocial');
        $empresa->descricao = $request->input('descricao');
        $empresa->capitalsocial = $request->input('capitalsocial');
        $empresa->endereco = $request->input('endereco');
        $empresa->site = $request->input('site');
        $empresa->email = $request->input('email');
        $empresa->telefone = $request->input('telefone');
        $empresa->contatonome = $request->input('contatonome');
        $empresa->contatotelefone = $request->input('contatotelefone');
        $empresa->contatoemail = $request->input('contatoemail');
        $empresa->idusuario = $request->input('idusuario');
        $save = $empresa->save();
        if(!$save)
            abort(500);
        return redirect()->route('bdoempresas.lista');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $resultado = BdoEmpresa::find($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
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
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'cnpj' => 'required',
            'razaosocial' => 'required',
            'endereco' => 'required',
            'descricao' => 'required',
            'email' => 'required',
            'telefone' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $empresa = BdoEmpresa::find($id);
        $empresa->segmento = $request->input('segmento');
        $empresa->cnpj = $request->input('cnpj');
        $empresa->razaosocial = $request->input('razaosocial');
        $empresa->descricao = $request->input('descricao');
        $empresa->capitalsocial = $request->input('capitalsocial');
        $empresa->endereco = $request->input('endereco');
        $empresa->site = $request->input('site');
        $empresa->email = $request->input('email');
        $empresa->telefone = $request->input('telefone');
        $empresa->contatonome = $request->input('contatonome');
        $empresa->contatotelefone = $request->input('contatotelefone');
        $empresa->contatoemail = $request->input('contatoemail');
        $empresa->idusuario = $request->input('idusuario');
        $update = $empresa->update();
        if(!$update)
            abort(500);
        return redirect()->route('bdoempresas.lista');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $empresa = BdoEmpresa::find($id);
        $delete = $empresa->delete();
        if(!$delete)
            abort(500);
        return redirect()->route('bdoempresas.lista');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = BdoEmpresa::where('segmento','LIKE','%'.$busca.'%')
            ->orWhere('razaosocial','LIKE','%'.$busca.'%')
            ->orWhere('cnpj','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
