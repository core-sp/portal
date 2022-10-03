<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\UserExternoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Contracts\MediadorServiceInterface;

class UserExternoLoginController extends Controller
{
    use AuthenticatesUsers;

    private $service;
    private $verificou;
    protected $redirectTo = '/externo/home';

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('guest:user_externo')->except('logout');
        $this->service = $service;
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

        $this->verificou = $this->service->getService('UserExterno')->verificaSeAtivo($request->cpf_cnpj);

        if($this->attemptLogin($request))
            return $this->sendLoginResponse($request);
        
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

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
        $user_externo = new UserExternoRequest();
        $requestRules = Request::createFrom($request, $user_externo);
        $requestRules->validate($requestRules->rules(), $requestRules->messages());
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
            'cpf_cnpj' => apenasNumeros($request->cpf_cnpj),
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

    public function username()
    {
        return 'cpf_cnpj';
    }

    protected function guard()
    {
        return Auth::guard('user_externo');
    }
}
