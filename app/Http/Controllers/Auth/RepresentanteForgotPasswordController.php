<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Representante;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class RepresentanteForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest:representante');
    }

    protected function broker()
    {
        return Password::broker('representantes');
    }

    protected function validateEmail(Request $request)
    {
        $this->validate(
            $request,
            ['cpf_cnpj' => 'required'],
            ['required' => 'Por favor, informe o campo de CPF/CNPJ.']
        );
    }

    protected function getEmail($cpfCnpj)
    {
        $first = Representante::where('cpf_cnpj', $cpfCnpj)->first();
        
        if(isset($first)) {
            return $first->email;
        } else {
            return redirect()
                ->back()
                ->with('message', 'CPF ou CNPJ não cadastrado.')
                ->with('class', 'alert-danger');
        }
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        
        $cpf_cnpj = apenasNumeros($request->only('cpf_cnpj'));
        $arrayCC = [
            'cpf_cnpj' => $cpf_cnpj
        ];
        
        $response = $this->broker()->sendResetLink($arrayCC);

        $email = $this->getEmail($cpf_cnpj);

        return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($request, 'O link de reconfiguração de senha foi enviado ao email ' . $email . '<br>Esse link é válido por 60 minutos')
                    : $this->sendResetLinkFailedResponse($request, $response);
    }

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email-representante');
    }
}
