<?php

namespace App\Http\Controllers;

use App\PreCadastro;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreCadastroAprovadoMail;
use App\Mail\PreCadastroRecusadoMail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PreCadastroRequest;
use App\Repositories\PreCadastroRepository;

class PreCadastroController extends Controller
{
    use ControleAcesso, TabelaAdmin;
    
    private $preCadastroRepository;
    private $variaveis;

    public function __construct(PreCadastroRepository $preCadastroRepository)
    {
        $this->middleware('auth', ['except' => ['create', 'store']]);

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
        $nomeArquivo;

        foreach($anexos as $anexo) {
            if (strpos($anexo, 'CPF') !== false) {
                $nomeArquivo = 'CPF';
            }
            elseif (strpos($anexo, 'RG') !== false) {
                $nomeArquivo = 'RG';
            }
            elseif (strpos($anexo, 'CNH') !== false) {
                $nomeArquivo = 'CNH';
            }
            elseif (strpos($anexo, 'Comprovante_Residencia') !== false) {
                $nomeArquivo = 'Comprovante de Residência';
            }
            elseif (strpos($anexo, 'Certidao_Quitacao_Eleitoral') !== false) {
                $nomeArquivo = 'Certidão de Quitação Eleitoral';
            }
            elseif (strpos($anexo, 'Reservista_Militar') !== false) {
                $nomeArquivo = 'Reservista Militar';
            }

            $listaAnexos[$nomeArquivo ] = $anexo;
        }

        return view('admin.crud.mostra', compact('resultado', 'variaveis', 'listaAnexos'));
    }

    public function create()
    {
        return view('site.pre-cadastro');
    }

    public function store(PreCadastroRequest $request)
    {
        $preCadastro = $this->preCadastroRepository->store($request);

        $nomeAnexoCpf = $preCadastro->id . '_' . 'CPF.' . $request->file('anexoCpf')->getClientOriginalExtension();
        $request->file('anexoCpf')->storeAs('pre-cadastro/' . $preCadastro->id , $nomeAnexoCpf);
        
        $nomeAnexoDocumento = $preCadastro->id . '_' . $preCadastro->tipoDocumento . '.' . $request->file('anexoDocumento')->getClientOriginalExtension();
        $request->file('anexoDocumento')->storeAs('pre-cadastro/' . $preCadastro->id , $nomeAnexoDocumento);

        $nomeAnexoComprovanteResidencia = $preCadastro->id . '_' . 'Comprovante_Residencia.' . $request->file('anexoComprovanteResidencia')->getClientOriginalExtension();
        $request->file('anexoComprovanteResidencia')->storeAs('pre-cadastro/' . $preCadastro->id , $nomeAnexoComprovanteResidencia);

        $nomeAnexoCertidaoQuitacaoEleitoral = $preCadastro->id . '_' . 'Certidao_Quitacao_Eleitoral.' . $request->file('anexoCertidaoQuitacaoEleitoral')->getClientOriginalExtension();
        $request->file('anexoCertidaoQuitacaoEleitoral')->storeAs('pre-cadastro/' . $preCadastro->id , $nomeAnexoCertidaoQuitacaoEleitoral);

        if($request->file('anexoReservistaMilitar') !== null) {
            $nomeAnexoComprovanteResidencia = $preCadastro->id . '_' . 'Reservista_Militar.' . $request->file('anexoReservistaMilitar')->getClientOriginalExtension();
            $request->file('anexoReservistaMilitar')->storeAs('pre-cadastro/' . $preCadastro->id , $nomeAnexoComprovanteResidencia);
        }
    }

    public function atualizarStatus(Request $request)
    {
        $preCadastro = $this->preCadastroRepository->getById($request->input('id'));

        // TODO - Investigar forma melhor de verificação de status (usuário modificando request manualmente)
        if($request->input('status') == PreCadastro::STATUS_APROVADO) {
            $this->preCadastroRepository->updateStatus($preCadastro, PreCadastro::STATUS_APROVADO);

            $email = new PreCadastroAprovadoMail();
        }
        elseif($request->input('status') == PreCadastro::STATUS_RECUSADO) {
            $this->preCadastroRepository->updateStatus($preCadastro, PreCadastro::STATUS_RECUSADO, $request->input('motivo'));

            $email = new PreCadastroRecusadoMail($preCadastro->motivo);
        }

        Mail::to($preCadastro->email)->queue($email);
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
                $resultado->status,
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
}
