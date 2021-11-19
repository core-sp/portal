<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    protected $table = 'avisos';
    protected $guarded = [];
    protected $with = ['user'];

    const ATIVADO = 'Ativado';
    const DESATIVADO = 'Desativado';

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function isAtivado()
    {
        return $this->status == Aviso::ATIVADO ? true : false;
    }
}
