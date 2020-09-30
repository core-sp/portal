<?php

namespace App\Http\Controllers;

use App\Representante;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use App\Traits\GerentiProcedures;
use Illuminate\Support\Facades\Request as FacadesRequest;

class RepresentanteController extends Controller
{
    use GerentiProcedures;

    // Nome da classe
    private $class = 'RepresentanteController';
    private $representante;

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

    public function __construct(Representante $representante)
    {
        $this->middleware('auth');
        $this->representante = $representante;
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
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function busca()
    {
        ControleController::autoriza($this->class, 'index');
        $busca = preg_replace('/[^0-9A-Za-z]+/', '', FacadesRequest::input('q'));
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
            $acoes = '<form method="POST" action="/admin/representantes/info" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="tipo" value="'.$resultado["ASS_TP_ASSOC"].'" />';
            $acoes .= '<input type="hidden" name="nome" value="'.$resultado["ASS_NOME"].'" />';
            $acoes .= '<input type="hidden" name="ass_id" value="'.$resultado["ASS_ID"].'" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-default" value="Detalhes" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado['ASS_NOME'] . ' <strong>(' . stringTipoPessoa($resultado['ASS_TP_ASSOC']) . ')</strong>',
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
        ControleController::autoriza($this->class, 'index');
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
            'registro' => preg_replace('/[^0-9]/', '', $request->registro),
            'cpf_cnpj' => preg_replace('/[^0-9]/', '', $request->cpf_cnpj)
        ]);
        $this->validateRequest();
        $variaveis = (object) $this->variaveis;
        $resultados = $this->gerentiBusca($request->registro, $request->nome, $request->cpf_cnpj);
        $count = count($resultados);
        $count ? $tabela = $this->tabelaGerenti($resultados) : $tabela = 'vazia';
        return view('admin.crud.criar', compact('variaveis', 'tabela', 'count'));
    }

    public function representanteInfo(Request $request)
    {
        ControleController::autoriza($this->class, 'index');
        $variaveis = (object) $this->variaveis;
        $nome = $request->nome;
        $request->tipo === '2' || $request->tipo === '5' ? $dados_gerais = $this->representante::arrangeDgPf($this->gerentiDadosGeraisPF($request->ass_id)) : $dados_gerais = $this->representante::arrangeDgPj($this->gerentiDadosGeraisPJ($request->ass_id));
        $contatos = $this->gerentiContatos($request->ass_id);
        $enderecos = utf8_converter($this->gerentiEnderecos($request->ass_id));
        $cobrancas = $this->representante->cobrancasById($request->ass_id);
        $situacao = $this->representante->pegaSituacao($request->ass_id);
        
        return view('admin.crud.mostra', compact('variaveis', 'nome', 'situacao', 'dados_gerais', 'contatos', 'enderecos', 'cobrancas'));
    }
}
