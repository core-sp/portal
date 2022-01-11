<?php

namespace App\Http\Controllers;

use App\Representante;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use App\Repositories\GerentiApiRepository;
use App\Repositories\GerentiRepositoryInterface;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class RepresentanteController extends Controller
{

    // Nome da classe
    private $class = 'RepresentanteController';
    private $gerentiRepository;
    private $gerentiApiRepository;

    // Variáveis
    public $variaveis = [
        'singular' => 'representante',
        'singulariza' => 'o representante',
        'plural' => 'representantes',
        'pluraliza' => 'representantes',
        'titulo_criar' => 'Buscar Representante no Gerenti',
        'muda_criar' => 'Preencha as informações para buscar no Gerenti',
        'btn_lista' => '<a href="/admin/representantes/buscaGerenti" class="btn btn-primary">Nova Busca</a>'
    ];

    public function __construct(GerentiRepositoryInterface $gerentiRepository, GerentiApiRepository $gerentiApiRepository)
    {
        $this->middleware('auth');
        $this->gerentiRepository = $gerentiRepository;
        $this->gerentiApiRepository = $gerentiApiRepository;
    }

    public function resultados()
    {
        $resultados = Representante::orderBy('id','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'CPF/CNPJ',
            'Nº do Registro',
            'Nome',
            'Email',
            'Ativo'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $resultado->ativo === 0 ? $ativo = 'Não' : $ativo = 'Sim'; 
            $conteudo = [
                $resultado->id,
                '<span class="nowrap">' . $resultado->cpf_cnpj . '</span>',
                $resultado->registro_core,
                $resultado->nome . '<br><small>Cadastro realizado em: ' . formataData($resultado->created_at) . '</small>',
                $resultado->email,
                $ativo
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function busca()
    {
        $this->authorize('viewAny', auth()->user());
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Representante::where('nome','LIKE','%'.$busca.'%')
            ->orWhere('registro_core','LIKE','%'.$busca.'%')
            ->orWhere('cpf_cnpj','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'variaveis', 'tabela', 'busca'));
    }

    public function tabelaGerenti($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Nome',
            'Registro',
            'CPF/CNPJ',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<form method="GET" action="/admin/representantes/info" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="tipo" value="'.$resultado["ASS_TP_ASSOC"].'" />';
            $acoes .= '<input type="hidden" name="nome" value="'.$resultado["ASS_NOME"].'" />';
            $acoes .= '<input type="hidden" name="ass_id" value="'.$resultado["ASS_ID"].'" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-default" value="Detalhes" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado['ASS_NOME'] . ' <strong>(' . Representante::mapaCodigoTipoPessoa($resultado['ASS_TP_ASSOC']) . ')</strong>',
                formataRegistro($resultado['ASS_REGISTRO']),
                '<span class="nowrap">' . formataCpfCnpj($resultado['ASS_CPF_CGC']) . '</span>',
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        // Monta e retorna tabela        
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function buscaGerentiView()
    {
        $this->authorize('viewAny', auth()->user());
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    protected function validateRequest()
    {
        return request()->validate([
            'nome' => 'nullable|min:5|required_without_all:cpf_cnpj,registro',
            'cpf_cnpj' => ['min:11', new CpfCnpj, 'required_without_all:nome,registro'],
            'registro' => 'nullable|min:5|required_without_all:nome,cpf_cnpj'
        ], [
            'nome.min' => 'Preencha no mínimo 5 caracteres',
            'registro.min' => 'Preencha no mínimo 5 caracteres',
            'min' => 'Erro no preenchimento!',
            'required_without_all' => 'Preencha pelo menos um campo'
        ]);
    }
    
    public function buscaGerenti(Request $request)
    {
        $request->merge([
            'registro' => apenasNumeros($request->registro),
            'cpf_cnpj' => apenasNumeros($request->cpf_cnpj)
        ]);
        $this->validateRequest();
        $variaveis = (object) $this->variaveis;
        $resultados = $this->gerentiRepository->gerentiBusca($request->registro, $request->nome, $request->cpf_cnpj);
        $count = count($resultados);
        $count ? $tabela = $this->tabelaGerenti($resultados) : $tabela = 'vazia';
        
        return view('admin.crud.criar', compact('variaveis', 'tabela', 'count'));
    }

    public function representanteInfo(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        $variaveis = (object) $this->variaveis;
        $nome = $request->nome;
        $tipoPessoa = $request->tipo === '2' || $request->tipo === '5' ? Representante::PESSOA_FISICA : Representante::PESSOA_JURIDICA;
        $dados_gerais = $this->gerentiRepository->gerentiDadosGerais($tipoPessoa, $request->ass_id);
        $contatos = $this->gerentiRepository->gerentiContatos($request->ass_id);
        $enderecos = $this->gerentiRepository->gerentiEnderecos($request->ass_id);
        $cobrancas = $this->gerentiRepository->gerentiCobrancas($request->ass_id);
        $situacao = trim(explode(':', $this->gerentiRepository->gerentiStatus($request->ass_id))[1]);
        $certidoes = $this->listarCertidao($request->ass_id);
        $assId = $request->ass_id;
        $valoresRefis = $this->simuladorRefis($request->ass_id);
        
        return view('admin.crud.mostra', compact('variaveis', 'nome', 'situacao', 'dados_gerais', 'contatos', 'enderecos', 'cobrancas', 'certidoes', 'assId', 'valoresRefis'));
    }

    public function listarCertidao($assId) 
    {
        try {
            $responseGetCertidao = $this->gerentiApiRepository->gerentiGetCertidao(($assId));
        }
        catch (Exception $e) {
            Log::error($e->getTraceAsString());

            abort(500, 'Estamos enfrentando problemas técnicos no momento. Por favor, tente dentro de alguns minutos.');
        }

        $certidoes = $responseGetCertidao['data'];

        array_multisort(array_column($certidoes, 'dataEmissao'), SORT_DESC, array_column($certidoes, 'horaEmissao'), SORT_DESC, $certidoes);

        return $certidoes;
    }

    public function baixarCertidao(Request $request) 
    {
        $responseGerentiJson = $this->gerentiApiRepository->gerentiGetCertidao($request->assId);

        $certidoes = $responseGerentiJson['data'];

        $posCertidao = array_search($request->numero, array_column($certidoes, 'numeroDocumento'));

        if($certidoes[$posCertidao]['status'] === 'Emitido') {

            $pdfBase64 = $certidoes[$posCertidao]['base64'];

            header('Content-Type: application/pdf');

            return response()->streamDownload(function () use ($pdfBase64){
                echo base64_decode($pdfBase64);
            }, 'certidao.pdf');
        }
        else {
            $titulo = 'Falha ao baixar certidão';
            $mensagem = 'Não foi possível baixar a certidão.';
            $emitir = false;

            return view("site.representante.emitir-certidao", compact('titulo', 'mensagem', 'emitir'));
        }
    }

    public function simuladorRefis($assId)
    {
        $valoresRefis = $this->gerentiRepository->gerentiValoresRefis($assId);

        return $valoresRefis;
    }
}
