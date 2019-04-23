<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chamado extends Model
{
    use SoftDeletes;
	protected $primaryKey = 'idchamado';
    protected $table = 'chamados';

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }
}
