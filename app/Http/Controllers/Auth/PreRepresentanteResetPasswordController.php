<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\ExternoEvent;
use App\Http\Requests\PreRepresentanteRequest;

class PreRepresentanteResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/pre-representante/home';

    public function __construct()
    {
        $this->middleware('guest:pre_representante');
    }

    protected function resetPassword($prerep, $password)
    {
        $pre = $prerep->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        if(!$pre)
            event(new ExternoEvent('Usuário do Pré-registro ' . $prerep->id . ' não conseguiu alterar a senha.'));
    }

    protected function guard()
    {
        return Auth::guard('pre_representante');
    }

    protected function broker()
    {
        return Password::broker('pre_representantes');
    }

    protected function rules()
    {
        $pre = new PreRepresentanteRequest();
        $request = new Request($pre->rules());
        return $request->all();
    }

    protected function validationErrorMessages()
    {
        $pre = new PreRepresentanteRequest();
        $request = new Request($pre->messages());
        return $request->all();
    }

    public function getEmailForPasswordReset()
    {
        return $this->cpf_cnpj;
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

    protected function sendResetResponse(Request $request, $response)
    {
        event(new ExternoEvent('Usuário do Pré-registro com o cpf/cnpj ' .$request->cpf_cnpj. ' alterou a senha com sucesso.'));

        return redirect(route('prerepresentante.login'))->with([
                'message' => 'Senha alterada com sucesso. Favor realizar o login novamente com as novas informações.',
                'class' => 'alert-success'
            ]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset-prerepresentante')->with([
            'token' => $token, 
            'cpf_cnpj' => $request->cpf_cnpj
        ]);
    }
}
