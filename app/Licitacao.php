<?php

namespace App;

use App\Traits\ControleAcesso;
use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Licitacao extends Model
{
	use SoftDeletes, TabelaAdmin, ControleAcesso;

    protected $primaryKey = 'idlicitacao';
    protected $table = 'licitacoes';
    protected $fillable = ['modalidade', 'situacao', 'uasg', 'titulo', 'edital',
    'nrlicitacao', 'nrprocesso', 'datarealizacao', 'objeto', 'idusuario'];

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function variaveis()
    {
        return [
            'singular' => 'licitacao',
            'singulariza' => 'a licitação',
            'plural' => 'licitacoes',
            'pluraliza' => 'licitações',
            'titulo_criar' => 'Cadastrar licitação',
            'btn_criar' => '<a href="'.route('licitacoes.create').'" class="btn btn-primary mr-1">Nova Licitação</a>',
            'btn_lixeira' => '<a href="'.route('licitacoes.trashed').'" class="btn btn-warning">Licitações Deletadas</a>',
            'btn_lista' => '<a href="'.route('licitacoes.index').'" class="btn btn-primary mr-1">Lista de Licitações</a>',
            'titulo' => 'Licitações Deletadas',
        ];
    }

    protected function tabelaHeaders()
    {
        return [
            'Código',
            'Modalidade',
            'Nº da Licitação',
            'Nº do Processo',
            'Situação',
            'Data de Realização',
            'Ações'
        ];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            $acoes = '<a href="/licitacao/'.$row->idlicitacao.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($this->mostra('LicitacaoController', 'edit'))
                $acoes .= '<a href="'.route('licitacoes.edit', $row->idlicitacao).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($this->mostra('LicitacaoController', 'destroy')) {
                $acoes .= '<form method="POST" action="'.route('licitacoes.destroy', $row->idlicitacao).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a licitação?\')" />';
                $acoes .= '</form>';
            }
            return [
                $row->idlicitacao,
                $row->modalidade,
                $row->nrlicitacao,
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
        $headers = ['Código', 'Modalidade', 'Nº da Licitação', 'Deletada em:', 'Ações'];
        $contents = $query->map(function($row){
            $acoes = '<a href="'.route('licitacoes.restore', $row->idlicitacao).'" class="btn btn-sm btn-primary">Restaurar</a>';
            return [
                $row->idlicitacao,
                $row->modalidade,
                $row->nrlicitacao,
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
