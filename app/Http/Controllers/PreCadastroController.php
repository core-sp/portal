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

        return view('admin.crud.mostra', compact('resultado', 'variaveis'));
    }

    public function create()
    {
        return view('site.pre-cadastro');
    }

    public function store(PreCadastroRequest $request)
    {
        $nomeAnexo1 = 'anexo_1.' . $request->file('anexo1')->getClientOriginalExtension();

        $nomeAnexo2 = 'anexo_2.' . $request->file('anexo2')->getClientOriginalExtension();

        $preCadastro = $this->preCadastroRepository->store($request, $nomeAnexo1, $nomeAnexo2);

        $request->file('anexo1')->storeAs('pre-cadastro/' . $preCadastro->id , $nomeAnexo1);

        $request->file('anexo2')->storeAs('pre-cadastro/' . $preCadastro->id , $nomeAnexo2);
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

    public function visualizarAnexo(Request $request, $id) 
    {
        if(Storage::exists('pre-cadastro/' . $id . '/' . $request->nomeArquivo)) {
            return response()->file(Storage::path('pre-cadastro/' . $id . '/' . $request->nomeArquivo), ["Cache-Control" => "no-cache"]);
        }
        else {
            abort(404);
        }
    }

    public function baixarAnexo(Request $request, $id) 
    {
        if(Storage::exists('pre-cadastro/' . $id . '/' . $request->nomeArquivo)) {
            return Storage::download('pre-cadastro/' . $id . '/' . $request->nomeArquivo, $request->nomeArquivo);
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
            'CPF',
            'Solicitado em:', 
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];

        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/pre-cadastro/mostrar/'.$resultado->id.'" class="btn btn-sm btn-default">Ver</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->cpf,
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
