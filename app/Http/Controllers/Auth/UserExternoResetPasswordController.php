<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\PasswordReset;
use App\Http\Requests\UserExternoRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;
use App\Contracts\MediadorServiceInterface;

class UserExternoResetPasswordController extends Controller
{
    use ResetsPasswords;

    private $service;
    private $tipo;
    protected $redirectTo = '/externo/home';

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('guest:user_externo');
        $this->middleware('guest:contabil');
        $this->service = $service;
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset-user-externo')->with([
            'token' => $token, 
            'cpf_cnpj' => $request->cpf_cnpj
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());
        $this->tipo = $this->service->getService('UserExterno')->getDefinicoes($request->tipo_conta);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request, $response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    protected function rules()
    {
        $user_externo = new UserExternoRequest();
        $request = new Request($user_externo->rules());
        return $request->all();
    }

    protected function validationErrorMessages()
    {
        $user_externo = new UserExternoRequest();
        $request = new Request($user_externo->messages());
        return $request->all();
    }

    protected function credentials(Request $request)
    {
        return [
            $this->tipo['campo'] => apenasNumeros($request->cpf_cnpj),
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
            'token' => $request->token
        ];
    }

    protected function resetPassword($user_externo, $password)
    {
        $user_externo->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user_externo));
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return redirect(route('externo.login'))->with([
                'message' => 'Senha alterada com sucesso. Favor realizar o login novamente com as novas informações.',
                'class' => 'alert-success'
            ]);
    }

    // Caso o token seja inválido ou o usuário, ele devolve o aviso no campo cpf_cnpj
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return redirect()->back()
                    ->withInput($request->only('cpf_cnpj'))
                    ->withErrors(['cpf_cnpj' => trans($response)]);
    }

    protected function broker()
    {
        return Password::broker($this->tipo['tabela']);
    }

    protected function guard()
    {
        return Auth::guard($this->tipo['tipo']);
    }
}
