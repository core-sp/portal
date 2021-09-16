<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\ExternoEvent;

class RepresentanteResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/representante/home';

    public function __construct()
    {
        $this->middleware('guest:representante');
    }

    protected function resetPassword($user, $password)
    {
        $u = $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        if(!$u)
            event(new ExternoEvent('Usuário ' . $user->id . ' ("'. $user->registro_core .'") não conseguiu alterar a senha.'));
    }

    protected function guard()
    {
        return Auth::guard('representante');
    }

    protected function broker()
    {
        return Password::broker('representantes');
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'cpf_cnpj' => 'required',
            'password' => 'required|confirmed|min:6',
        ];
    }

    protected function validationErrorMessages()
    {
        return [
            'required' => 'Campo obrigatório',
            'confirmed' => 'A senha e a confirmação precisam ser idênticas',
            'password.min' => 'A senha precisa ter no mínimo 6 caracteres'
        ];
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
        event(new ExternoEvent('Usuário com o cpf/cnpj ' .$request->cpf_cnpj. ' alterou a senha com sucesso.'));

        return redirect()
            ->route('representante.login')
            ->with([
                'message' => 'Senha alterada com sucesso. Favor realizar o login novamente com as novas informações.',
                'class' => 'alert-success'
            ]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset-representante')->with(
            ['token' => $token, 'cpf_cnpj' => $request->cpf_cnpj]
        );
    }
}
