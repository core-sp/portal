<?php

namespace App\Http\Controllers;

use App\Representante;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\RepresentanteEndereco;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ControleController;
use App\Repositories\RepresentanteRepository;
use App\Repositories\GerentiRepositoryInterface;
use App\Repositories\RepresentanteEnderecoRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class RepresentanteEnderecoController extends Controller
{
    use ControleAcesso, TabelaAdmin;

    private $class = 'RepresentanteEnderecoController';
    private $gerentiRepository;
    private $representanteEnderecoRepository;
    private $representanteRepository;

    // Variáveis
    public $variaveis = [
        'singular' => 'solicitação de inclusão de endereço',
        'singulariza' => 'a solicitação de inclusão de endereço',
        'plural' => 'solicitações de inclusões de endereços',
        'pluraliza' => 'solicitações de inclusões de endereços',
        'mostra' => 'representante-endereco',
        'busca' => 'representante-enderecos'
    ];

    public function __construct(GerentiRepositoryInterface $gerentiRepository, RepresentanteEnderecoRepository $representanteEnderecoRepository, RepresentanteRepository $representanteRepository)
    {
        $this->middleware('auth');
        $this->gerentiRepository = $gerentiRepository;
        $this->representanteEnderecoRepository = $representanteEnderecoRepository;
        $this->representanteRepository = $representanteRepository;

    }

    public function resultados()
    {
        $resultados = $this->representanteEnderecoRepository->getAll();

        return $resultados;
    }

    public function show($id)
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->representanteEnderecoRepository->getById($id);
        $representante = $this->representanteRepository->getByAssId($resultado->ass_id);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.mostra', compact('resultado', 'variaveis', 'representante'));
    }

    public function inserirEnderecoGerenti(Request $request)
    {
        $this->gerentiRepository->gerentiInserirEndereco($request->ass_id, unserialize($request->infos));

        $this->representanteEnderecoRepository->updateStatusEnviado($request->id);

        event(new CrudEvent('endereço representante', 'enviou para o Gerenti', $request->id));

        return redirect('/admin/representante-enderecos')
                ->with('message', 'O endereço foi cadastrado com sucesso no Gerenti.')
                ->with('class', 'alert-success');
    }

    public function recusarEndereco(Request $request)
    {
        $this->representanteEnderecoRepository->updateStatusRecusado($request->id, $request->observacao);

        event(new CrudEvent('endereço representante', 'recusou', $request->id));

        return redirect('/admin/representante-enderecos')
                ->with('message', 'A atualização de endereço foi recusada.')
                ->with('class', 'alert-info');
    }

    public function visualizarComprovante(Request $request) 
    {
        if(Storage::exists("representantes/enderecos/" . $request->nome)) {
            return response()->file(Storage::path("representantes/enderecos/" . $request->nome), ["Cache-Control" => "no-cache"]);
        }
        else {
            abort(404);
        }
        
    }

    public function baixarComprovante(Request $request) 
    {
        if(Storage::exists("representantes/enderecos/" . $request->nome)) {
            return Storage::download("/representantes/enderecos/" . $request->nome, $request->nome);
        }
        else {
            abort(404);
        }
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'ID do Representante',
            'Solicitado em:',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/representante-enderecos/' . $resultado->id . '" class="btn btn-sm btn-default">Ver</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->ass_id,
                formataData($resultado->created_at),
                $this->showStatus($resultado->status),
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
        $tabela = $this->montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    protected function showStatus($string)
    {
        switch ($string) {
            case RepresentanteEndereco::STATUS_AGUARDANDO_CONFIRMACAO:
                return '<strong><i>Aguardando confirmação</i></strong>';
            break;

            case RepresentanteEndereco::STATUS_RECUSADO:
                return '<strong class="text-danger">Recusado</strong>';
            break;

            case RepresentanteEndereco::STATUS_ENVIADO:
                return '<strong class="text-success">Enviado</strong>';
            break;
            
            default:
                return $string;
            break;
        }
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');

        $busca = IlluminateRequest::input('q');

        $resultados = $this->representanteEnderecoRepository->getBusca($busca);
        
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
