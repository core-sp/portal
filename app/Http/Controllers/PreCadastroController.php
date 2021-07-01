<?php

namespace App\Http\Controllers;

use App\Rules\Cpf;
use App\Rules\Cnpj;
use App\PreCadastro;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\Mail\PreCadastroMail;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreCadastroAprovadoMail;
use App\Mail\PreCadastroRecusadoMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\PreCadastroRequest;
use Illuminate\Support\Facades\Validator;
use App\Repositories\PreCadastroRepository;

class PreCadastroController extends Controller
{
    use ControleAcesso, TabelaAdmin;
    
    private $class = 'PreCadastroController';
    private $preCadastroRepository;
    private $variaveis;

    public function __construct(PreCadastroRepository $preCadastroRepository)
    {
        $this->middleware('auth', ['except' => ['createPFAutonomo', 'createPFRT', 'createPJFirmaIndividual', 'createPJVariado', 'store']]);

        $this->preCadastroRepository = $preCadastroRepository;

        $this->variaveis = [
            'singular' => 'solicitação de pré-cadastro',
            'singulariza' => 'a solicitação de inclusão de pré-cadastro',
            'plural' => 'solicitações de pré-cadastro',
            'pluraliza' => 'solicitações de pré-cadastro',
            'mostra' => 'pre-cadastro'
        ];
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->preCadastroRepository->getToTable();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function show($id)
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->preCadastroRepository->getById($id);
        $variaveis = (object) $this->variaveis;
        $anexos = Storage::files('pre-cadastro/' . $id . '/');
        $listaAnexos = [];

        // Caso o pré-cadastro esteja pendente, recupera os anexos da solicitação para mostrar na tela
        if($resultado->status === PreCadastro::STATUS_PENDENTE) {
            foreach($anexos as $anexo) {
                $listaAnexos[explode('/', $anexo)[2]] = $anexo;
            }
        }

        return view('admin.crud.mostra', compact('resultado', 'variaveis', 'listaAnexos'));
    }

    public function atualizarStatus(Request $request)
    {
        $this->autoriza($this->class, 'edit');

        if($request->status == PreCadastro::STATUS_APROVADO) {

            $preCadastro = $this->preCadastroRepository->updateStatus($request->id, $request->status, null);

            $email = new PreCadastroAprovadoMail();
            $mensagem = 'Requisição de pré-cadastro aprovada.';
            $class = 'alert-success';
            event(new CrudEvent('pré-cadastro', 'aprovou', $preCadastro->id));
        }
        elseif($request->status == PreCadastro::STATUS_RECUSADO) {

            // Em caso de recusa, é obrigatório fornecer o motivo
            if($request->motivo === null || trim($request->motivo) === '') {
                return Redirect::back()
                    ->with('message', 'Motivo é obrigatório quando recusando solicitação!')
                    ->with('class', 'alert-danger');
            }

            $preCadastro = $this->preCadastroRepository->updateStatus($request->id, $request->status, $request->motivo);

            $email = new PreCadastroRecusadoMail($preCadastro->motivo);
            $mensagem = 'Requisição de pré-cadastro recusada.';
            $class = 'alert-danger';
            event(new CrudEvent('pré-cadastro', 'recusou', $preCadastro->id));
        }

        // Envia e-mail ao solicitante informando o resultado da requisição de pré-cadastro
        Mail::to($preCadastro->email)->queue($email);

        return redirect(route('pre-cadastro.index'))
                ->with('message', $mensagem)
                ->with('class', $class);
    }

    public function visualizarAnexo(Request $request) 
    {
        $this->autoriza($this->class, 'show');

        if(Storage::exists($request->arquivo)) {
            return response()->file(Storage::path($request->arquivo), ["Cache-Control" => "no-cache"]);
        }
        else {
            abort(404);
        }
    }

    public function baixarAnexo(Request $request) 
    {
        $this->autoriza($this->class, 'show');

        if(Storage::exists($request->arquivo)) {
            return Storage::download($request->arquivo);
        }
        else {
            abort(404);
        }
    }

    public function createPFAutonomo()
    {
        $tipo = PreCadastro::TIPO_PRE_CADASTRO_PF_AUTONOMA;

        return view('site.pre-cadastro-pf', compact('tipo'));
    }

    public function createPFRT()
    {
        $tipo = PreCadastro::TIPO_PRE_CADASTRO_PF_RT;

        return view('site.pre-cadastro-pf', compact('tipo'));
    }

    public function createPJFirmaIndividual()
    {
        $tipo = PreCadastro::TIPO_PRE_CADASTRO_PJ_INDIVIDUAL;

        return view('site.pre-cadastro-pj', compact('tipo'));
    }

    public function createPJVariado()
    {
        $tipo = PreCadastro::TIPO_PRE_CADASTRO_PJ_VARIADO;

        return view('site.pre-cadastro-pj', compact('tipo'));
    }

