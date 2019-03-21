<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Perfil extends Model
{
    protected $primaryKey = 'idperfil';
    protected $table = 'perfis';

    public function user()
    {
    	return $this->belongsToMany('App\User', 'perfil_usuarios', 'idperfil', 'idusuario');
    }
}
