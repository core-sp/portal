<?php

namespace App\Http\Controllers\Auth;

use App\Representante;
use App\Rules\CpfCnpj;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Repositories\GerentiRepositoryInterface;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class RepresentanteLoginController extends Controller
{
    private $gerentiRepository;

    public function __construct(GerentiRepositoryInterface $gerentiRepository)
    {
        $this->middleware('guest:representante')->except('logout');
        $this->gerentiRepository = $gerentiRepository;
    }

    public function showLoginForm()
    {
        return view('auth.representante-login'); 
    }

    protected function verificaGerentiLogin($cpfCnpj)
    {
        $cpfCnpj = apenasNumeros($cpfCnpj);
        $registro = Representante::where('cpf_cnpj', $cpfCnpj)->first();

        if(isset($registro)) {
            $checkGerenti = $this->gerentiRepository->gerentiChecaLogin($registro->registro_core, $cpfCnpj);

            if($checkGerenti === false) {
                return redirect()
                    ->route('representante.cadastro')
                    ->with('message', 'Desculpe, mas o cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique se todas as informações foram inseridas corretamente.')
                    ->withInput(IlluminateRequest::all());
            }
        }
    }

    protected function verificaSeAtivo($cpfCnpj)
    {
        $representante = Representante::where('cpf_cnpj', '=', $cpfCnpj)->first();

        if(isset($representante)) {
            if($representante->ativo === 0) {
                return [
                    'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta.',
                    'class' => 'alert-warning'
                ];
            } else {
                return [];
            }
        } else {
            return [
                'message' => 'Login inválido.',
                'class' => 'alert-danger'
            ];
        }
    }

    public function login(Request $request)
    {
        $cpfCnpj = apenasNumeros($request->cpf_cnpj);

        $request->request->set('cpf_cnpj', $cpfCnpj);

        $this->validate($request, [
            'cpf_cnpj' => ['required', new CpfCnpj],
            'password' => 'required'
        ], [
            'required' => 'Campo obrigatório'
        ]);

        if(!empty($this->verificaSeAtivo($cpfCnpj)))
            return $this->redirectWithErrors($request->only('cpf_cnpj', 'remember'), $this->verificaSeAtivo($cpfCnpj)['message'], $this->verificaSeAtivo($cpfCnpj)['class']);

            $this->verificaGerentiLogin($request->cpf_cnpj);

        if (Auth::guard('representante')->attempt([
            'cpf_cnpj' => $cpfCnpj,
            'password' => $request->password
        ], $request->remember)) {
            event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") conectou-se à Área do Representante.'));

            return redirect()->intended(route('representante.dashboard'));
        }

        return $this->redirectWithErrors($request->only('cpf_cnpj', 'remember'));
    }

    protected function redirectWithErrors($withInput, $message = 'Login inválido.', $class = 'alert-danger')
    {
        return redirect()
            ->back()
            ->with([
                'message' => $message,
                'class' => $class
            ])->withInput($withInput);
    }

    public function logout(Request $request)
    {
        event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") desconectou-se da Área do Representante.'));

        Auth::guard('representante')->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}
