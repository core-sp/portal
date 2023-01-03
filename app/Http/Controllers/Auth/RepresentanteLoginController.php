<?php

namespace App\Http\Controllers\Auth;

// use App\Representante;
use App\Rules\CpfCnpj;
// use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Input;
use App\Repositories\GerentiRepositoryInterface;
// use Illuminate\Support\Facades\Request as IlluminateRequest;
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

    // protected function verificaGerentiLogin($cpfCnpj)
    // {
    //     $cpfCnpj = apenasNumeros($cpfCnpj);
    //     $registro = Representante::where('cpf_cnpj', $cpfCnpj)->first();

    //     if(isset($registro)) {
    //         $checkGerenti = $this->gerentiRepository->gerentiChecaLogin($registro->registro_core, $cpfCnpj);

    //         if($checkGerenti === false) {
    //             return redirect()
    //                 ->route('representante.cadastro')
    //                 ->with('message', 'Desculpe, mas o cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique se todas as informações foram inseridas corretamente.')
    //                 ->withInput(IlluminateRequest::all());
    //         }
    //     }
    // }

    // protected function verificaSeAtivo($cpfCnpj)
    // {
    //     $representante = Representante::where('cpf_cnpj', '=', $cpfCnpj)->first();

    //     if(isset($representante)) {
    //         if($representante->ativo === 0) {
    //             return [
    //                 'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta.',
    //                 'class' => 'alert-warning'
    //             ];
    //         } else {
    //             return [];
    //         }
    //     } else {
    //         return [
    //             'message' => 'Login inválido.',
    //             'class' => 'alert-danger'
    //         ];
    //     }
    // }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $this->verificou = $this->service->getService('Representante')->verificaAtivoAndGerenti($request->cpf_cnpj, $this->gerentiRepository);

        if($this->attemptLogin($request))
            return $this->sendLoginResponse($request);
        
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);

        // $cpfCnpj = apenasNumeros($request->cpf_cnpj);

        // $request->request->set('cpf_cnpj', $cpfCnpj);

        // $this->validate($request, [
        //     'cpf_cnpj' => ['required', new CpfCnpj],
        //     'password' => 'required'
        // ], [
        //     'required' => 'Campo obrigatório'
        // ]);

        // if(!empty($this->verificaSeAtivo($cpfCnpj)))
        //     return $this->redirectWithErrors($request->only('cpf_cnpj', 'remember'), $this->verificaSeAtivo($cpfCnpj)['message'], $this->verificaSeAtivo($cpfCnpj)['class']);

        //     $this->verificaGerentiLogin($request->cpf_cnpj);

        // if (Auth::guard('representante')->attempt([
        //     'cpf_cnpj' => $cpfCnpj,
        //     'password' => $request->password
        // ], $request->remember)) {
        //     return redirect()->intended(route('representante.dashboard'));
        // }
        
        // return $this->redirectWithErrors($request->only('cpf_cnpj', 'remember'));
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

        $this->validate($request, [
            'cpf_cnpj' => ['required', new CpfCnpj],
            'password' => 'required'
            ], [
            'required' => 'Campo obrigatório'
        ]);
    }

    protected function throttleKey(Request $request)
    {
        return $request->_token.'|'.$request->ip();
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

    // protected function redirectWithErrors($withInput, $message = 'Login inválido.', $class = 'alert-danger')
    // {
    //     return redirect()
    //         ->back()
    //         ->with([
    //             'message' => $message,
    //             'class' => $class
    //         ])->withInput($withInput);
    // }

    // public function logout(Request $request)
    // {
    //     Auth::guard('representante')->logout();

    //     $request->session()->invalidate();

    //     return redirect('/');
    // }
}
