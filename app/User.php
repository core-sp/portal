<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPassword;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $primaryKey = 'idusuario';
    protected $table = 'users';
    protected $with = ['regional', 'perfil', 'sessao'];

    protected $fillable = [
        'nome', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

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

    public function perfil()
    {
        return $this->belongsTo('App\Perfil', 'idperfil');
    }

    public function sessao()
    {
        return $this->hasOne('App\Sessao', 'idusuario');
    }
}
