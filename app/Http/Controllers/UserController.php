<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Perfil;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $usuarios = User::paginate(10);
        return view('admin.usuarios.home', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $perfis = Perfil::all();
        return view('admin.usuarios.criar', compact('perfis'));
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
        $usuario->password = Hash::make($request->input('password'));
        $usuario->save();
        $usuario->perfil()->attach([$request->input('perfil')]);
        return redirect('/admin/usuarios');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $usuario = User::find($id);
        $perfis = Perfil::all();
        return view('admin.usuarios.editar', compact('usuario', 'perfis'));
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
        $usuario->update();
        $usuario->perfil()->sync([$request->input('perfil')]);
        return redirect('/admin/usuarios');
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
        $usuario = User::find($id);
        $usuario->delete();
        return redirect()->route('usuarios.lista');

    }

    /**
     * Mostra a lixeira de usuários
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $usuarios = User::onlyTrashed()->get();
        return view('/admin/usuarios/lixeira', compact('usuarios'));
    }

    /**
     * Restaura usuário deletado
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $usuario = User::onlyTrashed()->find($id);
        $usuario->restore();
        return redirect()->route('usuarios.lista');
    }

    /**
     * Muda a senha de determinado usuário
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function senha()
    {
        return view('admin.info.senha');
    }

    public function changePassword(Request $request)
    {
        $regras = [
            'current-password' => 'required',
            'password' => 'required|same:password',
            'password_confirmation' => 'required|same:password'
        ];
        $mensagens = [
          'current-password.required' => 'Por favor, insira sua senha atual',
          'password.required' => 'Por favor, insira uma nova senha',
        ];
        $erros = $request->validate($regras, $mensagens);

        $current_password = Auth::User()->password;
        if (Hash::check($request->input('current-password'), $current_password)) {
            $user_id = Auth::id();
            $obj_user = User::find($user_id);
            $obj_user->password = Hash::make($request->input('password'));
            $obj_user->save();
            return redirect()->route('admin.info');
        } else {
            $error = array('current-password' => 'Por favor, insira a senha correta');
            return response()->json(array('error' => $error), 400);
        }
    }

    public function infos()
    {
        return view('admin.info.home');
    }
}
