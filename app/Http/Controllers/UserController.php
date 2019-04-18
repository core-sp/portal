<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\User;
use App\Perfil;
use App\Regional;

class UserController extends Controller
{
    // Variáveis extras da página
    public $variaveis = [
        'singular' => 'usuario',
        'singulariza' => 'o usuário',
        'plural' => 'usuarios',
        'pluraliza' => 'usuários',
        'titulo_criar' => 'Cadastrar usuário',
        'btn_criar' => '<a href="/admin/usuarios/criar" class="btn btn-primary mr-1">Novo Usuário</a>',
        'btn_lixeira' => '<a href="/admin/usuarios/lixeira" class="btn btn-warning">Usuários Deletados</a>',
        'btn_lista' => '<a href="/admin/usuarios" class="btn btn-primary">Lista de Usuários</a>',
        'titulo' => 'Usuários Deletados',
        'cancela_idusuario' => true
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function resultados()
    {
        $resultados = User::orderBy('idusuario','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Nome',
            'E-mail',
            'Perfil',
            'Seccional',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/usuarios/editar/'.$resultado->idusuario.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/usuarios/apagar/'.$resultado->idusuario.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir o usuário?\')" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado->idusuario,
                $resultado->nome,
                $resultado->email,
                $resultado->perfil[0]->nome,
                $resultado->regional->regional,
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
    
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $perfis = Perfil::all();
        $variaveis = (object) $this->variaveis;
        $regionais = Regional::all();
        return view('admin.crud.criar', compact('variaveis', 'perfis', 'regionais'));
    }

    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'nome' => 'required',
            'email' => 'email|required',
            'password' => 'required|confirmed|min:6'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'password.min' => 'A senha precisa ter no mínimo 6 caracteres.',
            'password.confirmed' => 'As senhas precisam ser idênticas entre si.',
        ];
        $erros = $request->validate($regras, $mensagens);

        $usuario = new User();
        $usuario->nome = $request->input('nome');
        $usuario->email = $request->input('email');
        $usuario->idregional = $request->input('idregional');
        $usuario->password = Hash::make($request->input('password'));
        $save = $usuario->save();
        if(!$save)
            abort(500);
        $usuario->perfil()->attach([$request->input('perfil')]);
        return redirect('/admin/usuarios')
            ->with('message', '<i class="icon fa fa-check"></i>Usuário cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $resultado = User::find($id);
        $perfis = Perfil::all();
        $regionais = Regional::all();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'perfis', 'variaveis', 'regionais'));
    }

    public function update(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'nome' => 'required',
            'email' => 'email|required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);

        $usuario = User::find($id);
        $usuario->nome = $request->input('nome');
        $usuario->email = $request->input('email');
        $usuario->idregional = $request->input('idregional');
        $update = $usuario->update();
        if(!$update)
            abort(500);
        $usuario->perfil()->sync([$request->input('perfil')]);
        return redirect('/admin/usuarios')
            ->with('message', '<i class="icon fa fa-check"></i>Usuário editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $usuario = User::find($id);
        $delete = $usuario->delete();
        if(!$delete)
            abort(500);
        return redirect()->route('usuarios.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Usuário deletado com sucesso!')
            ->with('class', 'alert-danger');

    }

    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin']);
        $resultados = User::onlyTrashed()->paginate(10);
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Nome',
            'E-mail',
            'Deletado em',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/usuarios/restore/'.$resultado->idusuario.'" class="btn btn-sm btn-primary">Restaurar</a>';
            $conteudo = [
                $resultado->idusuario,
                $resultado->nome,
                $resultado->email,
                Helper::formataData($resultado->deleted_at),
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $variaveis = (object) $this->variaveis;
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $usuario = User::onlyTrashed()->find($id);
        $usuario->restore();
        return redirect()->route('usuarios.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Usuário restaurado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function senha()
    {
        return view('admin.info.senha');
    }

    public function changePassword(Request $request)
    {
        $regras = [
            'current-password' => 'required',
            'password' => 'required|min:6|regex:/^[a-zA-Z0-9]+$/',
            'password_confirmation' => 'required|same:password'
        ];
        $mensagens = [
          'current-password.required' => 'Por favor, insira sua senha atual',
          'password.required' => 'Por favor, insira uma nova senha',
          'password.min' => 'A senha deve conter no mínimo 6 caracteres',
          'password.regex' => 'O formato da senha é inválido',
          'password_confirmation.same' => 'A confirmação de senha deve ser idêntica à senha'
        ];
        $erros = $request->validate($regras, $mensagens);

        $current_password = Auth::User()->password;
        if (Hash::check($request->input('current-password'), $current_password)) {
            $user_id = Auth::id();
            $obj_user = User::find($user_id);
            $obj_user->password = Hash::make($request->input('password'));
            $save = $obj_user->save();
            if(!$save)
                abort(500);
            return redirect()->route('admin.info')
                ->with('message', '<i class="icon fa fa-check"></i>Senha alterada com sucesso!')
                ->with('class', 'alert-success');
        } else {
            $error = array('current-password' => 'Por favor, insira a senha correta');
            return response()->json(array('error' => $error), 400);
        }
    }

    public function infos()
    {
        return view('admin.info.home');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = User::where('nome','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
