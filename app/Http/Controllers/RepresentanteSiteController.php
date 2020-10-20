<?php

namespace App\Http\Controllers;

use PDF;
use App\Representante;
use App\Rules\CpfCnpj;
use App\Mail\CertidaoMail;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\RepresentanteEndereco;
use App\Traits\GerentiProcedures;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Connections\FirebirdConnection;
use App\Mail\CadastroRepresentanteMail;
use App\Repositories\CertidaoRepository;
use App\Http\Controllers\CertidaoController;
use App\Mail\SolicitacaoAlteracaoEnderecoMail;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class RepresentanteSiteController extends Controller
{
    use GerentiProcedures;

    private $certidaoController;
    private $certidaoRepository;
    protected $idendereco;

    public function __construct(CertidaoController $certidaoController, CertidaoRepository $certidaoRepository)
    {
        $this->middleware('auth:representante')->except(['cadastroView', 'cadastro', 'verificaEmail']);
        $this->certidaoController = $certidaoController;
        $this->certidaoRepository = $certidaoRepository;
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
            'cpfCnpj' => ['required', new CpfCnpj, 'unique:representantes,cpf_cnpj,NULL,id,deleted_at,NULL'],
            'registro_core' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
            'checkbox-tdu' => 'accepted'
        ], [
            'required' => 'Campo obrigatório',
            'unique' => 'Este CPF/CNPJ já está cadastrado em nosso sistema',
            'password.min' => 'A senha deve conter no mínimo 6 caracteres',
            'min' => 'Quantidade inválida de caracteres',
            'email' => 'Email inválido',
            'password.confirmed' => 'A senha e a confirmação devem ser idênticas',
            'accepted' => '- Você deve concordar com os Termos de Uso'
        ]);
    }

    public function saveRepresentante($ass_id, $nome, $cpfCnpj)
    {
        $token = str_random(32);

        request('checkbox-tdu') === 'on' ? $aceite = 1 : $aceite = 0;        

        $save = Representante::create([
            'cpf_cnpj' => $cpfCnpj,
            'registro_core' => apenasNumeros(request('registro_core')),
            'ass_id' => $ass_id,
            'nome' => $nome,
            'email' => request('email'),
            'password' => bcrypt(request('password')),
            'verify_token' => $token,
            'aceite' => $aceite
        ]);

        if(!$save)
            abort(403);

        $body = '<strong>Cadastro no Portal Core-SP realizado com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Para concluir o processo, basta clicar <a href="'. route('representante.verifica-email', $token) .'">NESTE LINK</a>.';

        Mail::to(request('email'))->queue(new CadastroRepresentanteMail($body));
    }

    public function updateRepresentante($id, $ass_id, $nome, $cpfCnpj)
    {
        $token = str_random(32);

        request('checkbox-tdu') === 'on' ? $aceite = 1 : $aceite = 0;

        $rep = Representante::withTrashed()->find($id);
        $rep->restore();

        $update = $rep->update([
            'cpf_cnpj' => $cpfCnpj,
            'registro_core' => apenasNumeros(request('registro_core')),
            'ass_id' => $ass_id,
            'nome' => $nome,
            'email' => request('email'),
            'password' => bcrypt(request('password')),
            'verify_token' => $token,
            'aceite' => $aceite
        ]);

        if(!$update)
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

        event(new ExternoEvent('Usuário ' . $find->id . ' ("'. $find->cpf_cnpj .'") verificou o email após o cadastro.'));

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

        $cpfCnpj = apenasNumeros(request('cpfCnpj'));

        $this->rules($request, $cpfCnpj);

        strlen(request('registro_core')) === 11 ? $registro = '0' . request('registro_core') : $registro = request('registro_core');

        $checkGerenti = $this->checaAtivo($registro, request('cpfCnpj'), request('email'));

        if (array_key_exists('Error', $checkGerenti)) {
            return redirect()
                ->route('representante.cadastro')
                ->with('message', $checkGerenti['Error'])
                ->withInput(IlluminateRequest::all());
        }

        $checkSoftDeleted = Representante::where('cpf_cnpj', $cpfCnpj)->withTrashed()->first();

        if($checkSoftDeleted) {
            $this->updateRepresentante($checkSoftDeleted->id, $checkGerenti['ASS_ID'], utf8_encode($checkGerenti['NOME']), $cpfCnpj);
        } else {
            $this->saveRepresentante($checkGerenti['ASS_ID'], utf8_encode($checkGerenti['NOME']), $cpfCnpj);
        }

        event(new ExternoEvent('"' . $cpfCnpjCru . '" ("' . request('email') . '") cadastrou-se na Área do Representante.'));

        return view('site.agradecimento')->with([
            'agradece' => 'Cadastro realizado com sucesso. Por favor, <strong>acesse o email informado para confirmar seu cadastro.</strong>'
        ]);
    }

    public function dadosGeraisView()
    {
        return view('site.representante.dados-gerais');
    }

    public function inserirContatoView()
    {
        return view('site.representante.inserir-contato');
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

        event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") inseriu um novo contato: '. gerentiTiposContatos()[$request->tipo] .'.'));

        return redirect()
            ->route('representante.contatos.view')
            ->with([
                'message' => $msg,
                'class' => 'alert-success'
            ]);
    }

    public function inserirEnderecoView()
    {
        return view('site.representante.inserir-endereco');
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
            'crimage' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048'
        ], [
            'required' => 'Campo obrigatório',
            'max' => 'Excedido limite de caracteres',
            'crimage.required' => 'Favor adicionar um comprovante de residência',
            'mimes' => 'Tipo de arquivo não suportado',
            'crimage.max' => 'A imagem não pode ultrapassar 2MB'
        ]);
    }

    public function saveEndereco($image, $imageDois = null)
    {
        $save = RepresentanteEndereco::create([
            'ass_id' => Auth::guard('representante')->user()->ass_id,
            'cep' => request('cep'),
            'bairro' => request('bairro'),
            'logradouro' => request('logradouro'),
            'numero' => request('numero'),
            'complemento' => request('complemento'),
            'estado' => request('estado'),
            'municipio' => request('municipio'),
            'crimage' => $image,
            'crimagedois' => $imageDois,
            'status' => 'Aguardando confirmação'
        ]);

        if(!$save)
            abort(403);

        $this->idendereco = $save->id;
    }

    public function inserirEndereco(Request $request)
    {
        $this->validateEndereco($request);

        // Checa se já tem solicitação de endereço sob análise.
        $count = RepresentanteEndereco::where('ass_id', Auth::guard('representante')->user()->ass_id)->where('status', 'Aguardando confirmação')->count();
        if($count >= 1) {
            return redirect()
                ->route('representante.enderecos.view')
                ->with([
                    'message' => 'Você já possui uma solicitação de alteração de endereço sob análise. Não é possível solicitar uma nova até que a anterior seja analisada e protocolada pela equipe do Core-SP.',
                    'class' => 'alert-danger'
                ]);
        }

        $imageName = Auth::guard('representante')->user()->id . '-' . time() . '.' . request()->crimage->getClientOriginalExtension();

        request()->crimage->move(public_path('imagens/representantes/enderecos'), $imageName);

        if(isset(request()->crimagedois)) {
            $imageDoisName = Auth::guard('representante')->user()->id . '-2-' . time() . '.' . request()->crimagedois->getClientOriginalExtension();
            request()->crimagedois->move(public_path('imagens/representantes/enderecos'), $imageDoisName);
        } else {
            $imageDoisName = null;
        }

        $this->saveEndereco($imageName, $imageDoisName);

        // $this->gerentiInserirEndereco(Auth::guard('representante')->user()->ass_id, $request);

        event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") solicitou mudança no endereço de correspondência.'));

        $body = 'Nova solicitação de alteração de endereço no Portal Core-SP.';
        $body .= '<br /><br />';
        $body .= '<strong>Código da solicitação:</strong> #'. $this->idendereco;
        $body .= '<br /><br />';
        $body .= 'Para verifica-la, acesse o <a href="' . route('site.home') . '/admin">painel de administração</a> do Portal Core-SP.';

        Mail::to(['desenvolvimento@core-sp.org.br', 'atendimento.adm@core-sp.org.br'])->queue(new SolicitacaoAlteracaoEnderecoMail($body));

        return redirect()
            ->route('representante.enderecos.view')
            ->with([
                'message' => 'Solicitação enviada com sucesso! Após verificação das informações, o endereço será atualizado.',
                'class' => 'alert-success'
            ]);
    }

    public function deletarContato(Request $request)
    {
        $this->gerentiDeletarContato(Auth::guard('representante')->user()->ass_id, $request);

        if($request->status === '1') {
            $msg = 'Contato ativado com sucesso!';
            $class = 'alert-success';
            $str = 'ativou';
        } else {
            $msg = 'Contato desativado com sucesso!';
            $class = 'alert-info';
            $str = 'desativou';
        }

        event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") '. $str .' o contato '. $request->id .'.'));

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

    public function eventoBoleto()
    {
        $descricao = IlluminateRequest::input('descricao');
        event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") baixou o boleto "' . $descricao . '"'));
    }

    public function emitirCertidaoView($tipo) 
    {
        $codigo = null;

        switch($tipo) {
            case "Regularidade":
                $titulo = "Certidão de Regularidade";
                $mensagem = "Clique no botão abaixo para verificar se é possível emitir sua Certidão de Regularidade.</br>Em caso positivo você poderá baixar a certidão e também a receberá em seu e-mail cadastrado no Portal.";
                $emitir = true;
                $reuso = false;
            break;
    
            case "Parcelamento":
                $titulo = "Certidão de Parcelamento";
                $mensagem = "Clique no botão abaixo para verificar se é possível emitir sua Certidão de Parcelamento.</br>Em caso positivo você poderá baixar a certidão e também a receberá em seu e-mail cadastrado no Portal.";
                $emitir = true;
                $reuso = false;
            break;
    
            default:
                abort(404);
            break;
        }

        // Checa se existe alguma certidão que foi emitida nos últimos 30 dias.
        $ultimaCertidao = $this->certidaoRepository->consultaCertidao(apenasNumeros(Auth::guard('representante')->user()->cpf_cnpj), $tipo);

        if($ultimaCertidao) {
            $codigo = $ultimaCertidao->codigo;

            // Caso exista uma certidão que já foi emitida nos últimos 15 dias atrás, o Portal não deve permitir a emissão, apenas o download da certidão existente.
            if($ultimaCertidao->data_emissao > date('Y-m-d', strtotime('-15 days'))) {
                $mensagem = 'Representante Comercial emitiu uma certidão há menos de 15 dias e não pode emitir uma nova certidão, devendo reutilizar a última certidão emitida.</br>Por favor clique no botão abaixo para obter a última certidão.';
                $emitir = false;
                $reuso = true;
            }
            // Caso a certidão tenha mais de 15 dias, o Portal deve dar a opção de emitir uma nova, ou de retutilizar a existente.
            else {
                $mensagem = 'Representante Comercial emitiu uma certidão e esta ainda se encontra válida, caso queria reutilizar essa certidão, por favor clique no botão "Baixar" para obter a última certidão. </br>Caso deseje emitir uma nova, clique no botão "Emitir".';
                $emitir = true;
                $reuso = true;
            }
        }

        return view("site.representante.emitir-certidao", compact("titulo", "mensagem", "emitir", "reuso", "codigo"));
    }
    
    /**
     * Reúne dados do Representante Comercial para emitir a certidão.
     */
    public function emitirCertidao($tipo) 
    {    
        // Caso o código seja passado no lugar do tipo de certidão, deve-se baixar a certidão com esse código.
        if(!in_array($tipo, ["Regularidade", "Parcelamento"])) {
            return $this->certidaoController->baixarCertidao($tipo);
        }

        // Recupera dados do Representante Comercial
        $dadosGerenti = Auth::guard('representante')->user()->dadosGerais();

        $dadosRepresentante = [
            "nome" => Auth::guard('representante')->user()->nome, 
            "cpf_cnpj" => Auth::guard('representante')->user()->cpf_cnpj,
            "tipo_pessoa" => Auth::guard("representante")->user()->tipoPessoa(),
            "registro_core" => Auth::guard('representante')->user()->registro_core,
            "email" => Auth::guard('representante')->user()->email,
            "situacao" => Auth::guard("representante")->user()->pegaSituacao(),
            "ativo" => Auth::guard("representante")->user()->ativo()
        ];
        $dadosRepresentante["data_inscricao"] = $dadosGerenti["Data de início"];

        if($dadosRepresentante["tipo_pessoa"] == "PJ") {
            $dadosRepresentante["tipo_empresa"] = $dadosGerenti["Tipo de empresa"];

            if(!empty($dadosGerenti['Responsável técnico'])) {
                $rt = explode('(', $dadosGerenti['Responsável técnico']);
                $dadosRepresentante["resp_tecnico"] = trim($rt[0]);
                $dadosRepresentante["resp_tecnico_registro_core"] = trim(str_replace(")", "",$rt[1]));
            }
        }

        // Recupera dados de endereço do Representante Comercial
        $endereco = Auth::guard("representante")->user()->enderecoFormatado();
        

        // Recupera dados de cobrança do Representante Comercial
        $cobrancas = Auth::guard("representante")->user()->cobrancas();

        return $this->certidaoController->verificaRegraCertidao(
            $tipo,
            $dadosRepresentante,
            $endereco,
            $cobrancas
        );
    }
}
