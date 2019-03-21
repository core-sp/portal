<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagina extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'idpagina';

    public function paginacategoria()
    {
    	return $this->belongsTo('App\PaginaCategoria', 'idcategoria');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario');
    }
}
