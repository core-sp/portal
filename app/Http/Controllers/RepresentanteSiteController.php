<?php

namespace App\Http\Controllers;

use App\Connections\FirebirdConnection;
use App\Events\ExternoEvent;
use App\Mail\CadastroRepresentanteMail;
use App\Representante;
use Illuminate\Http\Request;
use App\Rules\CpfCnpj;
use Illuminate\Support\Facades\Input;
use App\Traits\GerentiProcedures;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RepresentanteSiteController extends Controller
{
    use GerentiProcedures;

    public function __construct()
    {
        $this->middleware('auth:representante')->except(['cadastroView', 'cadastro', 'verificaEmail']);
    }

    public function index()
    {
        return view('site.representante.home');
    }

    public function cadastroView()
    {
        return view('site.representante.cadastro');
    }

    protected function rules($request, $cpfCnpj)
    {
        $request->request->set('cpfCnpj', $cpfCnpj);

        $this->validate($request, [
            'cpfCnpj' => ['required', new CpfCnpj, 'unique:representantes,cpf_cnpj'],
            'registro_core' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ], [
            'required' => 'Campo obrigatório',
            'unique' => 'Este CPF/CNPJ já está cadastrado em nosso sistema',
            'password.min' => 'A senha deve conter no mínimo 6 caracteres',
            'min' => 'Quantidade inválida de caracteres',
            'email' => 'Email inválido',
            'password.confirmed' => 'A senha e a confirmação devem ser idênticas'
        ]);
    }

    public function saveRepresentante($ass_id, $nome, $cpfCnpj)
    {
        $token = str_random(32);

        $save = Representante::create([
            'cpf_cnpj' => $cpfCnpj,
            'registro_core' => preg_replace('/[^0-9]+/', '', request('registro_core')),
            'ass_id' => $ass_id,
            'nome' => $nome,
            'email' => request('email'),
            'password' => bcrypt(request('password')),
            'verify_token' => $token,
        ]);

        if(!$save)
            abort(403);

        $body = '<strong>Cadastro no Portal Core-SP realizado com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Para concluir o processo, basta clicar <a href="'. route('representante.verifica-email', $token) .'">NESTE LINK</a>.';

        Mail::to(request('email'))->queue(new CadastroRepresentanteMail($body));
    }

    public function verificaEmail($token)
    {
        $find = Representante::where('verify_token', '=', $token)->first();

        if($find) {
            $find->update([
                'ativo' => 1,
                'verify_token' => null
            ]);
        } else {
            abort(500);
        }

        event(new ExternoEvent('Usuário ' . $find->id . ' ('. $find->cpf_cnpj .') verificou o email após o cadastro.'));

        return redirect()
            ->route('representante.login')
            ->with([
                'message' => 'Email verificado com sucesso. Favor continuar com o login abaixo.',
                'class' => 'alert-success'
            ]);
    }

    public function cadastro(Request $request)
    {
        $cpfCnpjCru = request('cpfCnpj');

        $cpfCnpj = preg_replace('/[^0-9]+/', '', request('cpfCnpj'));

        $this->rules($request, $cpfCnpj);

        strlen(request('registro_core')) === 11 ? $registro = '0' . request('registro_core') : $registro = request('registro_core');
        
        $checkGerenti = $this->checaAtivo($registro, request('cpfCnpj'), request('email'));

        if($checkGerenti === false) {
            return redirect()
                ->route('representante.cadastro')
                ->with('message', 'Desculpe, mas o cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique se todas as informações foram inseridas corretamente.')
                ->withInput(Input::all());
        }

        $this->saveRepresentante($checkGerenti['ASS_ID'], $checkGerenti['NOME'], $cpfCnpj);

        event(new ExternoEvent($cpfCnpjCru . ' (' . request('email') . ') cadastrou-se na Área do Representante.'));

        return view('site.agradecimento')->with([
            'agradece' => 'Cadastro realizado com sucesso. Por favor, <strong>verifique o email informado para confirmar seu cadastro.</strong>'
        ]);
    }

    public function dadosGeraisView()
    {
        return view('site.representante.dados-gerais');
    }

    public function inserirContatoView()
    {
        return view('site.representante.inserir-ou-alterar-contato');
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
                'message' => $msg,
                'class' => 'alert-success'
            ]);
    }

    public function deletarContato(Request $request)
    {
        $this->gerentiDeletarContato(Auth::guard('representante')->user()->ass_id, $request->id, $request->status);

        if($request->status === '1') {
            $msg = 'Contato ativado com sucesso!';
            $class = 'alert-success';
        } else {
            $msg = 'Contato desativado com sucesso!';
            $class = 'alert-info';
        }

        return redirect()
            ->route('representante.contatos.view')
            ->with([
                'message' => $msg,
                'class' => $class
            ]);
    }

    public function listaCobrancas()
    {
        return view('site.representante.lista-cobrancas');
    }
}
