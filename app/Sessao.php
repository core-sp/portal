<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sessao extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idsessao';
    protected $table = 'sessoes';

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }
}
