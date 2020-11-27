<?php

namespace App\Http\Controllers;

use PDF;
use App\Certidao;
use App\Representante;
use App\Rules\CpfCnpj;
use App\Mail\CertidaoMail;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\RepresentanteEndereco;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroRepresentanteMail;
use App\Repositories\CertidaoRepository;
use App\Http\Controllers\CertidaoController;
use App\Mail\SolicitacaoAlteracaoEnderecoMail;
use App\Repositories\GerentiRepositoryInterface;
use App\Http\Requests\RepresentanteEnderecoRequest;
use App\Repositories\RepresentanteEnderecoRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class RepresentanteSiteController extends Controller
{
    private $certidaoController;
    private $certidaoRepository;
    private $gerentiRepository;
    private $representanteEnderecoRepository;
    protected $idendereco;

    public function __construct(CertidaoController $certidaoController, CertidaoRepository $certidaoRepository, GerentiRepositoryInterface $gerentiRepository, RepresentanteEnderecoRepository $representanteEnderecoRepository)
    {
        $this->middleware('auth:representante')->except(['cadastroView', 'cadastro', 'verificaEmail']);
        $this->certidaoController = $certidaoController;
        $this->certidaoRepository = $certidaoRepository;
        $this->gerentiRepository = $gerentiRepository;
        $this->representanteEnderecoRepository = $representanteEnderecoRepository;
    }

    public function index()
    {
        $resultado = $this->gerentiRepository->gerentiAnuidadeVigente(Auth::guard('representante')->user()->cpf_cnpj);
        $nrBoleto = isset($resultado[0]['NOSSONUMERO']) ? $resultado[0]['NOSSONUMERO'] : null;
        $status = statusBold($this->gerentiRepository->gerentiStatus(Auth::guard('representante')->user()->ass_id));
        $ano = date("Y");

        return view('site.representante.home', compact("nrBoleto", "status", "ano"));
    }

    public function dadosGeraisView()
    {
        $nome = Auth::guard('representante')->user()->nome;
        $registroCore = Auth::guard('representante')->user()->registro_core;
        $cpfCnpj = Auth::guard('representante')->user()->cpf_cnpj;
        $tipoPessoa = Auth::guard('representante')->user()->tipoPessoa();
        $dadosGerais = $this->gerentiRepository->gerentiDadosGerais(Auth::guard('representante')->user()->tipoPessoa(), Auth::guard('representante')->user()->ass_id);

        return view('site.representante.dados-gerais', compact("nome", "registroCore", "cpfCnpj", "tipoPessoa", "dadosGerais"));
    }

    public function contatosView()
    {
        $contatos = $this->gerentiRepository->gerentiContatos(Auth::guard('representante')->user()->ass_id);
        $gerentiTiposContatos = gerentiTiposContatos();

        return view('site.representante.contatos', compact("contatos", "gerentiTiposContatos"));
    }

    public function enderecosView()
    {
        $solicitacoesEnderecos = Auth::guard('representante')->user()->solicitacoesEnderecos();
        $possuiSolicitacaoEnderecos = $solicitacoesEnderecos->isNotEmpty();
        $endereco = $this->gerentiRepository->gerentiEnderecos(Auth::guard('representante')->user()->ass_id);

        return view('site.representante.enderecos', compact("possuiSolicitacaoEnderecos", "solicitacoesEnderecos", "endereco"));
    }

    public function listaCobrancas()
    {
        $cobrancas = $this->gerentiRepository->gerentiCobrancas(Auth::guard('representante')->user()->ass_id);

        return view('site.representante.lista-cobrancas', compact("cobrancas"));
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

        $checkGerenti = $this->gerentiChecaLogin($registro, request('cpfCnpj'), request('email'));

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

    public function inserirContatoView()
    {
        return view('site.representante.inserir-contato');
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
        $count = $this->representanteEnderecoRepository->getCountAguardandoConfirmacaoByAssId(Auth::guard('representante')->user()->ass_id);
        
        if($count >= 1) {
            return redirect()
                ->route('representante.enderecos.view')
                ->with([
                    'message' => 'Você já possui uma solicitação de alteração de endereço sob análise. Não é possível solicitar uma nova até que a anterior seja analisada e protocolada pela equipe do Core-SP.',
                    'class' => 'alert-danger'
                ]);
        }

        return view('site.representante.inserir-endereco');
    }

    public function inserirEndereco(RepresentanteEnderecoRequest $request)
    {
        $imageName = Auth::guard('representante')->user()->id . '-' . time() . '.' . request()->crimage->getClientOriginalExtension();

        $request->file("crimage")->storeAs("representantes/enderecos", $imageName);

        if(isset(request()->crimagedois)) {
            $imageDoisName = Auth::guard('representante')->user()->id . '-2-' . time() . '.' . request()->crimagedois->getClientOriginalExtension();
            $request->file("crimagedois")->storeAs("representantes/enderecos", $imageDoisName);
        } 
        else {
            $imageDoisName = null;
        }

        $save = $this->representanteEnderecoRepository->create(Auth::guard('representante')->user()->ass_id, request(["cep", "bairro", "logradouro", "numero", "complemento", "estado", "municipio"]), $imageName, $imageDoisName);

        if(!$save) {
            abort(500);
        }

        event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") solicitou mudança no endereço de correspondência.'));

        Mail::to(['desenvolvimento@core-sp.org.br', 'atendimento.adm@core-sp.org.br'])->queue(new SolicitacaoAlteracaoEnderecoMail($save->id));

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

    public function eventoBoleto()
    {
        $descricao = IlluminateRequest::input('descricao');
        event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") baixou o boleto "' . $descricao . '"'));
    }

    public function emitirCertidaoView($tipo) 
    {
        $codigo = null;

        switch($tipo) {
            case Certidao::REGULARIDADE:
                $titulo = "Certidão de Regularidade";
                $mensagem = "Clique no botão abaixo para verificar se é possível emitir sua Certidão de Regularidade.</br>Em caso positivo você poderá baixar a certidão e também a receberá em seu e-mail cadastrado no Portal.";
                $emitir = true;
                $reuso = false;
            break;
    
            case Certidao::PARCELAMENTO:
                $titulo = "Certidão de Parcelamento";
                $mensagem = "Clique no botão abaixo para verificar se é possível emitir sua Certidão de Parcelamento.</br>Em caso positivo você poderá baixar a certidão e também a receberá em seu e-mail cadastrado no Portal.";
                $emitir = true;
                $reuso = false;
            break;
    
            // Caso uma certidão inválida seja passada na URL, aborta com erro 404.
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
     * Reune dados do Representante Comercial para emitir a certidão.
     */
    public function emitirCertidao($tipo) 
    {    
        // Caso o código seja passado no lugar do tipo de certidão, deve-se baixar a certidão com esse código.
        if(!in_array($tipo, Certidao::tipos())) {
            return $this->certidaoController->baixarCertidao($tipo);
        }

        // Recupera dados do Representante Comercial.
        $dadosGerenti = $this->gerentiRepository->gerentiDadosGerais(Auth::guard('representante')->user()->tipoPessoa(), Auth::guard('representante')->user()->ass_id);

        $dadosRepresentante = [
            "nome" => Auth::guard('representante')->user()->nome, 
            "cpf_cnpj" => Auth::guard('representante')->user()->cpf_cnpj,
            "tipo_pessoa" => Auth::guard("representante")->user()->tipoPessoa(),
            "registro_core" => Auth::guard('representante')->user()->registro_core,
            "email" => Auth::guard('representante')->user()->email,
            "situacao" => trim(explode(':', $this->gerentiRepository->gerentiStatus(Auth::guard('representante')->user()->ass_id))[1]),
            "ativo" => $this->gerentiRepository->gerentiAtivo(apenasNumeros(Auth::guard('representante')->user()->cpf_cnpj))[0]["SITUACAO"] == Representante::ATIVO
        ];
        $dadosRepresentante["data_inscricao"] = $dadosGerenti["Data de início"];

        if($dadosRepresentante["tipo_pessoa"] == Representante::PESSOA_JURIDICA) {
            $dadosRepresentante["tipo_empresa"] = $dadosGerenti["Tipo de empresa"];

            // Verifica se Representante Comercial PJ possui resposável técnico. Se sim, faz um parse e define em $dadosRepresentante.
            if(!empty($dadosGerenti['Responsável técnico'])) {
                $rt = explode('(', $dadosGerenti['Responsável técnico']);
                $dadosRepresentante["resp_tecnico"] = trim($rt[0]);
                $dadosRepresentante["resp_tecnico_registro_core"] = trim(str_replace(")", "",$rt[1]));
            }
        }

        // Recupera dados de endereço do Representante Comercial
        $endereco = $this->gerentiRepository->gerentiEnderecoFormatado(Auth::guard('representante')->user()->ass_id);

        // Recupera dados de cobrança do Representante Comercial
        $cobrancas = $this->gerentiRepository->gerentiCobrancas(Auth::guard('representante')->user()->ass_id);

        return $this->certidaoController->verificaRegraCertidao(
            $tipo,
            $dadosRepresentante,
            $endereco,
            $cobrancas
        );
    }
}
