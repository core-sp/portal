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
        'titulo_criar' => 'Cadastrar perfil',
        'btn_criar' => '<a href="/admin/usuarios/perfis/criar" class="btn btn-primary mr-1">Novo Perfil</a>',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function resultados()
    {
        $resultados = Perfil::withCount('user')
            ->orderBy('created_at','DESC')
            ->paginate(10);
        return $resultados;
    }

    public function count($idperfil)
    {
        $count = User::where('idperfil',$idperfil)->count();
        return $count;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Nome',
            'Nº de Usuários',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $resultados = $this->resultados();
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/usuarios/perfis/editar/'.$resultado->idperfil.'" class="btn btn-sm btn-primary">Editar Permissões</a> ';
            $acoes .= '<form method="POST" action="/admin/usuarios/perfis/apagar/'.$resultado->idperfil.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'CUIDADO! Isto pode influenciar diretamente no funcionamento do Portal. Tem certeza que deseja excluir o perfil?\')" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado->idperfil,
                $resultado->nome,
                $this->count($resultado->idperfil),
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
        ControleController::autorizacao([
            'Admin'
        ]);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Request $request)
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $regras = [
            'nome' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $perfil = new Perfil();
        $perfil->nome = $request->input('nome');
        $save = $perfil->save();
        if(!$save)
            abort(500);
        return redirect('/admin/usuarios/perfis')
            ->with('message', '<i class="icon fa fa-check"></i>Perfil cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy(Request $request, $id)
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $perfil = Perfil::find($id);
        $delete = $perfil->delete();
        if(!$delete)
            abort(500);
        return redirect()->route('perfis.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Perfil deletado com sucesso!')
            ->with('class', 'alert-danger');
    }
}
