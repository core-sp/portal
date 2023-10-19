<?php

namespace App\Services;

use App\Curso;
use App\Events\CrudEvent;
use App\Contracts\CursoServiceInterface;

class CursoService implements CursoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'curso',
            'singulariza' => 'o curso',
            'plural' => 'cursos',
            'pluraliza' => 'cursos',
            'titulo_criar' => 'Cadastrar curso',
            'btn_criar' => '<a href="'.route('cursos.create').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Curso</a>',
            'btn_lixeira' => '<a href="'.route('cursos.lixeira').'" class="btn btn-warning"><i class="fas fa-trash"></i> Cursos Cancelados</a>',
            'btn_lista' => '<a href="'.route('cursos.index').'" class="btn btn-primary mr-1"><i class="fas fa-list"></i> Lista de Cursos</a>',
            'titulo' => 'Cursos cancelados',
        ];
    }

    private function tabelaCompleta($resultados, $user)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Turma',
            'Tipo / Tema',
            'Onde / Quando',
            'Vagas',
            'Regional',
            'Acesso',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userInscritoView = perfisPermitidos('CursoInscritoController', 'index');
        $userPodeEdit = $user->can('updateOther', $user);
        $userPodeDestroy = $user->can('delete', $user);
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('cursos.show', $resultado->idcurso).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($userInscritoView)
                $acoes .= '<a href="'.route('inscritos.index', $resultado->idcurso).'" class="btn btn-sm btn-secondary">Inscritos</a> ';
            if($userPodeEdit)
                $acoes .= '<a href="'.route('cursos.edit', $resultado->idcurso).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeDestroy) {
                $acoes .= '<form method="POST" action="'.route('cursos.destroy', $resultado->idcurso).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar" onclick="return confirm(\'Tem certeza que deseja cancelar o curso?\')" />';
                $acoes .= '</form>';
            }
            $publicado = $resultado->publicado == 'Sim' ? 'Publicado' : 'Rascunho';
            $endereco = isset($resultado->endereco) ? $resultado->endereco : 'Evento online';
            $conteudo = [
                $resultado->idcurso,
                $resultado->tipo.'<br>'.$resultado->tema.'<br /><small><em>'.$publicado.'</em></small>',
                $endereco.'<br />'.formataData($resultado->datarealizacao),
                $resultado->cursoinscrito_count.' / '.$resultado->nrvagas,
                $resultado->regional->regional,
                $resultado->acesso,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $tabela = montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    private function tabelaCompletaLixeira($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Turma', 
            'Tipo / Tema', 
            'Onde / Quando', 
            'Regional',
            'Cancelado em:',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('cursos.restore', $resultado->idcurso).'" class="btn btn-sm btn-primary">Restaurar</a> ';
            $endereco = isset($resultado->endereco) ? $resultado->endereco : 'Evento online';
            $conteudo = [
                $resultado->idcurso,
                $resultado->tipo.'<br>'.$resultado->tema,
                $endereco.'<br />'.formataData($resultado->datarealizacao),
                $resultado->regional->regional,
                formataData($resultado->deleted_at),
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $tabela = montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function tipos()
    {
        return Curso::tipos();
    }

    public function acessos()
    {
        return Curso::acessos();
    }

    public function listar($user)
    {
        $resultados = Curso::withCount('cursoinscrito')
        ->with('regional')
        ->orderBy('idcurso','DESC')
        ->paginate(10);

        $variaveis = $this->variaveis;
        if($user->cannot('create', $user))
            unset($variaveis['btn_criar']);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $variaveis
        ];
    }

    public function view($id = null)
    {
        $resultado = isset($id) ? Curso::findOrFail($id) : null;

        return [
            'resultado' => $resultado,
            'variaveis' => (object) $this->variaveis,
            'tipos' => Curso::tipos(),
            'acessos' => Curso::acessos(),
        ];
    }

    public function save($validated, $user, $id = null)
    {
        $validated['idusuario'] = $user->idusuario;
        $acao = (!isset($id)) ? 'criou' : 'editou';

        if(!isset($id))
            $id = Curso::create($validated);
        else
            Curso::findOrFail($id)->update($validated);
        
        event(new CrudEvent('curso', $acao, $id));
    }

    public function destroy($id)
    {
        Curso::findOrFail($id)->delete() ? event(new CrudEvent('curso', 'cancelou', $id)) : null;
    }

    public function lixeira()
    {
        $resultados = Curso::with('regional')->onlyTrashed()->paginate(10);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompletaLixeira($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function restore($id)
    {
        Curso::onlyTrashed()->findOrFail($id)->restore() ? event(new CrudEvent('curso', 'restaurou', $id)) : null;
    }

    public function buscar($busca, $user)
    {
        $resultados = Curso::with('regional')
        ->where('tipo','LIKE','%'.$busca.'%')
        ->orWhere('tema','LIKE','%'.$busca.'%')
        ->orWhere('descricao','LIKE','%'.$busca.'%')
        ->paginate(10);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function show($id)
    {
        return Curso::findOrFail($id);
    }

    public function siteGrid()
    {
        return Curso::select('idcurso','img','idregional','tipo','tema','resumo', 'datarealizacao')
            ->where('inicio_inscricao','<=', now()->format('Y-m-d H:i'))
            ->where('termino_inscricao','>=', now()->format('Y-m-d H:i'))
            ->where('publicado','Sim')
            ->paginate(9);
    }

    public function inscritos(Curso $curso = null)
    {
        return resolve('App\Contracts\CursoSubServiceInterface');
    }
}