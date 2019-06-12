<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Sessao;
use App\Permissao;
use App\Events\LoginEvent;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        logout as performLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request)
    {
        if(session('idusuario'))
            $this->performLogout($request);
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
            if(strpos($a['perfis'], $idperfil.',') !== false) {
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
        $email = $user->email;
        $nome = $user->nome;
        session([
            'perfil' => $perfil,
            'idperfil' => $idperfil,
            'idusuario' => $idusuario,
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
