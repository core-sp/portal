<?php

namespace App\Http\Controllers\Auth;

use App\PreRepresentante;
use App\Events\ExternoEvent;
use App\Http\Requests\PreRepresentanteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PreRepresentanteLoginController extends Controller
{
    use AuthenticatesUsers {
        logout as performLogout;
    }

    protected $redirectTo = '/pre-representante/home';

    public function __construct()
    {
        $this->middleware('guest:pre_representante')->except('logout');
    }

    private function verificaSeAtivo($cpf_cnpj)
    {
        $prerep = PreRepresentante::where('cpf_cnpj', $cpf_cnpj)->first();

        if(isset($prerep))
            if($prerep->ativo == 0)
                return [
                    'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta.',
                    'class' => 'alert-warning'
                ];
            else
                return [];
        else   
            return [
                'message' => 'CPF/CNPJ não encontrado.',
                'class' => 'alert-danger',
                'cpf_cnpj' => $cpf_cnpj
            ];
    }

    public function showLoginForm()
    {
        return view('auth.prerepresentante-login'); 
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

        $verificou = $this->verificaSeAtivo($request->cpf_cnpj);
        if(!empty($verificou))
        {
            event(new ExternoEvent('Usuário com o cpf/cnpj ' .$request->cpf_cnpj. ' não conseguiu logar no Pré-registro. Erro: '.$verificou['message']));
            return redirect()->back()->with($verificou);
        }

        if($this->guard()->attempt([
            'cpf_cnpj' => $request->cpf_cnpj,
            'password' => $request->password_login
        ])) 
        {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);

            event(new ExternoEvent('Usuário ' . auth()->guard('pre_representante')->user()->id . ' conectou-se à Área do Pré-registro.'));
            
            return redirect()->intended(route('prerepresentante.dashboard'));
        }
        
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        event(new ExternoEvent('Usuário com o cpf/cnpj ' .$request->cpf_cnpj. ' não conseguiu logar no Pré-registro.'));

        return redirect()->back()->with([
            'message' => 'Login inválido.',
            'class' => 'alert-danger',
            'cpf_cnpj' => $request->cpf_cnpj
        ]);
    }

    protected function validateLogin(Request $request)
    {
        $prerep = new PreRepresentanteRequest();
        $requestRules = new Request($prerep->rules());
        $request->validate([
            $requestRules->all()
        ]);
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password_login');
    }

    public function username()
    {
        return 'cpf_cnpj';
    }

    protected function guard()
    {
        return Auth::guard('pre_representante');
    }
}
