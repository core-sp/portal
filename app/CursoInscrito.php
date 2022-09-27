<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CursoInscrito extends Model
{
    use SoftDeletes;
    
    protected $primaryKey = 'idcursoinscrito';
    protected $table = 'curso_inscritos';
    protected $fillable = ['cpf', 'nome', 'telefone', 'email',
    'registrocore', 'idcurso', 'presenca', 'idusuario'];
    protected $with = ['curso'];

    public function curso()
    {
    	return $this->belongsTo('App\Curso', 'idcurso');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'idcursoinscrito');
    }
}
