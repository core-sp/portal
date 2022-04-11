<?php

namespace App\Http\Controllers\Auth;

use App\UserExterno;
use App\Events\ExternoEvent;
use App\Http\Requests\UserExternoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class UserExternoLoginController extends Controller
{
    use AuthenticatesUsers {
        logout as performLogout;
    }

    protected $redirectTo = '/externo/home';

    public function __construct()
    {
        $this->middleware('guest:user_externo')->except('logout');
    }

    private function verificaSeAtivo($cpf_cnpj)
    {
        $user_externo = UserExterno::where('cpf_cnpj', $cpf_cnpj)->first();

        if(isset($user_externo))
            if($user_externo->ativo == 0)
                return [
                    'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta.',
                    'class' => 'alert-warning'
                ];
            else
                return [];
        return [
            'message' => 'CPF/CNPJ não encontrado.',
            'class' => 'alert-danger',
            'cpf_cnpj' => $cpf_cnpj
        ];
    }

    public function showLoginForm()
    {
        return view('auth.user-externo-login'); 
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $verificou = $this->verificaSeAtivo(apenasNumeros($request->cpf_cnpj));
        if(!empty($verificou))
        {
            event(new ExternoEvent('Usuário com o cpf/cnpj ' .$request->cpf_cnpj. ' não conseguiu logar no Login Externo. Erro: '.$verificou['message']));
            return redirect()->back()->with($verificou);
        }

        if($this->guard()->attempt([
            'cpf_cnpj' => apenasNumeros($request->cpf_cnpj),
            'password' => $request->password
        ])) 
        {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);

            event(new ExternoEvent('Usuário ' . auth()->guard('user_externo')->user()->id . ' conectou-se à Área do Login Externo.'));
            
            return redirect()->intended(route('externo.dashboard'));
        }
        
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        event(new ExternoEvent('Usuário com o cpf/cnpj ' .$request->cpf_cnpj. ' não conseguiu logar no Login Externo.'));

        return redirect()->back()->with([
            'message' => 'Login inválido.',
            'class' => 'alert-danger',
            'cpf_cnpj' => apenasNumeros($request->cpf_cnpj)
        ]);
    }

    protected function validateLogin(Request $request)
    {
        $user_externo = new UserExternoRequest();
        $requestRules = Request::createFrom($request, $user_externo);
        $requestRules->validate($requestRules->rules(), $requestRules->messages());
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    public function username()
    {
        return 'cpf_cnpj';
    }

    protected function guard()
    {
        return Auth::guard('user_externo');
    }
}
