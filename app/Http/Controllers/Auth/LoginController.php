<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Sessao;
use App\Permissao;
use App\Events\LoginEvent;
use Session;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        logout as performLogout;
    }

    protected $redirectTo = '/admin';

    protected $username;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->username = $this->findUsername();
    }

    public function findUsername()
    {
        $login = request()->input('login');
 
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
 
        request()->merge([$fieldType => $login]);
 
        return $fieldType;
    }

    public function username()
    {
        return $this->username;
    }

    public function logout(Request $request)
    {
        $this->performLogout($request);
        Session::flush();
        return redirect()->route('admin');
    }

    protected function authenticated(Request $request, $user)
    {
        $this->setUserSession();
        $this->saveSession($request, $user);
    }

    protected function permissoes()
    {
        $user = Auth::user();
        $idperfil = $user->perfil()->first()->idperfil;
        $permissao = Permissao::all();
        $array = $permissao->toArray();
        $permissoes = [];
        
        foreach($array as $a) {

            $perfis = explode(',', $a['perfis']);

            if(in_array($idperfil, $perfis)) {
                $cm = $a['controller'].'_'.$a['metodo'];
                $perfis = $a['perfis'];
                array_push($permissoes, $cm);
            }
        }
        return $permissoes;
    }

    protected function setUserSession()
    {
        $user = Auth::user();
        $perfil = $user->perfil()->first()->nome;
        $idperfil = $user->perfil()->first()->idperfil;
        $idusuario = $user->idusuario;
        $idregional = $user->idregional;
        $email = $user->email;
        $nome = $user->nome;
        session([
            'perfil' => $perfil,
            'idperfil' => $idperfil,
            'idusuario' => $idusuario,
            'idregional' => $idregional,
            'email' => $email,
            'nome' => $nome,
            'permissoes' => $this->permissoes()
        ]);
    }

    protected function saveSession($request)
    {
        $id = Auth::id();
        $checa = Sessao::where('idusuario',$id)->first();
        if(!isset($checa)) {
            $sessao = new Sessao();
            $sessao->idusuario = $id;
            $sessao->ip_address = $request->ip();
            $save = $sessao->save();
            if(!$save)
                abort(500);
        } else {
            if($checa->ip_address == $request->ip()) {
                $update = $checa->touch();
            } else {
                $checa->ip_address = $request->ip();
                $update = $checa->update();
            }
            if(!$update)
                abort(500);
        }
    }
}
