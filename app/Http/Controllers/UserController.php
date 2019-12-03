<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Regional;
use App\Perfil;
use App\Http\Controllers\ControleController;
use App\Http\Controllers\Helper;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class UserController extends Controller
{
    // Nome da Classe
    private $class = 'UserController';
    // Variáveis extras da página
    public $variaveis = [
        'singular' => 'usuario',
        'singularTexto' => 'usuário',
        'singulariza' => 'o usuário',
        'plural' => 'usuarios',
        'pluralTexto' => 'usuários',
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
        $resultados = User::leftJoin('sessoes', 'sessoes.idusuario', '=', 'users.idusuario')
            ->select('users.idusuario','nome','email','idregional','idperfil')
            ->orderBy('sessoes.updated_at','DESC')
            ->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Nome / E-mail',
            'Último acesso',
            'Perfil',
            'Seccional',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            if(ControleController::mostraStatic(['1']) === true) {
                $acoes = '<a href="/admin/usuarios/editar/'.$resultado->idusuario.'" class="btn btn-sm btn-primary">Editar</a> ';
                $acoes .= '<form method="POST" action="/admin/usuarios/apagar/'.$resultado->idusuario.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir o usuário?\')" />';
                $acoes .= '</form>';
            } else {
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            }
            if(isset($resultado->perfil->nome))
                $perfil = $resultado->perfil->nome;
            else
                $perfil = 'Nenhum perfil associado';
            if(isset($resultado->sessao)) {
                $acesso = '<span style="white-space:nowrap;">';
                $acesso .= Helper::formataData($resultado->sessao->updated_at);
                $acesso .= '</span>';
                $acesso .= '<br />IP: ';
                $acesso .= $resultado->sessao->ip_address;
            } else {
                $acesso = '29/08/1997';
            }
            $conteudo = [
                $resultado->idusuario,
                $resultado->nome.'<br /><span style="white-space:nowrap;">'.$resultado->email.'</span>',
                $acesso,
                $perfil,
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
    
    public function index()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        if(ControleController::mostraStatic(['1']) !== true)
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        ControleController::autorizaStatic(['1']);
        $variaveis = (object) $this->variaveis;
        $regionais = Regional::all();
        $perfis = Perfil::all();
        return view('admin.crud.criar', compact('variaveis', 'regionais', 'perfis'));
    }

    public function store(Request $request)
    {
        ControleController::autorizaStatic(['1']);
        $regras = [
            'nome' => 'required|max:191',
            'email' => 'email|required',
            'password' => 'required|confirmed|min:6|max:24',
            'username' => 'unique:users'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'password.min' => 'A senha precisa ter no mínimo 6 caracteres.',
            'password.confirmed' => 'As senhas precisam ser idênticas entre si.',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'unique' => ':attribute indisponível',
        ];
        $erros = $request->validate($regras, $mensagens);

        $save = User::create([
            'nome' => request('nome'),
            'email' => request('email'),
            'username' => request('username'),
            'idperfil' => request('idperfil'),
            'idregional' => request('idregional'),
            'password' => Hash::make(request('password'))
        ]);

        if(!$save)
            abort(500);
        event(new CrudEvent('usuário', 'criou', $save->idusuario));
        return redirect('/admin/usuarios')
            ->with('message', '<i class="icon fa fa-check"></i>Usuário cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        ControleController::autorizaStatic(['1']);
        $resultado = User::findOrFail($id);
        $regionais = Regional::all();
        $perfis = Perfil::all();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis', 'regionais', 'perfis'));
    }

    public function update(Request $request, $id)
    {
        ControleController::autorizaStatic(['1']);
        $usuario = User::findOrFail($id);
        $regras = [
            'nome' => 'required|max:191',
            'email' => 'email|unique:users,email,'.$usuario->email.',email|required',
            'username' => 'unique:users,username,'.$usuario->idusuario.',idusuario'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'unique' => ':attribute indisponível',
        ];
        $erros = $request->validate($regras, $mensagens);

        $update = $usuario->update(request([
            'nome', 'email', 'username', 'idperfil', 'idregional'
        ]));

        if(!$update)
            abort(500);
        event(new CrudEvent('usuário', 'editou', $id));
        return redirect('/admin/usuarios')
            ->with('message', '<i class="icon fa fa-check"></i>Usuário editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy(Request $request, $id)
    {
        ControleController::autorizaStatic(['1']);
        $usuario = User::findOrFail($id);
        $delete = $usuario->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('usuário', 'apagou', $usuario->idusuario));
        return redirect()->route('usuarios.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Usuário deletado com sucesso!')
            ->with('class', 'alert-danger');

    }

    public function lixeira(Request $request)
    {
        ControleController::autorizaStatic(['1']);
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
        ControleController::autorizaStatic(['1']);
        $usuario = User::onlyTrashed()->findOrFail($id);
        $restore = $usuario->restore();
        if(!$restore)
            abort(500);
        event(new CrudEvent('usuário', 'restaurou', $usuario->idusuario));
        return redirect()->route('usuarios.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Usuário restaurado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function senha()
    {
        return view('admin.perfil.senha');
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
            $obj_user = User::findOrFail($user_id);
            $obj_user->password = Hash::make($request->input('password'));
            $save = $obj_user->save();
            if(!$save)
                abort(500);
            event(new CrudEvent('perfil', 'alterou senha', $obj_user->idusuario));
            return redirect()->route('admin.info')
                ->with('message', '<i class="icon fa fa-check"></i>Senha alterada com sucesso!')
                ->with('class', 'alert-success');
        } else {
            return redirect()->route('admin.info')
                ->with('message', '<i class="icon fa fa-ban"></i>A senha atual digitada está incorreta!')
                ->with('class', 'alert-danger');
        }
    }

    public function infos()
    {
        return view('admin.perfil.home');
    }

    public function busca()
    {
        ControleController::autoriza($this->class, 'index');
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = User::where('nome','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
