<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Sessao;
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

    private function findUsername()
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
        $this->saveSession($request, $user);
    }

    private function saveSession(Request $request, $user)
    {
        try{
            $check = Sessao::where('idusuario', $user->idusuario)->first();
            if(!isset($check)) {
                $sessao = new Sessao();
                $sessao->idusuario = $user->idusuario;
                $sessao->ip_address = $request->ip();
                $save = $sessao->save();
                if(!$save)
                    abort(500);
            } else {
                if($check->ip_address == $request->ip()) {
                    $update = $check->touch();
                } else {
                    $check->ip_address = $request->ip();
                    $update = $check->update();
                }
                if(!$update)
                    abort(500);
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao configurar a sessão no banco.");
        }
    }
}
