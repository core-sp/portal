<?php

namespace App\Http\Controllers;

use Exception;
use App\Certidao;
use App\Representante;
use App\Rules\CpfCnpj;
use GuzzleHttp\Client;
use App\Mail\CertidaoMail;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\RepresentanteEndereco;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroRepresentanteMail;
use App\Repositories\GerentiApiRepository;
use GuzzleHttp\Exception\RequestException;
use App\Mail\SolicitacaoAlteracaoEnderecoMail;
use App\Repositories\GerentiRepositoryInterface;
use App\Http\Requests\RepresentanteEnderecoRequest;
use App\Http\Requests\SolicitaCedulaRequest;
use App\Repositories\RepresentanteEnderecoRepository;
use App\Repositories\BdoOportunidadeRepository;
use Illuminate\Support\Facades\View;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\RepSalaReuniaoRequest;
use Illuminate\Support\Facades\Request as IlluminateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RepresentanteSiteController extends Controller
{
    private $gerentiRepository;
    private $representanteEnderecoRepository;
    private $gerentiApiRepository;
    protected $idendereco;
    private $bdoOportunidadeRepository;
    private $service;

    public function __construct(GerentiRepositoryInterface $gerentiRepository, RepresentanteEnderecoRepository $representanteEnderecoRepository, GerentiApiRepository $gerentiApiRepository, BdoOportunidadeRepository $bdoOportunidadeRepository, MediadorServiceInterface $service)
    {
        $this->middleware('auth:representante')->except(['cadastroView', 'cadastro', 'verificaEmail']);
        $this->gerentiRepository = $gerentiRepository;
        $this->representanteEnderecoRepository = $representanteEnderecoRepository;
        $this->gerentiApiRepository = $gerentiApiRepository;
        $this->bdoOportunidadeRepository = $bdoOportunidadeRepository;
        $this->service = $service;

        if($this->service->getService('Aviso')->avisoAtivado($this->service->getService('Aviso')->areas()[0]))
        {
            $aviso = $this->service->getService('Aviso')->getByArea($this->service->getService('Aviso')->areas()[0]);
            View::share('aviso', $aviso);
        }
    }

    public function index()
    {
        $rep = Auth::guard('representante')->user();
        $resultado = $this->gerentiRepository->gerentiAnuidadeVigente(apenasNumeros($rep->cpf_cnpj));
        $nrBoleto = isset($resultado[0]['NOSSONUMERO']) ? $resultado[0]['NOSSONUMERO'] : null;
        $status = statusBold($this->gerentiRepository->gerentiStatus($rep->ass_id));
        $ano = date("Y");
        $dadosBasicos = utf8_converter($this->gerentiRepository->gerentiAtivo(apenasNumeros($rep->cpf_cnpj)));
        $dados = isset($dadosBasicos) && (count($dadosBasicos) == 1) ? $dadosBasicos[0] : null;
        if(isset($dados['SITUACAO']) && ($dados['SITUACAO'] != 'Não encontrado'))
        {
            $nome = isset($dados['NOME']) && ($dados['NOME'] != $rep->nome);
            $id = isset($dados['ASS_ID']) && ($dados['ASS_ID'] == $rep->ass_id);
            $nome && $id ? $rep->update(['nome' => mb_strtoupper($dados['NOME'], 'UTF-8')]) : null;
        }

        event(new ExternoEvent('.', 'Home'));

        return view('site.representante.home', compact("nrBoleto", "status", "ano"));
    }

    public function dadosGeraisView()
    {
        $nome = Auth::guard('representante')->user()->nome;
        $registroCore = Auth::guard('representante')->user()->registro_core;
        $cpfCnpj = Auth::guard('representante')->user()->cpf_cnpj;
        $tipoPessoa = Auth::guard('representante')->user()->tipoPessoa();
        $dadosGerais = $this->gerentiRepository->gerentiDadosGerais(Auth::guard('representante')->user()->tipoPessoa(), Auth::guard('representante')->user()->ass_id);
        event(new ExternoEvent('.', 'Dados Gerais'));

        return view('site.representante.dados-gerais', compact("nome", "registroCore", "cpfCnpj", "tipoPessoa", "dadosGerais"));
    }

    public function contatosView()
    {
        $contatos = $this->gerentiRepository->gerentiContatos(Auth::guard('representante')->user()->ass_id);
        $gerentiTiposContatos = gerentiTiposContatos();
        event(new ExternoEvent('.', 'Contatos'));

        return view('site.representante.contatos', compact("contatos", "gerentiTiposContatos"));
    }

    public function enderecosView()
    {
        $solicitacoesEnderecos = Auth::guard('representante')->user()->solicitacoesEnderecos();
        $possuiSolicitacaoEnderecos = $solicitacoesEnderecos->isNotEmpty();
        $endereco = $this->gerentiRepository->gerentiEnderecos(Auth::guard('representante')->user()->ass_id);
        event(new ExternoEvent('.', 'End. de Correspondência'));

        return view('site.representante.enderecos', compact("possuiSolicitacaoEnderecos", "solicitacoesEnderecos", "endereco"));
    }

    public function listaCobrancas()
    {
        $cobrancas = $this->gerentiRepository->gerentiCobrancas(Auth::guard('representante')->user()->ass_id);
        event(new ExternoEvent('.', 'Situação Financeira'));

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
            abort(500, 'Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Portal, caso contrário, por favor refazer cadastro no Portal.');
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

        $checkGerenti = $this->gerentiRepository->gerentiChecaLogin($registro, request('cpfCnpj'), request('email'));

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
        event(new ExternoEvent(' para incluir / desativar.', 'Contatos'));
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
            $this->gerentiRepository->gerentiInserirContato(Auth::guard('representante')->user()->ass_id, $request->contato, $request->tipo, $request->id, $status);
        } else {
            $this->gerentiRepository->gerentiInserirContato(Auth::guard('representante')->user()->ass_id, $request->contato, $request->tipo);
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
        event(new ExternoEvent(' para incluir.', 'End. de Correspondência'));

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
        $this->gerentiRepository->gerentiDeletarContato(Auth::guard('representante')->user()->ass_id, $request);

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

    /**
     * Abre página para emissão da certidão.
     */
    public function emitirCertidaoView() 
    {
        try {
            $responseGetCertidao = $this->gerentiApiRepository->gerentiGetCertidao(Auth::guard('representante')->user()->ass_id);
        }
        catch (Exception $e) {
            Log::error($e->getTraceAsString());

            abort(500, 'Estamos enfrentando problemas técnicos no momento. Por favor, tente dentro de alguns minutos.');
        }

        $certidoes = isset($responseGetCertidao['data']) ? $responseGetCertidao['data'] : [];

        array_multisort(array_column($certidoes, 'dataEmissao'), SORT_DESC, array_column($certidoes, 'horaEmissao'), SORT_DESC, $certidoes);

        $titulo = 'Emissão de Certidão';
        $mensagem = 'Clique no botão abaixo para verificar e emitir sua Certidão.</br>';
        $emitir = true;
        
        $hasEmitido = in_array('Emitido', array_column($certidoes, 'status'));

        if($hasEmitido) {
            $mensagem .='<strong>Atenção, existem certidões que ainda estão válidas, caso opte em emitir uma nova, essas serão canceladas.</strong></br>';
        }

        event(new ExternoEvent('.', 'Emitir Certidão'));

        return view('site.representante.emitir-certidao', compact('titulo', 'mensagem', 'emitir', 'certidoes'));
    }
    
    /**
     * Verifica se é possível emitir a certidão. Em caso positivo, a certidão será gerada e enviada por e-mail/download, caso contrário, uma mensagem de erro é retornada.
     */
    public function emitirCertidao() 
    {
        try {
            $responseGerentiJson = $this->gerentiApiRepository->gerentiGenerateCertidao(Auth::guard('representante')->user()->ass_id);
        }
        // Erro lançado na integração HTTP
        catch (RequestException $e) {

            $responseGerentiJsonError = json_decode($e->getResponse()->getBody()->getContents(), true);

            // Log do erro que o representante comercial recebeu, com mensagem de erro direto do GERENTI
            event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") não conseguiu emitir certidão. Erro: ' . $responseGerentiJsonError['error']['messages'][0]));

            $titulo = 'Falha ao emitir certidão';
            $mensagem = 'Não foi possível emitir a certidão. Por favor entre em contato com o CORE-SP para mais informações.';
            $emitir = false;

            return view("site.representante.emitir-certidao", compact('titulo', 'mensagem', 'emitir'));
        }
        catch (Exception $e) {
            Log::error($e->getTraceAsString());

            abort(500, 'Estamos enfrentando problemas técnicos no momento. Por favor, tente dentro de alguns minutos.');
        }

        event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") gerou certidão com código: ' . $responseGerentiJson['data']['numeroDocumento']));

        // Arquivo enviado pelo GERENTI em base 64
        $pdfBase64 = $responseGerentiJson['data']['base64'];

        // Envio do PDF por e-mail
        $email = new CertidaoMail($pdfBase64);
        Mail::to(Auth::guard('representante')->user()->email)->queue($email);

        // Download do arquivo PDF
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=certidao.pdf',
            'Expires' => '0',
            'Pragma' => 'public',
        ];

        return response()->streamDownload(function () use ($pdfBase64){
            echo base64_decode($pdfBase64);
        }, 'certidao.pdf', $headers);
    }

    /**
     * Faz download do PDF da certidão do Representante Comercial através do númeo da certidão.
     */
    public function baixarCertidao(Request $request) 
    {
        $responseGerentiJson = $this->gerentiApiRepository->gerentiGetCertidao(Auth::guard('representante')->user()->ass_id);

        $certidoes = $responseGerentiJson['data'];

        $posCertidao = array_search($request->numero, array_column($certidoes, 'numeroDocumento'));

        if($certidoes[$posCertidao]['status'] === 'Emitido') {

            $pdfBase64 = $certidoes[$posCertidao]['base64'];

            // Download do arquivo PDF
            $headers = [
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename=certidao.pdf',
                'Expires' => '0',
                'Pragma' => 'public',
            ];

            event(new ExternoEvent(' e realizou download.', 'Emitir Certidão'));

            return response()->streamDownload(function () use ($pdfBase64){
                echo base64_decode($pdfBase64);
            }, 'certidao.pdf', $headers);
        }
        else {
            $titulo = 'Falha ao baixar certidão';
            $mensagem = 'Não foi possível baixar a certidão.';
            $emitir = false;

            return view("site.representante.emitir-certidao", compact('titulo', 'mensagem', 'emitir'));
        }
    }

    public function simuladorRefis()
    {
        $valoresRefis = $this->gerentiRepository->gerentiValoresRefis(Auth::guard('representante')->user()->ass_id);

        return view('site.representante.simulador-refis', compact('valoresRefis'));
    }

    public function bdo()
    {
        $rep = Auth::guard('representante')->user();
        try{
            $seccional = $this->gerentiRepository->gerentiDadosGerais($rep->tipoPessoa(), $rep->ass_id)["Regional"];
            $idregional = $this->service->getService('Regional')->getByName($seccional)->idregional;
            $segmentoGerenti = $this->gerentiRepository->gerentiGetSegmentosByAssId($rep->ass_id);
            $segmento = !empty($segmentoGerenti) ? $segmentoGerenti[0]["SEGMENTO"] : $segmentoGerenti;
            $bdo = !empty($segmento) ? $this->bdoOportunidadeRepository->buscaBySegmentoEmAndamento($segmento, $idregional) : collect();
            foreach($bdo as $b)
                // usei o campo observação do model para armazenar temporariamente o link
                $b->observacao = '/balcao-de-oportunidades/busca?palavra-chave='.str_replace('"', '', $b->titulo).'&segmento='.$segmento.'&regional='.$idregional;
        }catch (Exception $e) {
            Log::error($e->getMessage());
            abort(500, 'Estamos enfrentando problemas técnicos no momento. Por favor, tente mais tarde.');
        }
        
        event(new ExternoEvent('.', 'Oportunidades'));

        return view('site.representante.bdo', compact('bdo', 'segmento', 'seccional'));
    }

    public function cedulasView()
    {
        try{
            $user = auth()->guard('representante')->user();
            $dados = $this->service->getService('Cedula')->getByRepresentante($user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as solicitações de cédula do representante.");
        }

        event(new ExternoEvent('.', 'Solicitação de Cédula'));

        return view('site.representante.cedulas')->with($dados);
    }

    public function inserirsolicitarCedulaView()
    {
        try{
            $representante = auth()->guard('representante')->user();
            $dados = $this->service->getService('Cedula')->getByRepresentante($representante, $this->gerentiRepository);
            if($dados['cedulaEmAndamento'] > 0)
                return redirect()->route('representante.solicitarCedulaView');

            $dados['tipos'] = $this->service->getService('Cedula')->getAllTipos();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para realizar a solicitação de cédula do representante.");
        }

        event(new ExternoEvent(' para incluir.', 'Solicitação de Cédula'));

        return view('site.representante.inserir-solicita-cedula')->with($dados);
    }

    public function inserirsolicitarCedula(SolicitaCedulaRequest $request)
    {
        try{
            $validate = $request->validated();
            $representante = auth()->guard('representante')->user();
            $dados = $this->service->getService('Cedula')->getByRepresentante($representante);
            if($dados['cedulaEmAndamento'] > 0)
                return redirect()->route('representante.solicitarCedulaView');

            $dados = $this->service->getService('Cedula')->save($validate, $representante, $this->gerentiRepository, $this->service);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao salvar a solicitação de cédula do representante.");
        }

        return redirect()->route('representante.solicitarCedulaView')->with([
            'message' => 'Solicitação enviada com sucesso! Após verificação das informações, será atualizada.',
            'class' => 'alert-success'
        ]);
    }

    public function cursos()
    {
        try{
            $dados['cursos'] = $this->service->getService('Curso')->siteGrid(true);
            $dados['certificados'] = $this->service->getService('Curso')->certificadosRepresentante(auth()->guard('representante')->user()->cpf_cnpj);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os cursos.");
        }

        event(new ExternoEvent('.', 'Cursos'));

        return view('site.representante.cursos', $dados);
    }

    public function agendamentoSala($acao = null, $id = null)
    {
        try{
            $user = auth()->guard('representante')->user();
            $view = 'inserir-agendamento-sala';
            $erro = null;
            switch ($acao) {
                case 'agendar':
                    if(trim($this->gerentiRepository->gerentiStatus($user->ass_id)) != 'Situação: Em dia.')
                        return redirect()->route('representante.agendar.inserir.view')->with([
                            'message' => '<i class="fas fa-exclamation-triangle"></i>&nbsp;Para liberar o seu agendamento entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.',
                            'class' => 'alert-warning'
                        ]);
                    $erro = $this->service->getService('SalaReuniao')->site()->verificaPodeAgendar($user, $this->service);
                    $dados['salas'] = !isset($erro) ? $this->service->getService('SalaReuniao')->salasAtivas() : collect();
                    if($dados['salas']->count() == 0)
                        $erro = isset($erro) ? $erro : [
                            'message' => '<i class="fas fa-info-circle"></i> No momento não há salas disponíveis para novos agendamentos.',
                            'class' => 'alert-info'
                        ];
                    break;
                case 'editar':
                    $dados = $this->service->getService('SalaReuniao')->site()->verificaPodeEditar($id, $user);
                    $erro = isset($dados['message']) ? $dados : $erro;
                    break;
                case 'cancelar':
                    $dados = $this->service->getService('SalaReuniao')->site()->verificaPodeCancelar($id, $user);
                    $erro = isset($dados['message']) ? $dados : $erro;
                    break;
                case 'justificar':
                    $dados = $this->service->getService('SalaReuniao')->site()->verificaPodeJustificar($id, $user);
                    $erro = isset($dados['message']) ? $dados : $erro;
                    break;
                default:
                    $dados['salas'] = $user->agendamentosAtivos();
                    $dados['participando'] = $this->service->getService('SalaReuniao')->site()->getAgendadosParticipante($user);
                    $view = 'agendamento-sala';
                    $dados['situacao'] = $this->service->getService('SalaReuniao')->site()->verificaSuspensao($user, $this->service);
                    $dados['total'] = $this->service->getService('SalaReuniao')->salasAtivas()->count();
                    break;
            }
            $dados['acao'] = isset($acao) ? $acao : '';
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return redirect()->route('representante.agendar.inserir.view')->with([
                'message' => '<i class="fas fa-times"></i>&nbsp;&nbsp;Não existe agendamento com a ID inserida para o seu login.',
                'class' => 'alert-danger'
            ]);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as opções de agendamento das salas.");
        }

        event(new ExternoEvent(isset($acao) ? ' para '.$acao.'.' : '.', 'Agendar Salas'));

        return isset($erro) ? redirect()->route('representante.agendar.inserir.view')->with($erro) : 
        view('site.representante.'. $view, $dados);
    }

    public function salvarAgendamentoSala(RepSalaReuniaoRequest $request, $acao, $id = null)
    {
        try{
            $erro = null;
            $dados = $request->validated();
            $user = auth()->guard('representante')->user();
            switch ($acao) {
                case 'agendar':
                    if(trim($this->gerentiRepository->gerentiStatus($user->ass_id)) != 'Situação: Em dia.')
                        return redirect()->route('representante.agendar.inserir.view')->with([
                            'message' => '<i class="fas fa-exclamation-triangle"></i>&nbsp;Para liberar o seu agendamento entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.',
                            'class' => 'alert-warning'
                        ]);
                    $dados = $this->service->getService('SalaReuniao')->site()->save($dados, $user, $this->service);
                    $erro = isset($dados['message']) ? $dados : $erro;
                    $msg = ['message' => '<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento criado com sucesso! Foi enviado um e-mail com os detalhes.', 
                    'class' => 'alert-success'];
                    break;
                case 'editar':
                    $dados = $this->service->getService('SalaReuniao')->site()->editarParticipantes($dados, $id, $user);
                    $erro = isset($dados['message']) ? $dados : $erro;
                    $msg = ['message' => '<i class="fas fa-check"></i>&nbsp;&nbsp;Participantes foram alterados com sucesso! Foi enviado um e-mail com os detalhes.', 
                    'class' => 'alert-success'];
                    break;
                case 'cancelar':
                    $dados = $this->service->getService('SalaReuniao')->site()->cancelar($id, $user);
                    $erro = isset($dados['message']) ? $dados : $erro;
                    $msg = ['message' => '<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento cancelado com sucesso!', 
                    'class' => 'alert-success'];
                    break;
                case 'justificar':
                    $dados = $this->service->getService('SalaReuniao')->site()->justificar($dados, $id, $user);
                    $erro = isset($dados['message']) ? $dados : $erro;
                    $msg = ['message' => '<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento justificado com sucesso! Está em análise do atendente. Foi enviado um e-mail com a sua justificativa.', 
                    'class' => 'alert-success'];
                    break;
            }
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return redirect()->route('representante.agendar.inserir.view')->with([
                'message' => '<i class="fas fa-times"></i>&nbsp;&nbsp;Não existe agendamento com a ID inserida para o seu login.',
                'class' => 'alert-danger'
            ]);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao salvar agendamento da sala.");
        }

        return redirect()->route('representante.agendar.inserir.view')->with(isset($erro) ? $erro : $msg);
    }
}