<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
    use SoftDeletes;
	protected $primaryKey = 'idcurso';
    protected $table = 'cursos';
    protected $with = ['regional', 'user'];

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function cursoinscrito()
    {
    	return $this->hasMany('App\CursoInscrito', 'idcursoinscrito');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }

    public function noticia()
    {
        return $this->hasMany('App\Noticia', 'idnoticia');
    }
}
