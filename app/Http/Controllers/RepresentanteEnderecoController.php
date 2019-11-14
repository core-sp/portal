<?php

namespace App\Http\Controllers;

use App\Events\CrudEvent;
use App\Representante;
use App\RepresentanteEndereco;
use App\Traits\GerentiProcedures;
use Illuminate\Http\Request;
use App\Http\Controllers\ControleController;

class RepresentanteEnderecoController extends Controller
{
    use GerentiProcedures;

    // Nome da classe
    private $class = 'RepresentanteEnderecoController';
    // Variáveis
    public $variaveis = [
        'singular' => 'solicitação de inclusão de endereço',
        'singulariza' => 'a solicitação de inclusão de endereço',
        'plural' => 'solicitações de inclusões de endereços',
        'pluraliza' => 'solicitações de inclusões de endereços',
        'mostra' => 'representante-endereco'
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function resultados()
    {
        $resultados = RepresentanteEndereco::orderBy('id','DESC')->paginate(10);
        return $resultados;
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
            $acoes = '<a href="/admin/representante-enderecos/'.$resultado->id.'" class="btn btn-sm btn-default">Ver</a> ';
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
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    protected function showStatus($string)
    {
        switch ($string) {
            case 'Aguardando confirmação':
                return '<strong><i>Aguardando confirmação</i></strong>';
            break;

            case 'Recusado':
                return '<strong class="text-danger">Recusado</strong>';
            break;

            case 'Enviado':
                return '<strong class="text-success">Enviado</strong>';
            break;
            
            default:
                return $string;
            break;
        }
    }

    public function index()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function show($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = RepresentanteEndereco::find($id);
        $representante = Representante::where('ass_id', '=', $resultado->ass_id)->first();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.mostra', compact('resultado', 'variaveis', 'representante'));
    }

    public function inserirEnderecoGerenti()
    {
        $this->gerentiInserirEndereco(request('ass_id'), unserialize(request('infos')));

        $endereco = RepresentanteEndereco::find(request('id'));

        $endereco->update([
            'status' => 'Enviado'
        ]);

        event(new CrudEvent('endereço representante', 'enviou para o Gerenti', $endereco->id));

        return redirect('/admin/representante-enderecos')
                ->with('message', 'O endereço foi cadastrado com sucesso no Gerenti.')
                ->with('class', 'alert-success');
    }

    public function recusarEndereco()
    {
        $endereco = RepresentanteEndereco::find(request('id'));

        $endereco->update([
            'status' => 'Recusado'
        ]);

        event(new CrudEvent('endereço representante', 'recusou', $endereco->id));

        return redirect('/admin/representante-enderecos')
                ->with('message', 'A atualização de endereço foi recusada.')
                ->with('class', 'alert-info');
    }
}