    public function store(Request $request)
    {
        $regras = $this->regras($request);
        $mensagens = $this->mensagens();

        // Valida os campos de acordo com as regras
        $validacao = Validator::make($request->all(), $regras, $mensagens);
        
        // Caso algum erro ocorra, retorna para a tela do fomulário com mensagens de erro
        if($validacao->fails()) {
            return Redirect::back()->withErrors($validacao)->withInput($request->all());
        }

        $preCadastro = $this->preCadastroRepository->store($request);

        $this->salvarAnexos($request, $preCadastro->id);

        // Envia e-mail ao solicitante confirmando a requisição de pré-cadastro
        $email = new PreCadastroMail();
        Mail::to($preCadastro->email)->queue($email);

        // Geranto log da solicitação de pré-cadastro
        $string = $preCadastro->nome . " (CPF: " . $preCadastro->cpf . ")";
        $string .= " requisitou pré-cadastro para " . $preCadastro->tipo;
        event(new ExternoEvent($string));

        $texto = "<strong>Sua requisição de pré-cadastro foi submetida com sucesso!</strong>";
        $texto  .= "<br>";
        $texto  .= "Análise será realizada em 10 dias e resultado retornado por e-mail.";

        // Retorna view de agradecimento
        return view('site.agradecimento')->with([
            'agradece' => $texto,
        ]);
    }

