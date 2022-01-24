<?php

namespace App;

use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagina extends Model
{
    use SoftDeletes, TabelaAdmin;
    
    protected $primaryKey = 'idpagina';
    protected $fillable = ['titulo', 'subtitulo', 'slug', 'img',
    'conteudo', 'conteudoBusca', 'idusuario'];

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function variaveis() {
        return [
            'singular' => 'pagina',
            'singulariza' => 'a página',
            'plural' => 'paginas',
            'pluraliza' => 'páginas',
            'titulo_criar' => 'Criar página',
            'btn_criar' => '<a href="'.route('paginas.create').'" class="btn btn-primary mr-1">Nova Página</a>',
            'btn_lixeira' => '<a href="/admin/paginas/lixeira" class="btn btn-warning">Páginas Deletadas</a>',
            'btn_lista' => '<a href="'.route('paginas.index').'" class="btn btn-primary">Lista de Páginas</a>',
            'titulo' => 'Páginas Deletadas'
        ];
    }

    private function tabelaHeaders()
    {
        return [ 'Código', 'Título', 'Categoria', 'Última alteração', 'Ações' ];
    }

    private function tabelaContents($query)
    {
        return $query->map(function($row) {
            $acoes = '<a href="/'.$row->slug.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if(auth()->user()->can('updateOther', auth()->user()))
                $acoes .= '<a href="'.route('paginas.edit', $row->idpagina).'" class="btn btn-sm btn-primary">Editar</a> ';
            if(auth()->user()->can('delete', auth()->user())) {
                $acoes .= '<form method="POST" action="'.route('paginas.destroy', $row->idpagina).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a página?\')" />';
                $acoes .= '</form>';
            }
            if(isset($row->paginacategoria->nome))
                $categoria = $row->paginacategoria->nome;
            else
                $categoria = 'Sem Categoria';
            isset($row->user) ? $autor = $row->user->nome : $autor = 'Usuário Deletado';
            return [
                $row->idpagina,
                $row->titulo,
                $categoria,
                formataData($row->updated_at).'<br><small>Por: '. $autor .'</small>',
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
        $headers = ['Código', 'Título', 'Deletada em:', 'Ações'];
        $contents = $query->map(function($row){
            $acoes = '<a href="'.route('paginas.restore', $row->idpagina).'" class="btn btn-sm btn-primary">Restaurar</a>';
            return [
                $row->idpagina,
                $row->titulo,
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
