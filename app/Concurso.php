<?php

namespace App;

use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concurso extends Model
{
    use SoftDeletes, TabelaAdmin;

	protected $primaryKey = 'idconcurso';
    protected $table = 'concursos';
    protected $fillable = ['modalidade', 'titulo', 'nrprocesso', 'situacao',
    'datarealizacao', 'objeto', 'linkexterno', 'idusuario'];

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }
}