    /**
     * Método que retorna as regras para validar o request da solicitação de pré-cadastro de acordo 
     * com o tipo.
     */
    protected function regras($request) 
    {
        // Regras usadas em todos os tipos
        $regras = [
            'nome' => 'required|max:191',
            'cpf' => ['required', new Cpf],
            'tipoDocumento' => 'required|max:191',
            'numeroDocumento' => 'required|max:191',
            'orgaoEmissor' => 'required|max:191',
            'dataExpedicao' => 'required|date_format:d/m/Y',
            'dataNascimento' => 'required|date_format:d/m/Y',
            'estadoCivil' => 'required|max:191',
            'sexo' => 'required|max:191',
            'naturalizado' => 'required|max:191',
            'nacionalidade' => 'required|max:191',
            'nomeMae' => 'required|max:191',
            'nomePai' => 'required|max:191',
            'email' => 'required|email|max:191',
            'celular' => 'max:191|min:14',
            'telefoneFixo' => 'max:191',
            'segmento' => 'required|max:191',
            'cep' => 'required',
            'bairro' => 'required|max:30',
            'logradouro' => 'required|max:100',
            'numero' => 'required|max:15',
            'complemento' => 'max:100',
            'estado' => 'required|max:5',
            'municipio' => 'required|max:30',
            'g-recaptcha-response' => 'required|recaptcha'
        ];

        switch ($request->tipo) {
            case PreCadastro::TIPO_PRE_CADASTRO_PF_AUTONOMA:
                $regras['anexoCpf'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoDocumento'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoComprovanteResidencia'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoCertidaoQuitacaoEleitoral'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                
                if(formataDataUTC($request->dataNascimento) <= date('Y-m-d', strtotime('-46 years'))) {
                    $regras['anexoReservistaMilitar'] = 'mimes:jpeg,png,jpg,gif,pdf|max:2048';
                }
                else {
                    $regras['anexoReservistaMilitar'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                }
            break;

            case PreCadastro::TIPO_PRE_CADASTRO_PF_RT:
                $regras['cnpj'] = ['required', new Cnpj];
                $regras['razaoSocial'] = 'required|max:191';
                $regras['anexoCpf'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoDocumento'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoComprovanteResidencia'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoCertidaoQuitacaoEleitoral'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoIndicacaoRT'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';

                if(formataDataUTC($request->dataNascimento) <= date('Y-m-d', strtotime('-46 years'))) {
                    $regras['anexoReservistaMilitar'] = 'mimes:jpeg,png,jpg,gif,pdf|max:2048';
                }
                else {
                    $regras['anexoReservistaMilitar'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                }
            break;

            case PreCadastro::TIPO_PRE_CADASTRO_PJ_INDIVIDUAL:
                $regras['cnpj'] = ['required', new Cnpj];
                $regras['razaoSocial'] = 'required|max:191';
                $regras['formaRegistro'] = 'required|max:191';
                $regras['numeroRegistro'] = 'required|max:191';
                $regras['dataRegistro'] = 'required|date_format:d/m/Y';
                $regras['ramoAtividade'] = 'required|max:191';
                $regras['capitalSocial'] = 'required|max:191';
                $regras['anexoCpf'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoCnpj'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoDocumento'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoComprovanteResidencia'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoRequerimentoFirmaIndividual'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
            break;

            case PreCadastro::TIPO_PRE_CADASTRO_PJ_VARIADO:
                $regras['cnpj'] = ['required', new Cnpj];
                $regras['razaoSocial'] = 'required|max:191';
                $regras['formaRegistro'] = 'required|max:191';
                $regras['numeroRegistro'] = 'required|max:191';
                $regras['dataRegistro'] = 'required|date_format:d/m/Y';
                $regras['ramoAtividade'] = 'required|max:191';
                $regras['capitalSocial'] = 'required|max:191';
                $regras['anexoCnpj'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoContratoSocial'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoCpfQuadro'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoDocumentoQuadro'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
                $regras['anexoComprovanteResidenciaQuadro'] = 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048';
            break;

            // Caso tipo fornecido não seja válido, aborta e com mensagem deerro.
            default:
                abort(500, 'Tipo de pré-cadastro inválido.');
            break;
        }

        return $regras;
    }

    /**
     * Método para retornar mensagens de erro de acordo com as regras.
     */
    protected function mensagens() 
    {
        return [
            'required' => 'Campo obrigatório',
            'max' => 'Excedido limite de caracteres',
            'mimes' => 'Tipo de arquivo não suportado',
            'max' => 'Arquivo não pode ultrapassar 2MB',
            'date_format' => 'Data inválida',
            'upload' => 'Falha ao fazer o upload do arquivo',
            'g-recaptcha-response' => 'ReCAPTCHA inválido',
            'g-recaptcha-response.required' => 'ReCAPTCHA obrigatório'
        ];
    }

    /**
     * Método usado para salvar os arquivos de upload no servidor. Renomeia e cria os diretórios de acordo 
     * com o documento e o id da solicitação do pré-cadastro.
     */
    protected function salvarAnexos($request, $preCadastroId) 
    {
        if($request->file('anexoCpf') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'CPF.' . $request->file('anexoCpf')->getClientOriginalExtension();
            $request->file('anexoCpf')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }
        
        if($request->file('anexoDocumento') !== null) {
            $nomeAnexo = $preCadastroId . '_' . $request->tipoDocumento . '.' . $request->file('anexoDocumento')->getClientOriginalExtension();
            $request->file('anexoDocumento')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoComprovanteResidencia') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'Comprovante_Residencia.' . $request->file('anexoComprovanteResidencia')->getClientOriginalExtension();
            $request->file('anexoComprovanteResidencia')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoCertidaoQuitacaoEleitoral') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'Certidao_Quitacao_Eleitoral.' . $request->file('anexoCertidaoQuitacaoEleitoral')->getClientOriginalExtension();
            $request->file('anexoCertidaoQuitacaoEleitoral')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoReservistaMilitar') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'Reservista_Militar.' . $request->file('anexoReservistaMilitar')->getClientOriginalExtension();
            $request->file('anexoReservistaMilitar')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoCnpj') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'CNPJ.' . $request->file('anexoCnpj')->getClientOriginalExtension();
            $request->file('anexoCnpj')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoRequerimentoFirmaIndividual') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'Requerimento_Firma_Individual.' . $request->file('anexoRequerimentoFirmaIndividual')->getClientOriginalExtension();
            $request->file('anexoRequerimentoFirmaIndividual')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoContratoSocial') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'Contrato_Social.' . $request->file('anexoContratoSocial')->getClientOriginalExtension();
            $request->file('anexoContratoSocial')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoCpfQuadro') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'CPF_Quadro_Societario.' . $request->file('anexoCpfQuadro')->getClientOriginalExtension();
            $request->file('anexoCpfQuadro')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoDocumentoQuadro') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'Documento_Quadro_Societario.' . $request->file('anexoDocumentoQuadro')->getClientOriginalExtension();
            $request->file('anexoDocumentoQuadro')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }

        if($request->file('anexoComprovanteResidenciaQuadro') !== null) {
            $nomeAnexo = $preCadastroId . '_' . 'Comprovante_Residencia_Quadro_Societario.' . $request->file('anexoComprovanteResidenciaQuadro')->getClientOriginalExtension();
            $request->file('anexoComprovanteResidenciaQuadro')->storeAs('pre-cadastro/' . $preCadastroId , $nomeAnexo);
        }
    }

    protected function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Tipo Pré-Cadastro',
            'CPF/CNPJ',
            'Solicitado em', 
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];

        foreach($resultados as $resultado) {
            $acoes = '';
            if($this->mostra($this->class, 'show')) {
                $acoes .= '<a href="/admin/pre-cadastro/mostrar/'.$resultado->id.'" class="btn btn-sm btn-default">Ver</a> ';
            }
            
            $cpfCnpj = $resultado->tipo === PreCadastro::TIPO_PRE_CADASTRO_PF_AUTONOMA || $resultado->tipo === PreCadastro::TIPO_PRE_CADASTRO_PF_RT ? $resultado->cpf : $resultado->cnpj;
            
            $conteudo = [
                $resultado->id,
                $resultado->tipo,
                $cpfCnpj,
                formataData($resultado->created_at),
                $this->showStatus($resultado->status),
                $acoes
            ];
            array_push($contents, $conteudo);
        }

        // Classes da tabela
        $classes = [
            'table',
            'table-bordered',
            'table-striped'
        ];
        $tabela = $this->montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }

    protected function showStatus($status)
    {
        switch ($status) {
            case PreCadastro::STATUS_PENDENTE:
                return '<strong><i>' . $status . '</i></strong>';
            break;

            case PreCadastro::STATUS_RECUSADO:
                return '<strong class="text-danger">' . $status . '</strong>';
            break;

            case PreCadastro::STATUS_APROVADO:
                return '<strong class="text-success">' . $status . '</strong>';
            break;
            
            default:
                return $status;
            break;
        }
    }
}
