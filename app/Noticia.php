<?php

namespace App;

use App\Http\Controllers\ControleController;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\Helper;
use App\Repositories\NoticiaRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticia extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idnoticia';
    protected $fillable = ['titulo', 'slug', 'img', 'conteudo', 'categoria',
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

    protected function tabelaHeaders()
    {
        return ['Código', 'Título', 'Regional', 'Última alteração', 'Ações' ];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            $acoes = '<a href="/noticia/'.$row->slug.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if(ControleController::mostra('NoticiaController', 'edit'))
                $acoes .= '<a href="/admin/noticias/editar/'.$row->idnoticia.'" class="btn btn-sm btn-primary">Editar</a> ';
            if(ControleController::mostra('NoticiaController', 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/noticias/apagar/'.$row->idnoticia.'" class="d-inline">';
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
                Helper::formataData($row->updated_at).'<br><small>Por: '.$autor.'</small>',
                $acoes
            ];
        })->toArray();
    }

    public function tabelaCompleta($query)
    {
        return CrudController::montaTabela(
            $this->tabelaHeaders(), 
            $this->tabelaContents($query),
            [ 'table', 'table-hover' ]
        );
    }

    public function tabelaTrashed($query)
    {
        $headers = [ 'Código', 'Título', 'Deletada em:', 'Ações' ];
        $contents = $query->map(function($row){
            $acoes = '<a href="/admin/noticias/restore/'.$row->idnoticia.'" class="btn btn-sm btn-primary">Restaurar</a>';
            return [
                $row->idnoticia,
                $row->titulo,
                Helper::formataData($row->deleted_at),
                $acoes
            ];
        });

        return CrudController::montaTabela(
            $headers, 
            $contents,
            [ 'table', 'table-hover' ]
        );
    }
}
