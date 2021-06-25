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
        //$this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->preCadastroRepository->getToTable();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function show($id)
    {
        //$this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->preCadastroRepository->getById($id);
        $variaveis = (object) $this->variaveis;
        $anexos = Storage::files('pre-cadastro/' . $id . '/');
        $listaAnexos = [];

        foreach($anexos as $anexo) {
            $listaAnexos[explode('/', $anexo)[2]] = $anexo;
        }

        return view('admin.crud.mostra', compact('resultado', 'variaveis', 'listaAnexos'));
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
        $regras = $this->regrasByTipo($request->tipo);
        $mensagens = $this->mensagensByTipo($request->tipo);

        $validacao = Validator::make($request->all(), $regras, $mensagens);
        
        if($validacao->fails()) {
            return Redirect::back()->withErrors($validacao)->withInput($request->all());
        }

        $preCadastro = $this->preCadastroRepository->store($request);

        $this->salvarAnexos($request, $preCadastro->id);

        $email = new PreCadastroMail();

        Mail::to($preCadastro->email)->queue($email);

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

    protected function regrasByTipo($tipo) 
    {
        switch ($tipo) {
            case PreCadastro::TIPO_PRE_CADASTRO_PF_AUTONOMA:
                return [
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
                    'anexoCpf' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoDocumento' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoComprovanteResidencia' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoCertidaoQuitacaoEleitoral' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoReservistaMilitar' => 'mimes:jpeg,png,jpg,gif,pdf|max:2048'
                ];
            break;
            case PreCadastro::TIPO_PRE_CADASTRO_PF_RT:
                return [
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
                    'anexoCpf' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoDocumento' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoComprovanteResidencia' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoCertidaoQuitacaoEleitoral' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoReservistaMilitar' => 'mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'cnpj' => ['required', new Cnpj],
                    'razaoSocial' => 'required|max:191',
                    'anexoIndicacaoRT' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048'
                ];
            break;
            case PreCadastro::TIPO_PRE_CADASTRO_PJ_INDIVIDUAL:
                return [
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
                    'anexoCpf' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoDocumento' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoComprovanteResidencia' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'cnpj' => ['required', new Cnpj],
                    'razaoSocial' => 'required|max:191',
                    'formaRegistro' => 'required|max:191',
                    'numeroRegistro' => 'required|max:191',
                    'dataRegistro' => 'required|date_format:d/m/Y',
                    'ramoAtividade' => 'required|max:191',
                    'capitalSocial' => 'required|max:191',
                    'anexoCnpj' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoRequerimentoFirmaIndividual' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048'
                ];
            break;
            case PreCadastro::TIPO_PRE_CADASTRO_PJ_VARIADO:
                return [
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
                    'cnpj' => ['required', new Cnpj],
                    'razaoSocial' => 'required|max:191',
                    'formaRegistro' => 'required|max:191',
                    'numeroRegistro' => 'required|max:191',
                    'dataRegistro' => 'required|date_format:d/m/Y',
                    'ramoAtividade' => 'required|max:191',
                    'capitalSocial' => 'required|max:191',
                    'anexoCnpj' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoContratoSocial' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoCpfQuadro' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoDocumentoQuadro' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                    'anexoComprovanteResidenciaQuadro' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                ];
            break;
        }
    }

    protected function mensagensByTipo($tipo) 
    {
        return [
            'required' => 'Campo obrigatório',
            'max' => 'Excedido limite de caracteres',
            'mimes' => 'Tipo de arquivo não suportado',
            'max' => 'Arquivo não pode ultrapassar 2MB',
            'date_format' => 'Data inválida',
            'upload' => 'Arquivo não pode ultrapassar 2MB'
        ];

        // switch ($tipo) {
        //     case PreCadastro::TIPO_PRE_CADASTRO_PF_AUTONOMA:
        //         return [
        //             'required' => 'Campo obrigatório',
        //             'max' => 'Excedido limite de caracteres',
        //             'mimes' => 'Tipo de arquivo não suportado',
        //             'max' => 'Arquivo não pode ultrapassar 2MB',
        //             'date_format' => 'Data inválida',
        //         ];
        //     break;
        // }
    }

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

    public function atualizarStatus(Request $request)
    {
        $preCadastro = $this->preCadastroRepository->updateStatus($request->input('id'), $request->input('status'), $request->input('motivo'));

        if($preCadastro->status == PreCadastro::STATUS_APROVADO) {

            $email = new PreCadastroAprovadoMail();
            $mensagem = 'Requisição de pré-cadastro aprovada.';
            $class = 'alert-success';
            event(new CrudEvent('pré-cadastro', 'aprovou', $preCadastro->id));
        }
        elseif($preCadastro->status == PreCadastro::STATUS_RECUSADO) {

            $email = new PreCadastroRecusadoMail($preCadastro->motivo);
            $mensagem = 'Requisição de pré-cadastro recusada.';
            $class = 'alert-danger';
            event(new CrudEvent('pré-cadastro', 'recusou', $preCadastro->id));
        }

        Mail::to($preCadastro->email)->queue($email);

        return redirect(route('pre-cadastro.index'))
                ->with('message', $mensagem)
                ->with('class', $class);
    }

    public function visualizarAnexo(Request $request) 
    {
        if(Storage::exists($request->arquivo)) {
            return response()->file(Storage::path($request->arquivo), ["Cache-Control" => "no-cache"]);
        }
        else {
            abort(404);
        }
    }

    public function baixarAnexo(Request $request) 
    {
        if(Storage::exists($request->arquivo)) {
            return Storage::download($request->arquivo);
        }
        else {
            abort(404);
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
            $acoes = '<a href="/admin/pre-cadastro/mostrar/'.$resultado->id.'" class="btn btn-sm btn-default">Ver</a> ';
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
