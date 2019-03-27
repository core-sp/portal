<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BdoEmpresa extends Model
{
    use SoftDeletes;
	protected $primaryKey = 'idempresa';
    protected $table = 'bdo_empresas';

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }
}
