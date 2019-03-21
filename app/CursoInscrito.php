<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CursoInscrito extends Model
{
    protected $primaryKey = 'idcursoinscrito';
    protected $table = 'curso_inscritos';

    public function curso()
    {
    	return $this->belongsTo('App\Curso', 'idcurso');
    }
}
