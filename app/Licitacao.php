<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Licitacao extends Model
{
	use SoftDeletes;

    protected $primaryKey = 'idlicitacao';
    protected $table = 'licitacoes';
    protected $fillable = ['modalidade', 'situacao', 'uasg', 'titulo', 'edital',
    'nrlicitacao', 'nrprocesso', 'datarealizacao', 'objeto', 'idusuario'];

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario');
    }
}
