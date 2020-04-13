<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagina extends Model
{
    use SoftDeletes;
    
    protected $primaryKey = 'idpagina';
    protected $fillable = ['titulo', 'subtitulo', 'slug', 'img',
    'conteudo', 'idpaginacategoria', 'idusuario'];

    public function paginacategoria()
    {
    	return $this->belongsTo('App\PaginaCategoria', 'idpaginacategoria');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }
}
