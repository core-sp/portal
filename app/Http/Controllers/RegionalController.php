<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Regional;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Request as IlluminateRequest;

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
        $resultado = Regional::findOrFail($id);
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
            'bairro' => 'required|max:191',
            'numero' => 'required|max:191',
            'cep' => 'required|max:191',
            'telefone' => 'required|max:191',
            'funcionamento' => 'required|max:191',
            'ageporhorario' => 'required|regex:/^[1-9]+$/',
            'descricao' => 'required',
            'complemento' => 'max:191',
            'fax' => 'max:191',
            'responsavel' => 'max:191'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'ageporhorario.regex' => 'O valor deve ser maior que 0'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Inputa dados no BD
        $update = Regional::findOrFail($id)->update(request([
            'cidade', 'email', 'endereco', 'bairro', 'numero', 'complemento',
            'cep', 'telefone', 'fax', 'funcionamento', 'ageporhorario',
            'responsavel', 'descricao'
        ]));
        
        if(!$update)
            abort(500);
        event(new CrudEvent('regional', 'editou', $id));
        return redirect('/admin/regionais')
            ->with('message', '<i class="icon fa fa-check"></i>Regional editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Regional::where('regional','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}