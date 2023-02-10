<?php

namespace App\Services;

use App\Licitacao;
use App\Contracts\LicitacaoServiceInterface;
use App\Events\CrudEvent;

class LicitacaoService implements LicitacaoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'licitacao',
            'singulariza' => 'a licitação',
            'plural' => 'licitacoes',
            'pluraliza' => 'licitações',
            'titulo_criar' => 'Cadastrar licitação',
            'btn_criar' => '<a href="'.route('licitacoes.create').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Nova Licitação</a>',
            'btn_lixeira' => '<a href="'.route('licitacoes.trashed').'" class="btn btn-warning"><i class="fas fa-trash"></i> Licitações Deletadas</a>',
            'btn_lista' => '<a href="'.route('licitacoes.index').'" class="btn btn-primary mr-1"><i class="fas fa-list"></i> Lista de Licitações</a>',
            'titulo' => 'Licitações Deletadas'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Modalidade',
            'Nº da Licitação',
            'Nº do Processo',
            'Situação',
            'Data de Realização',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        $userPodeExcluir = auth()->user()->can('delete', auth()->user());
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('licitacoes.show', $resultado->idlicitacao).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($userPodeEditar)
                $acoes .= '<a href="'.route('licitacoes.edit', $resultado->idlicitacao).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeExcluir)
            {
                $acoes .= '<form method="POST" action="'.route('licitacoes.destroy', $resultado->idlicitacao).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a licitação?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->idlicitacao,
                $resultado->modalidade,
                $resultado->nrlicitacao,
                $resultado->nrprocesso,
                $resultado->situacao,
                formataData($resultado->datarealizacao),
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
            'Código', 
            'Modalidade', 
            'Nº da Licitação', 
            'Deletada em:', 
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('licitacoes.restore', $resultado->idlicitacao).'" class="btn btn-sm btn-primary">Restaurar</a>';
            $conteudo = [
                $resultado->idlicitacao,
                $resultado->modalidade,
                $resultado->nrlicitacao,
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

    private function buscaSite($request = null)
    {
        $inicio = Licitacao::select('nrprocesso', 'nrlicitacao', 'modalidade', 'situacao', 'titulo', 'edital', 'datarealizacao', 'objeto', 'uasg', 'idlicitacao', 'created_at', 'updated_at')
        ->selectRaw("CAST(SUBSTR(nrprocesso, INSTR(nrprocesso, '/') + 1) AS DECIMAL) as anoProcesso")
        ->selectRaw("CAST(SUBSTR(nrprocesso, 1, INSTR(nrprocesso, '/') - 1) AS DECIMAL) as numeroProcesso")
        ->selectRaw("CAST(SUBSTR(nrlicitacao, INSTR(nrlicitacao, '/') + 1) AS DECIMAL) as anoLicitacao")
        ->selectRaw("CAST(SUBSTR(nrlicitacao, 1, INSTR(nrlicitacao, '/') - 1) AS DECIMAL) as numeroLicitacao");

        if(isset($request))
        {
            $palavrachave = isset($request['palavrachave']) ? $request['palavrachave'] : null;
            $modalidade = isset($request['modalidade']) ? $request['modalidade'] : null;
            $situacao = isset($request['situacao']) ? $request['situacao'] : null;
            $nrlicitacao = isset($request['nrlicitacao']) ? $request['nrlicitacao'] : null;
            $nrprocesso = isset($request['nrprocesso']) ? $request['nrprocesso'] : null;
            $datarealizacao = isset($request['datarealizacao']) ? $request['datarealizacao'] : null;

            return $inicio->when(isset($palavrachave), function($query) use($palavrachave){
                $query->where('objeto', 'LIKE', '%' . htmlentities($palavrachave, ENT_NOQUOTES, 'UTF-8') . '%')
                ->orWhere('titulo', 'LIKE', '%'.$palavrachave.'%');
            })->when(isset($modalidade), function($query) use($modalidade){
                $query->where('modalidade', $modalidade);
            })->when(isset($situacao), function($query) use($situacao){
                $query->where('situacao', $situacao);
            })->when(isset($nrlicitacao), function($query) use($nrlicitacao){
                $query->where('nrlicitacao', 'LIKE', '%'.$nrlicitacao.'%');
            })->when(isset($nrprocesso), function($query) use($nrprocesso){
                $query->where('nrprocesso', 'LIKE', '%'.$nrprocesso.'%');
            })->when(isset($datarealizacao), function($query) use($datarealizacao){
                $query->whereDate('datarealizacao', $datarealizacao);
            })->orderBy('anoProcesso','DESC')
            ->orderBy('numeroProcesso','DESC')
            ->orderBy('anoLicitacao','DESC')
            ->orderBy('numeroLicitacao','DESC')
            ->paginate(10);
        }

        return $inicio->orderBy('anoProcesso','DESC')
            ->orderBy('numeroProcesso','DESC')
            ->orderBy('anoLicitacao','DESC')
            ->orderBy('numeroLicitacao','DESC')
            ->paginate(10);
    }

    public function getModalidades()
    {
        return Licitacao::modalidadesLicitacao();
    }

    public function getSituacoes()
    {
        return Licitacao::situacoesLicitacao();
    }

    public function listar()
    {
        $resultados = Licitacao::orderBy('idlicitacao', 'DESC')->paginate(10);

        if(auth()->user()->cannot('create', auth()->user()))
            unset($this->variaveis['btn_criar']);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id = null)
    {
        if(isset($id))
            return [
                'resultado' => Licitacao::findOrFail($id),
                'modalidades' => $this->getModalidades(),
                'situacoes' => $this->getSituacoes(),
                'variaveis' => (object) $this->variaveis
            ];

        return [
            'modalidades' => $this->getModalidades(),
            'situacoes' => $this->getSituacoes(),
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($request, $user, $id = null)
    {
        $request['idusuario'] = $user->idusuario;
        $txt = isset($id) ? 'editou' : 'criou';

        if(isset($id))
            Licitacao::findOrFail($id)->update($request);
        else  
            $id = Licitacao::create($request)->idlicitacao;
            
        event(new CrudEvent('licitação', $txt, $id));
    }

    public function destroy($id)
    {
        $apagado = Licitacao::findOrFail($id)->delete();
        if($apagado)
            event(new CrudEvent('licitação', 'apagou', $id));
    }

    public function lixeira()
    {
        $resultados = Licitacao::onlyTrashed()->paginate(10);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompletaLixeira($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function restore($id)
    {
        $restaurado = Licitacao::onlyTrashed()->findOrFail($id)->restore();
        if($restaurado)
            event(new CrudEvent('licitação', 'restaurou', $id));
    }

    public function buscar($busca)
    {
        $resultados = Licitacao::where('modalidade','LIKE','%'.$busca.'%')
            ->orWhere('nrlicitacao','LIKE','%'.$busca.'%')
            ->orWhere('nrprocesso','LIKE','%'.$busca.'%')
            ->orWhere('situacao','LIKE','%'.$busca.'%')
            ->orWhere('objeto','LIKE','%' . htmlentities($busca, ENT_NOQUOTES, 'UTF-8') . '%')
            ->orWhere('idlicitacao', $busca)
            ->paginate(10);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function siteGrid($request = null)
    {
        $licitacoes = $this->buscaSite($request);

        return [
            'licitacoes' => $licitacoes,
            'modalidades' => $this->getModalidades(),
            'situacoes' => $this->getSituacoes(),
        ];
    }

    public function viewSite($id)
    {
        return Licitacao::findOrFail($id);
    }
}