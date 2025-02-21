<?php

namespace App\Http\Controllers\Auth;

use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\GerentiRepositoryInterface;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Validation\ValidationException;

class RepresentanteLoginController extends Controller
{
    use AuthenticatesUsers;

    private $gerentiRepository;
    private $service;
    private $verificou;
    protected $redirectTo = '/representante/home';

    public function __construct(GerentiRepositoryInterface $gerentiRepository, MediadorServiceInterface $service)
    {
        $this->middleware('guest:representante')->except('logout');
        $this->gerentiRepository = $gerentiRepository;
        $this->service = $service;
    }

    public function showLoginForm()
    {
        return view('auth.representante-login'); 
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $this->verificou = $this->service->getService('Representante')->verificaAtivoAndGerenti($request->cpf_cnpj, $this->gerentiRepository);

        if($this->attemptLogin($request)){
            $this->service->getService('Representante')->registrarUltimoAcesso($request->input('cpf_cnpj'));
            return $this->sendLoginResponse($request);
        }
        
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        $this->service->getService('Suporte')->bloquearIp($request->ip());

        return $this->sendFailedLoginResponse($request);
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
            \Log::channel('externo')->info($ip . 'Possível bot tentou login com cpf/cnpj "' . apenasNumeros($request->cpf_cnpj) . '", mas impedido de verificar o usuário no banco de dados.');
            throw ValidationException::withMessages([
                'email_system' => 'error',
            ]);
        }

        $cpfCnpj = apenasNumeros($request->cpf_cnpj);
        $request->request->set('cpf_cnpj', $cpfCnpj);
        $request->offsetUnset('remember');
        $this->validate($request, [
            'cpf_cnpj' => ['required', new CpfCnpj],
            'password' => 'required'
            ], [
            'required' => 'Campo obrigatório'
        ]);
    }

    protected function throttleKey(Request $request)
    {
        return $request->session()->get('_token').'|'.$request->ip();
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt([
            'cpf_cnpj' => apenasNumeros($request->cpf_cnpj),
            'password' => $request->password,
            'ativo' => 1
        ], $request->remember);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended(route('representante.dashboard'));
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        if(!empty($this->verificou))
            return redirect()->back()->with($this->verificou);

        return redirect()->back()->with([
            'message' => 'Login inválido.',
            'class' => 'alert-danger',
            'cpf_cnpj' => apenasNumeros($request->cpf_cnpj)
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        $this->service->getService('Suporte')->liberarIp($request->ip());
    }

    public function decayMinutes()
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 10;
    }

    public function username()
    {
        return 'cpf_cnpj';
    }

    protected function guard()
    {
        return Auth::guard('representante');
    }
}
