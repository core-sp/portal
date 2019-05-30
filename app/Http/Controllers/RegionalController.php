<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use App\Regional;
use App\Noticia;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;

class RegionalController extends Controller
{
    // Nome da classe
    private $class = 'RegionalController';
    // Variáveis extras da página
    public $variaveis = [
        'singular' => 'regional',
        'singulariza' => 'a regional',
        'plural' => 'regionais',
        'pluraliza' => 'regionais'
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function resultados()
    {
        $resultados = Regional::paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Regional',
            'Telefone',
            'Email',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/seccional/'.$resultado->idregional.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if(ControleController::mostra($this->class, 'edit'))
                $acoes .= '<a href="/admin/regionais/editar/'.$resultado->idregional.'" class="btn btn-sm btn-primary">Editar</a>';
            $conteudo = [
                $resultado->idregional,
                $resultado->prefixo.' - '.$resultado->regional,
                $resultado->telefone,
                $resultado->email,
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
    
    public function index()
    {
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function edit(Request $request, $id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = Regional::find($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $regras = [
            'cidade' => 'required|max:191',
            'email' => 'required|max:191',
            'endereco' => 'required|max:191',
            'numero' => 'required|max:191',
            'cep' => 'required|max:191',
            'telefone' => 'required|max:191',
            'funcionamento' => 'required|max:191',
            'descricao' => 'required',
            'complemento' => 'max:191',
            'fax' => 'max:191',
            'responsavel' => 'max:191'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Inputa dados no BD
        $regional = Regional::find($id);
        $regional->regional = $request->input('cidade');
        $regional->email = $request->input('email');
        $regional->endereco = $request->input('endereco');
        $regional->numero = $request->input('numero');
        $regional->complemento = $request->input('complemento');
        $regional->cep = $request->input('cep');
        $regional->telefone = $request->input('telefone');
        $regional->fax = $request->input('fax');
        $regional->funcionamento = $request->input('funcionamento');
        $regional->responsavel = $request->input('responsavel');
        $regional->descricao = $request->input('descricao');
        $update = $regional->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('regional', 'editou', $regional->idregional));
        return redirect('/admin/regionais')
            ->with('message', '<i class="icon fa fa-check"></i>Regional editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Regional::where('regional','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}