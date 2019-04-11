<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class Perfil extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'idperfil';
    protected $table = 'perfis';

    public function user()
    {
    	return $this->belongsToMany('App\User', 'perfil_usuarios', 'idperfil', 'idusuario');
    }
}
