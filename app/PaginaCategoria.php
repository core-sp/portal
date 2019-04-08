<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaginaCategoria extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idpaginacategoria';
    protected $table = 'pagina_categorias';

    public function pagina()
    {
    	return $this->hasMany('App\Pagina', 'idpagina');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario');
    }
}
