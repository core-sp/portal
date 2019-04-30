<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    protected $table = 'regionais';
    protected $primaryKey = 'idregional';
    public $timestamps = false;

    public function user()
    {
        return $this->hasMany('App\User', 'idusuario');
    }
}
