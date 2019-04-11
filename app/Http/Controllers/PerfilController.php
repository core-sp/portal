<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Perfil;
use App\User;

class PerfilController extends Controller
{
    public $variaveis = [
        'singular' => 'perfil',
        'singulariza' => 'o perfil',
        'plural' => 'perfis',
        'pluraliza' => 'perfis',
        'titulo_criar' => 'Cadastrar novo perfil',
        'btn_criar' => '<a href="/admin/usuarios/perfis/criar" class="btn btn-primary mr-1">Novo Perfil</a>',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function resultados()
    {
        $resultados = Perfil::paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Nome',
            'Descrição',
            'Usuários',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            // Ações possíveis com cada resultado
            $acoes = '<a href="/admin/usuarios/perfis/editar/'.$resultado->idperfil.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/usuarios/perfis/apagar/'.$resultado->idperfil.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'CUIDADO! Esta ação pode influenciar diretamente no funcionamento do sistema. Tem certeza que deseja excluir o perfil?\')" />';
            $acoes .= '</form>';
            //Conta número de atendentes
            $nome = $resultado->nome;
            $count = User::whereHas('perfil', function($q) use($nome) {
                $q->where('nome', $nome);
            })->count();
            // Mostra dados na tabela
            $conteudo = [
                $resultado->idperfil,
                $nome,
                $resultado->descricao,
                $count,
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
        $request->user()->autorizarPerfis(['admin']);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'nome' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);

        $pagina = new Perfil();
        $pagina->nome = $request->input('nome');
        $pagina->descricao = $request->input('descricao');
        $save = $pagina->save();
        if(!$save)
            abort(500);
        return redirect('/admin/usuarios/perfis');
    }

    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $resultado = Perfil::find($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'nome' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);

        $pagina = Perfil::find($id);
        $pagina->nome = $request->input('nome');
        $pagina->descricao = $request->input('descricao');
        $update = $pagina->update();
        if(!$update)
            abort(500);
        return redirect('/admin/usuarios/perfis');
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $perfil = Perfil::find($id);
        $delete = $perfil->delete();
        if(!$delete)
            abort(500);
        return redirect('/admin/usuarios/perfis');
    }

}
