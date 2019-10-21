<?php

namespace App\Http\Controllers;

use App\Connections\FirebirdConnection;
use App\Representante;
use Illuminate\Http\Request;
use App\Rules\CpfCnpj;
use Illuminate\Support\Facades\Input;

class RepresentanteSiteController extends Controller
{
    private $connection;

    public function __construct()
    {
        $this->middleware('auth:representante')->except(['cadastroView', 'cadastro']);
    }

    protected function connect()
    {
        $this->connection = new FirebirdConnection();
    }

    public function index()
    {
        return view('site.representante.home');
    }

    public function cadastroView()
    {
        return view('site.representante.cadastro');
    }

    protected function rules($request)
    {
        $this->validate($request, [
            'cpfCnpj' => ['required', new CpfCnpj],
            'registro_core' => 'required',
            'nome' => 'required|min:3|max:100',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ], [
            'required' => 'Campo obrigatório',
            'password.min' => 'A senha deve conter no mínimo 6 caracteres',
            'min' => 'Quantidade inválida de caracteres',
            'max' => 'Quantidade inválida de caracteres',
            'email' => 'Email inválido',
            'password.confirmed' => 'A senha e a confirmação devem ser idênticas'
        ]);
    }

    protected function checaAtivo($registro, $cpfCnpj)
    {
        $this->connect();

        $cpfCnpj = preg_replace('/[^0-9]+/', '', $cpfCnpj);

        $run = $this->connection->prepare("select SITUACAO, REGISTRONUM, ASS_ID, NOME, EMAILS from PROCLOGINPORTAL('" . $registro . "', '" . $cpfCnpj . "')");

        $run->execute();
        $resultado = $run->fetchAll();

        if($resultado[0]['SITUACAO'] !== 'Ativo') 
            return false;
        else
            return true;
    }

    public function saveRepresentante($request)
    {
        $save = Representante::create([
            'cpf_cnpj' => preg_replace('/[^0-9]+/', '', request('cpfCnpj')),
            'registro_core' => preg_replace('/[^0-9]+/', '', request('registro_core')),
            'nome' => request('nome'),
            'email' => request('email'),
            'password' => bcrypt(request('password'))
        ]);

        if(!$save)
            abort(403);
    }

    public function cadastro(Request $request)
    {
        $this->rules($request);

        if(!$this->checaAtivo(request('registro_core'), request('cpfCnpj'))) {
            return redirect()
                ->route('representante.cadastro')
                ->with('message', 'Desculpe, mas o cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique se todas as informações foram inseridas corretamente.')
                ->withInput(Input::all());
        }

        $this->saveRepresentante($request);

        return redirect()
            ->route('representante.login')
            ->with([
                'message' => 'Cadastro realizado com sucesso. Por favor, realize o login abaixo.',
                'class' => 'alert-success'
            ]);
    }
}
