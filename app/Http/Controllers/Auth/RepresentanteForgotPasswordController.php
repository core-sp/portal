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
        $this->validate($request, ['cpf_cnpj' => 'required']);
    }

    protected function getEmail($cpfCnpj)
    {
        $first = Representante::where('cpf_cnpj', $cpfCnpj)->first();
        
        if(isset($first)) {
            return $first->email;
        } else {
            return redirect()->back()->with('message', 'CPF ou CNPJ não encontrado');
        }
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        
        $response = $this->broker()->sendResetLink(
            $request->only('cpf_cnpj')
        );

        $email = $this->getEmail($request->only('cpf_cnpj')['cpf_cnpj']);

        return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($request, 'O link de confirmação da senha foi enviado ao email ' . $email)
                    : $this->sendResetLinkFailedResponse($request, $response);
    }

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email-representante');
    }
}
