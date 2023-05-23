<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\UserExternoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Validation\ValidationException;

class UserExternoLoginController extends Controller
{
    use AuthenticatesUsers;

    private $service;
    private $verificou;
    private $tipo;
    protected $redirectTo = '/externo/home';

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('guest:user_externo')->except('logout');
        $this->middleware('guest:contabil')->except('logout');
        $this->service = $service;
    }

    public function showLoginForm()
    {
        return view('auth.user-externo-login'); 
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);
        $this->tipo = $this->service->getService('UserExterno')->getDefinicoes($request->tipo_conta);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $this->verificou = $this->service->getService('UserExterno')->verificaSeAtivo($this->tipo['tipo'], $request->cpf_cnpj);

        if($this->attemptLogin($request))
            return $this->sendLoginResponse($request);
        
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        $this->service->getService('Suporte')->bloquearIp($request->ip());

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
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
            \Log::channel('externo')->info($ip . 'Possível bot tentou login com cpf/cnpj "' . apenasNumeros($request->cpf_cnpj) . '" como Usuário Externo, mas impedido de verificar o usuário no banco de dados.');
            throw ValidationException::withMessages([
                'email_system' => 'error',
            ]);
        }

        $user_externo = new UserExternoRequest();
        $requestRules = Request::createFrom($request, $user_externo);
        $requestRules->validate($requestRules->rules(), $requestRules->messages());
    }

    protected function throttleKey(Request $request)
    {
        return $request->session()->get('_token').'|'.$request->ip();
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt([
            $this->tipo['campo'] => apenasNumeros($request->cpf_cnpj),
            'password' => $request->password,
            'ativo' => 1
        ]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended(route('externo.dashboard'));
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
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
        return $this->tipo['campo'];
    }

    protected function guard()
    {
        $guard = getGuardExterno(auth());
        if(!isset($this->tipo) && isset($guard))
            $this->tipo = $this->service->getService('UserExterno')->getDefinicoes($guard);

        return Auth::guard($this->tipo['tipo']);
    }
}
