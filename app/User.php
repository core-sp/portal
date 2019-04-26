<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Perfil;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $primaryKey = 'idusuario';
    protected $table = 'users';
    protected $with = ['regional'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function pagina()
    {
        return $this->hasMany('App\Pagina', 'idpagina');
    }

    public function noticia()
    {
        return $this->hasMany('App\Noticia', 'idnoticia');
    }

    public function licitacao()
    {
        return $this->hasMany('App\Licitacao', 'idlicitacao');
    }

    public function concurso()
    {
        return $this->hasMany('App\Concurso', 'idconcurso');
    }

    public function curso()
    {
        return $this->hasMany('App\Curso', 'idcurso');
    }

    public function bdoempresa()
    {
        return $this->hasMany('App\BdoEmpresa', 'idempresa');
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function chamado()
    {
        return $this->hasMany('App\Chamado', 'idchamado');
    }
}
