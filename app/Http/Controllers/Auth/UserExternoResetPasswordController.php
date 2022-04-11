<?php

namespace App\Http\Controllers\Auth;

use App\Events\ExternoEvent;
use App\Http\Requests\UserExternoRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;

class UserExternoResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/externo/home';

    public function __construct()
    {
        $this->middleware('guest:user_externo');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset-user-externo')->with([
            'token' => $token, 
            'cpf_cnpj' => $request->cpf_cnpj
        ]);
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
            'cpf_cnpj' => apenasNumeros($request->cpf_cnpj),
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
            'token' => $request->token
        ];
    }

    protected function resetPassword($user_externo, $password)
    {
        $externo = $user_externo->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        if(!$externo)
            event(new ExternoEvent('Usuário do Login Externo ' . $user_externo->id . ' não conseguiu alterar a senha.'));
    }

    protected function sendResetResponse(Request $request, $response)
    {
        event(new ExternoEvent('Usuário do Login Externo com o cpf/cnpj ' .$request->cpf_cnpj. ' alterou a senha com sucesso.'));

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
        return Password::broker('users_externo');
    }

    protected function guard()
    {
        return Auth::guard('user_externo');
    }
}
