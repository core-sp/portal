<?php

namespace App\Http\Controllers;

use App\Connections\FirebirdConnection;
use App\Representante;
use Illuminate\Http\Request;
use App\Rules\CpfCnpj;
use Illuminate\Support\Facades\Input;
use App\Traits\GerentiProcedures;
use Illuminate\Support\Facades\Auth;

class RepresentanteSiteController extends Controller
{
    use GerentiProcedures;

    public function __construct()
    {
        $this->middleware('auth:representante')->except(['cadastroView', 'cadastro']);
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
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ], [
            'required' => 'Campo obrigatório',
            'password.min' => 'A senha deve conter no mínimo 6 caracteres',
            'min' => 'Quantidade inválida de caracteres',
            'email' => 'Email inválido',
            'password.confirmed' => 'A senha e a confirmação devem ser idênticas'
        ]);
    }

    public function saveRepresentante($ass_id, $nome)
    {
        $save = Representante::create([
            'cpf_cnpj' => preg_replace('/[^0-9]+/', '', request('cpfCnpj')),
            'registro_core' => preg_replace('/[^0-9]+/', '', request('registro_core')),
            'ass_id' => $ass_id,
            'nome' => $nome,
            'email' => request('email'),
            'password' => bcrypt(request('password'))
        ]);

        if(!$save)
            abort(403);
    }

    public function cadastro(Request $request)
    {
        $this->rules($request);
        
        $checkGerenti = $this->checaAtivo(request('registro_core'), request('cpfCnpj'));

        if($checkGerenti === false) {
            return redirect()
                ->route('representante.cadastro')
                ->with('message', 'Desculpe, mas o cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique se todas as informações foram inseridas corretamente.')
                ->withInput(Input::all());
        }

        $this->saveRepresentante($checkGerenti['ASS_ID'], $checkGerenti['NOME']);

        return redirect()
            ->route('representante.login')
            ->with([
                'message' => 'Cadastro realizado com sucesso. Por favor, realize o login abaixo.',
                'class' => 'alert-success'
            ]);
    }

    public function dadosGeraisView()
    {
        return view('site.representante.dados-gerais');
    }

    public function inserirContatoView(Request $request)
    {
        $tipo = $request->tipo;
        $id = $request->id;
        $conteudo = $request->conteudo;
        return view('site.representante.inserir-ou-alterar-contato', compact('tipo', 'id', 'conteudo'));
    }

    public function contatosView()
    {
        return view('site.representante.contatos');
    }

    public function enderecosView()
    {
        return view('site.representante.enderecos');
    }

    public function inserirContato(Request $request)
    {
        $this->validate($request, [
            'contato' => 'required|min:3|max:80'
        ], [
            'required' => 'Campo obrigatório',
            'min' => 'Preencha ao menos 3 caracteres',
            'max' => 'Excedido limite de caracteres'
        ]);

        $request->status === 'on' ? $status = 1 : $status = 0;

        if(isset($request->id)) {
            $this->gerentiInserirContato(Auth::guard('representante')->user()->ass_id, $request->contato, $request->tipo, $request->id, $status);
        } else {
            $this->gerentiInserirContato(Auth::guard('representante')->user()->ass_id, $request->contato, $request->tipo);
        }

        isset($request->id) ? $msg = 'Contato editado com sucesso!' : $msg = 'Contato cadastrado com sucesso!';

        return redirect()
            ->route('representante.contatos.view')
            ->with([
                'message' => $msg,
                'class' => 'alert-success'
            ]);
    }

    public function inserirEnderecoView()
    {
        $sequencia = Input::get('sequencia');
        isset($sequencia) ? $infos = $this->gerentiEnderecoInfos(Auth::guard('representante')->user()->ass_id, $sequencia) : $infos = null;

        return view('site.representante.inserir-ou-alterar-endereco', compact('infos', 'sequencia'));
    }

    protected function validateEndereco($request)
    {
        $this->validate($request, [
            'cep' => 'required',
            'bairro' => 'required|max:30',
            'logradouro' => 'required|max:100',
            'numero' => 'required|max:15',
            'complemento' => 'max:100',
            'estado' => 'required|max:5',
            'municipio' => 'required|max:30',
        ], [
            'required' => 'Campo obrigatório',
            'max' => 'Excedido limite de caracteres'
        ]);
    }

    public function inserirEndereco(Request $request)
    {
        $this->validateEndereco($request);

        $this->gerentiInserirEndereco(Auth::guard('representante')->user()->ass_id, $request);

        isset($request->sequencia) ? $msg = 'Endereço atualizado com sucesso!' : $msg = 'Endereço cadastrado com sucesso!';

        return redirect()
            ->route('representante.enderecos.view')
            ->with([
                'message' => 'Endereço cadastrado com sucesso!',
                'class' => 'alert-success'
            ]);
    }

    public function deletarContato(Request $request)
    {
        $this->gerentiDeletarContato(Auth::guard('representante')->user()->ass_id, $request->id, $request->status);

        $request->status === '1' ? $msg = 'Contato ativado com sucesso!' : $msg = 'Contato desativado com sucesso!';

        return redirect()
            ->route('representante.contatos.view')
            ->with([
                'message' => $msg,
                'class' => 'alert-info'
            ]);
    }
}
