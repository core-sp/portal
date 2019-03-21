<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Licitacao extends Model
{
	use SoftDeletes;
	protected $primaryKey = 'idlicitacao';
    protected $table = 'licitacoes';

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario');
    }
}
