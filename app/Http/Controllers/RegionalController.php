<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Regional;
use App\Noticia;

class RegionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
    	$resultados = Regional::orderBy('regional', 'ASC')->paginate(10);
    	// Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Regional',
            'Telefone',
            'Email',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/seccional/'.$resultado->idregional.'" class="btn btn-sm btn-default" target="_blank">Ver</a>';
            $conteudo = [
                $resultado->idregional,
                $resultado->regional,
                $resultado->telefone,
                $resultado->email,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hovered'
        ];
        // Variáveis extras da página
        $variaveis = [
            'singular' => 'regional',
            'plural' => 'regionais'
        ];
        $variaveis = (object) $variaveis;
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function busca()
    {
        $busca = Input::get('q');
        $regionais = Regional::where('regional','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->paginate(10);
        if (count($regionais) > 0) 
            return view('admin.regionais.home', compact('regionais', 'busca'));
        else
            return view('admin.regionais.home')->withMessage('Nenhuma regional encontrada');
    }
}