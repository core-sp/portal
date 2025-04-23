<?php

namespace App\Services;

use App\Curso;
use App\Events\CrudEvent;
use App\Contracts\CursoServiceInterface;
use Carbon\Carbon;

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
            '<span class="text-nowrap">Campo adicional?</span>',
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
                $acoes .= '<form method="POST" action="'.route('cursos.destroy', $resultado->idcurso).'" class="d-inline acaoTabelaAdmin">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="hidden" class="cor-danger txtTabelaAdmin" value="Tem certeza que deseja cancelar o curso <i>' . $resultado->tema . '</i>?" />';
                $acoes .= '<button type="button" class="btn btn-sm btn-danger" value="' . $resultado->idcurso . '">Cancelar</button>';
                $acoes .= '</form>';
            }
            $publicado = $resultado->publicado() ? 'Publicado' : 'Rascunho';
            $endereco = isset($resultado->endereco) ? $resultado->endereco : 'Evento online';
            $required = $resultado->campo_required ? 'Obrigatório' : 'Opcional';
            $conteudo = [
                $resultado->idcurso,
                $resultado->tipo.'<br>'.$resultado->tema.'<br /><small><em>'.$publicado.'</em></small>',
                $endereco.'<br />'.formataData($resultado->datarealizacao),
                $resultado->cursoinscrito_count.' / '.$resultado->nrvagas,
                $resultado->regional->regional,
                $resultado->acesso,
                $resultado->add_campo ? 'Sim<br /><small><em><span class="text-nowrap">'.$resultado->nomeRotulo().'</span><br />'.$required.'</em></small>' : 'Não',
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

    public function rotulos()
    {
        return Curso::rotulos();
    }

    public function getRegrasCampoAdicional($id)
    {
        return Curso::findOrFail($id)->getRegras();
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
            'rotulos' => Curso::rotulos(),
        ];
    }

    public function save($validated, $user, $id = null)
    {
        $validated['idusuario'] = $user->idusuario;
        $acao = !isset($id) ? 'criou' : 'editou';

        if(!Carbon::hasFormat($validated['inicio_inscricao'], 'Y-m-d H:i'))
            $validated['inicio_inscricao'] = null;

        if(!Carbon::hasFormat($validated['termino_inscricao'], 'Y-m-d H:i'))
            $validated['termino_inscricao'] = null;

        if(!isset($id))
            $id = Curso::create($validated)->idcurso;
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
        Curso::onlyTrashed()->findOrFail($id)->restore() ? event(new CrudEvent('curso', 'reabriu', $id)) : null;
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

    public function downloadInscricoes($id)
    {
        $resultado = Curso::findOrFail($id)->cursoinscrito()
        ->selectRaw('email AS "E-mail", cpf AS "CPF", nome AS "Nome", telefone AS "Telefone", registrocore AS "Registro Core", tipo_inscrito AS "Tipo da Inscrição", campo_adicional AS "Campo Adicional", presenca AS "Compareceu", created_at AS "Data da Inscrição"')
        ->orderBy('created_at', 'desc')
        ->get();
        
        $lista = $resultado->toArray();
        array_unshift($lista, array_keys($lista[0]));
        $callback = function() use($lista) {
            $fh = fopen('php://output','w');
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach($lista as $key => $linha) {
                // if($key != 'curso')
                    fputcsv($fh,$linha,';');
            }
            fclose($fh);
        };

        return $callback;
    }

    public function show($id, $publicado = false)
    {
        return $publicado ? Curso::where('idcurso', $id)->where('publicado', 'Sim')->firstOrFail() : Curso::findOrFail($id);
    }

    public function siteGrid($areaRep = false)
    {
        $now = now()->format('Y-m-d H:i');

        if($areaRep)
            return Curso::where('datatermino','>=', $now)
            ->where('publicado','Sim')
            ->where('acesso', Curso::ACESSO_PRI)
            ->paginate(6);
            
        return Curso::where('datatermino','>=', $now)
            ->where('publicado','Sim')
            ->paginate(9);
    }

    public function cursosAnteriores()
    {
        return Curso::where('datatermino', '<', now()->format('Y-m-d H:i'))
        ->where('publicado', 'Sim')
        ->orderBy('created_at', 'DESC')
        ->paginate(9);
    }

    public function inscritos(Curso $curso = null)
    {
        return resolve('App\Contracts\CursoSubServiceInterface');
    }
}