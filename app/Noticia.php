<?php

namespace App;

use App\Traits\ControleAcesso;
use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticia extends Model
{
    use SoftDeletes, TabelaAdmin, ControleAcesso;

    protected $primaryKey = 'idnoticia';
    protected $fillable = ['titulo', 'slug', 'img', 'conteudo', 'conteudoBusca', 'categoria',
    'publicada', 'idregional', 'idcurso', 'idusuario'];
    
    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function curso()
    {
        return $this->belongsTo('App\Curso', 'idcurso');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function variaveis()
    {
        return [
            'singular' => 'noticia',
            'singulariza' => 'a notícia',
            'plural' => 'noticias',
            'pluraliza' => 'notícias',
            'titulo_criar' => 'Publicar notícia',
            'btn_criar' => '<a href="'.route("noticias.create").'" class="btn btn-primary mr-1">Nova Notícia</a>',
            'btn_lixeira' => '<a href="/admin/noticias/lixeira" class="btn btn-warning">Notícias Deletadas</a>',
            'btn_lista' => '<a href="'.route("noticias.index").'" class="btn btn-primary">Lista de Notícias</a>',
            'titulo' => 'Notícias Deletadas'
        ];
    }

    protected function tabelaHeaders()
    {
        return ['Código', 'Título', 'Regional', 'Última alteração', 'Ações' ];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            $acoes = '<a href="/noticia/'.$row->slug.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($this->mostra('NoticiaController', 'edit'))
                $acoes .= '<a href="'.route('noticias.edit', $row->idnoticia).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($this->mostra('NoticiaController', 'destroy')) {
                $acoes .= '<form method="POST" action="'.route('noticias.destroy', $row->idnoticia).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a notícia?\')" />';
                $acoes .= '</form>';
            }
            if(isset($row->idregional))
                $regional = $row->regional->regional;
            else
                $regional = "Todas";
            if($row->publicada == 'Sim')
                $publicada = 'Publicada';
            else
                $publicada = 'Rascunho';
            isset($row->user) ? $autor = $row->user->nome : $autor = 'Usuário Deletado';
            return [
                $row->idnoticia,
                $row->titulo.'<br><small><em>'.$publicada.'</em></small>',
                $regional,
                formataData($row->updated_at).'<br><small>Por: '.$autor.'</small>',
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
        $headers = [ 'Código', 'Título', 'Deletada em:', 'Ações' ];
        $contents = $query->map(function($row){
            $acoes = '<a href="'.route('noticias.restore', $row->idnoticia).'" class="btn btn-sm btn-primary">Restaurar</a>';
            return [
                $row->idnoticia,
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
