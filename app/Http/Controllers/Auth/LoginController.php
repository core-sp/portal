<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Sessao;
use App\Events\LoginEvent;
use Session;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

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

    protected function hasTooManyLoginAttempts(Request $request)
    {
        $maxAttempts = 3;
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request), $maxAttempts
        );
    }

    protected function validateLogin(Request $request)
    {
        if($request->filled('email_system')){
            $ip = "[IP: " . request()->ip() . "] - ";
            \Log::channel('interno')->info($ip . 'Possível bot tentou login com username "' . $request->login . '", mas impedido de verificar o usuário no banco de dados.');
            throw ValidationException::withMessages([
                'email_system' => 'error',
            ]);
        }

        $this->validate($request, [
            'login' => 'required',
            'password' => 'required'
            ], [
            'required' => 'Campo obrigatório'
        ]);
    }

    protected function throttleKey(Request $request)
    {
        return $request->_token.'|'.$request->ip();
    }

    protected function authenticated(Request $request, $user)
    {
        $this->saveSession($request, $user);
    }

    public function decayMinutes()
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 10;
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
