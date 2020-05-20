<?php

namespace App;

use App\Traits\ControleAcesso;
use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concurso extends Model
{
    use SoftDeletes, TabelaAdmin, ControleAcesso;

	protected $primaryKey = 'idconcurso';
    protected $table = 'concursos';
    protected $fillable = ['modalidade', 'titulo', 'nrprocesso', 'situacao',
    'datarealizacao', 'objeto', 'linkexterno', 'idusuario'];

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function variaveis()
    {
        return [
            'singular' => 'concurso',
            'singulariza' => 'o concurso',
            'plural' => 'concursos',
            'pluraliza' => 'concursos',
            'titulo_criar' => 'Cadastrar concurso',
            'btn_criar' => '<a href="'.route('concursos.create').'" class="btn btn-primary mr-1">Novo Concurso</a>',
            'btn_lixeira' => '<a href="'.route('concursos.lixeira').'" class="btn btn-warning">Concursos Deletados</a>',
            'btn_lista' => '<a href="'.route('concursos.index').'" class="btn btn-primary">Lista de Concursos</a>',
            'titulo' => 'Concursos Deletados',
        ];
    }

    protected function tabelaHeaders()
    {
        return ['Código', 'Modalidade', 'Nº do Processo', 'Situação', 'Data de Realização', 'Ações'];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            $acoes = '<a href="'.route('concursos.show', $row->idconcurso).'" class="btn btn-sm btn-default">Ver</a> ';
            if($this->mostra('ConcursoController', 'edit'))
                $acoes .= '<a href="'.route('concursos.edit', $row->idconcurso).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($this->mostra('ConcursoController', 'destroy')) {
                $acoes .= '<form method="POST" action="'.route('concursos.destroy', $row->idconcurso).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir o concurso?\')" />';
                $acoes .= '</form>';
            }
            return [
                $row->idconcurso,
                $row->modalidade,
                $row->nrprocesso,
                $row->situacao,
                formataData($row->datarealizacao),
                $acoes
            ];
        })->toArray();
    }

    public function tabelaCompleta($query)
    {
        return $this->montaTabela(
            $this->tabelaHeaders(), 
            $this->tabelaContents($query),
            [ 'table', 'table-hover' ]
        );
    }

    public function tabelaTrashed($query)
    {
        $headers = ['Código', 'Modalidade', 'Nº do Processo', 'Deletado em', 'Ações'];
        
        $contents = $query->map(function($row){
            $acoes = '<a href="'.route('concursos.restore', $row->idconcurso).'" class="btn btn-sm btn-primary">Restaurar</a>';
            return [
                $row->idconcurso,
                $row->modalidade,
                $row->nrprocesso,
                formataData($row->deleted_at),
                $acoes
            ];
        })->toArray();
        
        return $this->montaTabela(
            $headers, 
            $contents,
            [ 'table', 'table-hover' ]
        );
    }
}
