<?php

namespace App\Http\Controllers\Auth;

use App\PreRepresentante;
use App\Http\Requests\PreRepresentanteRequest;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class PreRepresentanteForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest:pre_representante');
    }

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email-prerepresentante');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        
        $cpf_cnpj = apenasNumeros($request->only('cpf_cnpj'));
        $arrayCC = [
            'cpf_cnpj' => $cpf_cnpj
        ];
        
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink($arrayCC);

        $email = $this->getEmail($cpf_cnpj);
        
        if($response == Password::RESET_LINK_SENT)
        {
            event(new ExternoEvent('Usuário com o cpf/cnpj ' .$request->cpf_cnpj. ' solicitou o envio de link para alterar a senha no Pré-registro.'));
            return $this->sendResetLinkResponse($request, 'O link de reconfiguração de senha foi enviado ao email ' . $email . '<br>Esse link é válido por 60 minutos');
        }

        return $this->sendResetLinkFailedResponse($request, $response);
    }

    protected function validateEmail(Request $request)
    {
        $pre = new PreRepresentanteRequest();
        $requestRules = new Request($pre->rules());
        $requestMessages = new Request($pre->messages());
        $this->validate(
            $request,
            $requestRules->all(),
            $requestMessages->all()
        );
    }

    protected function broker()
    {
        return Password::broker('pre_representantes');
    }

    protected function getEmail($cpfCnpj)
    {
        $first = PreRepresentante::where('cpf_cnpj', $cpfCnpj)->first();
        
        if(isset($first))
            return $first->email;
        return redirect()->back()->with([
            'message' => 'CPF ou CNPJ não cadastrado.',
            'class' => 'alert-danger'
        ]);
    }
}
