<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticia extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'idnoticia';

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario');
    }
}
