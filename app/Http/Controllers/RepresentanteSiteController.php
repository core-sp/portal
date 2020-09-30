<?php

namespace App\Http\Controllers;

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
use App\Http\Controllers\CertidaoController;
use App\Mail\SolicitacaoAlteracaoEnderecoMail;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class RepresentanteSiteController extends Controller
{
    use GerentiProcedures;

    private $certidaoController;
    protected $idendereco;

    public function __construct(CertidaoController $certidaoController)
    {
        $this->middleware('auth:representante')->except(['cadastroView', 'cadastro', 'verificaEmail']);
        $this->certidaoController = $certidaoController;
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
            'registro_core' => preg_replace('/[^0-9]+/', '', request('registro_core')),
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
            'registro_core' => preg_replace('/[^0-9]+/', '', request('registro_core')),
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

        $cpfCnpj = preg_replace('/[^0-9]+/', '', request('cpfCnpj'));

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
        switch($tipo) {
            case "Regularidade":
                $titulo = "Certidão de Regularidade";

                $mensagem = "Clique no botão abaixo para verificar se é possível emitir sua Certidão de Regularidade.";
            break;
    
            case "Parcelamento":
                $titulo = "Certidão de Parcelamento";

                $mensagem = "Clique no botão abaixo para verificar se é possível emitir sua Certidão de Parcelamento.";
            break;
    
            default:
                abort(500, "Tipo de certidão inválida");
            break;
        }

        return view("site.representante.emitir-certidao", compact("titulo", "mensagem"));
    }
    
    public function emitirCertidao($tipo) 
    {   
        $testeMK = false;

        if($testeMK) {
            // Dados de Representante Comercial Teste
            $dadosRepresentante = [
                "nome" => "Teste", 
                "cpf_cnpj" => "12345679000012",
                "registro_core" => "12345/12345",
                "email" => "teste@teste.com",
                "data_inscricao" => "20/12/2020",
                "tipo_empresa" => "Empresa Peguena",
                "resp_tecnico" => null,
                "resp_tecnico_registro_core" => null
            ];

            // Endereço teste
            $endereco = "Rua Teste, 1234, Teste São Paulo/SP CEP: 1234-1234";
        }
        else {
            // Recupera dados do Representante Comercial
            $dadosRepresentante = [
                "nome" => Auth::guard('representante')->user()->nome, 
                "cpf_cnpj" => Auth::guard('representante')->user()->cpf_cnpj,
                "registro_core" => Auth::guard('representante')->user()->registro_core,
                "email" => Auth::guard('representante')->user()->email,
                "tipo_empresa" => null,
                "resp_tecnico" => null,
                "resp_tecnico_registro_core" => null
            ];
            // Dados de PJ: "Data de homologação", "Tipo de empresa", "Responsável técnico" (falta CPF do resposável técnico)
            // Dados de PF: "Data de homologação"
            $dadosGerenti = Auth::guard('representante')->user()->dadosGerais();
            $dadosRepresentante["data_inscricao"] = $dadosGerenti["Data de homologação"];
            if(Auth::guard('representante')->user()->tipoPessoa() == "PJ") {
                $dadosRepresentante["tipo_empresa"] = $dadosGerenti["Tipo de empresa"];
                if(!empty($dadosGerenti['Responsável técnico'])) {
                    $rt = explode('(', $dados['Responsável técnico']);
                    $dadosRepresentante["resp_tecnico"] = trim($rt[0]);
                    $dadosRepresentante["resp_tecnico_registro_core"] = trim(str_replace(")", "",$rt[1]));
                }
            }

            // Recupera dados do endereço
            $endereco = Auth::guard("representante")->user()->enderecoFormatado();
        }

        switch($tipo) {
            case "Regularidade":
                if($testeMK) {
                    $podeEmitir = true;
                }
                else {
                    $podeEmitir = Auth::guard("representante")->user()->pegaSituacao() === "Em dia." ? true : false;
                }
                
                if($podeEmitir) {                    
                    return $this->certidaoController->storeCertidaoRegularidade(
                        $testeMK ? "PF" : Auth::guard("representante")->user()->tipoPessoa(),
                        $dadosRepresentante,
                        $endereco
                    );
                }
                else {
                    return view("site.representante.emitir-certidao")
                        ->with("mensagem", "Certidão de Regularidade não pode ser emitida. Por favor verificar sua situação finaceira com o CORE-SP.")
                        ->with("titulo", "Certidão de Regularidade");
                }
            break;
    
            case "Parcelamento":
                if($testeMK) {
                    $podeEmitir = true;
                }
                else {
                    if(Auth::guard("representante")->user()->pegaSituacao() === "Parcelamento em aberto.") {
                        $podeEmitir = true;
                        
                        $cobrancas = Auth::guard("representante")->user()->cobrancas();

                        // Se não existe outras cobranças, não há acordo de parcelamento
                        if(empty($cobrancas["outros"])) {
                            $podeEmitir = false;
                        }
                        else {
                            $parcelamentosAgrupados = array();

                            // Agrupa todos os Acordos por anos parcelados
                            foreach ($cobrancas["outros"] as $cobranca) {
                                if(strpos($cobranca["DESCRICAO"], "Acordo") !== false) {
                                    preg_match_all("/\((.*?)\)/", $cobranca["DESCRICAO"], $matches);

                                    $parcelamentosAgrupados[$matches[1][0]][] = $cobranca;

                                    // Se qualquer parcelamento estiver vencido, a certidão não pode ser emitida. Cancelamos a iteração.
                                    if($cobranca["SITUACAO"] === "Em aberto" && $cobranca["VENCIMENTOBOLETO"] === null) {
                                        $podeEmitir = false;
                                        break;
                                    }
                                }
                            }
                            
                            // Não existe acordos em outras cobranças
                            if (empty($parcelamentosAgrupados)) {
                                $podeEmitir =  false;
                            }
                            else {
                                foreach($parcelamentosAgrupados as $grupo) {
                                    $acordoPago = true;
                                    $primeiraParcelaPaga = false;

                                    // Iterando para verificar se todas as parcelas foram pagas
                                    foreach($grupo as $index => $parcelamento) {
                                        // Caso uma parcela esteja em aberto, o acordo não foi totalmente pago
                                        if($parcelamento["SITUACAO"] === "Em aberto") {
                                            $acordoPago = false;
                                        }

                                        // Último valor do array contêm a primeira parcela do acordo
                                        if($index == count($grupo) - 1) {
                                            // Verifica se a primeira parcela foi paga
                                            if($parcelamento["SITUACAO"] === "Pago") {
                                                $primeiraParcelaPaga = true;
                                            }
                                        }
                                    }

                                    // Caso o acordo ainda não esteja totalmente pago e a primeira parcela foi paga, recuperamos dados do acordo
                                    if(!$acordoPago && $primeiraParcelaPaga) {
                                        // Recupera o número de parcelas ([0] = parcela atual, [1] = total de parcelas)
                                        preg_match_all("/Parcela (.*?) Acordo/", $grupo[0]["DESCRICAO"], $matches); 
                                        $numeroParcelas = explode("/", $matches[1][0]);

                                        // Recupera os anos do parcelamento
                                        preg_match_all("/\((.*?)\)/", $grupo[0]["DESCRICAO"], $matches); 
                                        $anosParcelas = $matches[1][0];

                                        // Recupera data do primeiro pagamento
                                        $primeiroPagamento = onlyDate($grupo[count($grupo) - 1]["VENCIMENTO"]);
                                    }
                                }
                            } 
                        }
                    }
                    else {
                        $podeEmitir =  false;
                    }
                }

                if($podeEmitir) {
                    $dadosParcelamento = [
                        "parcelamento_ano" => $anosParcelas, 
                        "numero_parcelas" => $numeroParcelas[1],
                        "data_primeiro_pagamento" => $primeiroPagamento
                    ];

                    return $this->certidaoController->storeCertidaoParcelamento(
                        $testeMK ? "PJ" : Auth::guard('representante')->user()->tipoPessoa(),
                        $dadosRepresentante,
                        $endereco,
                        $dadosParcelamento
                    );
                }
                else {
                    return view("site.representante.emitir-certidao")
                        ->with("mensagem", "Certidão de Parcelamento não pode ser emitida. Por favor verificar sua situação finaceira com o CORE-SP.")
                        ->with("titulo", "Certidão de Parcelamento");
                }
            break;
    
            default:
                abort(500, "Tipo de certidão inválida.");
            break;
        }    
    }
}
