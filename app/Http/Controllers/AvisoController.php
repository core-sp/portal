<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\CrudEvent;
use App\Http\Requests\AvisoRequest;
use App\Repositories\AvisoRepository;
use App\Traits\ControleAcesso;
use App\Traits\TabelaAdmin;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class AvisoController extends Controller
{
    use ControleAcesso, TabelaAdmin;

    // Nome da classe
    private $class = 'AvisoController';
    private $avisoRepository;
    private $variaveis = [
        'singular' => 'aviso',
        'singulariza' => 'o aviso',
        'plural' => 'avisos',
        'pluraliza' => 'avisos',
        'form' => 'aviso'
    ];

    public function __construct(AvisoRepository $avisoRepository)
    {
        $this->middleware('auth');
        $this->avisoRepository = $avisoRepository;
    }  

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultados = $this->avisoRepository->getAll();
        $variaveis = (object) $this->variaveis;
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('tabela', 'resultados', 'variaveis'));
    }

    public function show($id)
    {
        $this->autoriza($this->class, 'index');
        $resultado = $this->avisoRepository->getById($id);
        $this->variaveis['singulariza'] = 'o aviso da área do ' .$resultado->area;
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.mostra', compact('resultado', 'variaveis'));
    }

    public function edit($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultado = $this->avisoRepository->getById($id);
        $variaveis = (object) $this->variaveis;
        $cores = $this->avisoRepository->cores();
        return view('admin.crud.editar', compact('resultado', 'variaveis', 'cores'));
    }

    public function update(AvisoRequest $request, $id)
    {
        $this->autoriza($this->class, 'edit');
        $request->validated();
        $update = $this->avisoRepository->update($request, $id, auth()->user());
        if(!$update)
            abort(500, 'Erro ao atualizar o aviso');

        event(new CrudEvent('aviso', 'editou', $id));

        return redirect(route('avisos.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Aviso editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function updateStatus($id)
    {
        $this->autoriza($this->class, 'edit');
        $update = $this->avisoRepository->updateCampoStatus($id, auth()->user());
        if(!$update)
            abort(500, 'Erro ao atualizar o status do aviso');

        $aviso = $this->avisoRepository->getById($id);
        event(new CrudEvent('aviso', 'editou o status para ' . $aviso->status, $id));

        return redirect(route('avisos.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Aviso foi ' .$aviso->status. ' com sucesso!')
            ->with('class', 'alert-success');
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Id',
            'Área',
            'Título',
            'Última Atualização',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $statusDesejado = $resultado->isAtivado() ? 'Desativar' : 'Ativar';
            $botao = $resultado->isAtivado() ? 'btn btn-sm btn-danger' : 'btn btn-sm btn-success';

            $acoes = ' <a href="' .route('avisos.show', $resultado->id). '" class="btn btn-sm btn-default">Ver</a> ';
            $acoes .= '<a href="' .route('avisos.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="' .route('avisos.editar.status', $resultado->id). '" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="put" />';
            $acoes .= '<input type="submit" class="' .$botao. '" value="' .$statusDesejado. '" 
            onclick="return confirm(\'Tem certeza que deseja ' .$statusDesejado. ' o aviso?\')" />';
            $acoes .= '</form>';

            $user = isset($resultado->user) ? $resultado->user->nome : '------------';
            $conteudo = [
                $resultado->id,
                $resultado->area,
                $resultado->titulo,
                formataData($resultado->updated_at). '<br><small>Por: ' .$user. '</small>',
                $acoes
            ];
            array_push($contents, $conteudo);
        }

        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $tabela = $this->montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }
}
