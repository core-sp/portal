<?php

namespace App\Http\Controllers\Auth;

use App\Events\ExternoEvent;
use App\Http\Controllers\Controller;
use App\Representante;
use Illuminate\Http\Request;

class RepresentanteForgotEmailController extends Controller
{
    public function resetEmailView()
    {
        return view('site.representante.email-reset');
    }

    public function resetEmail(Request $request)
    {
        $this->rules($request);

        if (!$this->checkInfo($request->all())) {
            return redirect()
                ->route('representante.email.reset.view')
                ->with([
                    'message' => 'Não foi possível encontrar o cadastro informado. Por favor, verifique as informações inseridas.',
                    'class' => 'alert-danger'
                ]);
        }

        $update = Representante::where('cpf_cnpj', apenasNumeros($request['cpf_cnpj']))
            ->where('registro_core', apenasNumeros($request['registro_core']))
            ->update(['email' => $request['email_novo']]);
        
        if(!$update)
            abort(500);
        
        event(new ExternoEvent('"' . $request['registro_core'] . '" alterou o email para "'. $request['email_novo'] .'".'));

        return redirect()
            ->route('representante.email.reset.view')
            ->with([
                'message' => 'Email atualizado com sucesso!',
                'class' => 'alert-success'
            ]);
    }

    protected function rules($request)
    {
        $this->validate($request, [
            'cpf_cnpj' => 'required',
            'registro_core' => 'required',
            'email_antigo' => 'required|email',
            'email_novo' => 'required|email'
        ], [
            'cpf_cnpj.required' => 'Por favor, informe o CPF / CNPJ',
            'registro_core.required' => 'Por favor, informe o número de registro no Core-SP',
            'email_antigo.required' => 'Por favor, informe o email antigo, utilizado no momento do cadastro',
            'email' => 'Email inválido',
            'email_novo.required' => 'Por favor, informe o email para o qual deseja atualizar seu cadastro'
        ]);
    }

    protected function checkInfo($attr)
    {
        $representante = Representante::where([
            'cpf_cnpj' => apenasNumeros($attr['cpf_cnpj']),
            'registro_core' => apenasNumeros($attr['registro_core']),
            'email' => $attr['email_antigo']
        ])->get();

        return $representante->isEmpty() ? false : true;
    }
}
