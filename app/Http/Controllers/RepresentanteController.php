<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Representante;

class RepresentanteController extends Controller
{
    // Nome da classe
    private $class = 'RepresentanteController';
    // Variáveis
    public $variaveis = [
        'singular' => 'representante',
        'singulariza' => 'o representante',
        'plural' => 'representantes',
        'pluraliza' => 'representantes',
    ];

    public function __construct()
    {
        $this->middleware('auth');
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
                $resultado->cpf_cnpj,
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
}
