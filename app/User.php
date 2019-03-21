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

    public function perfil()
    {
        return $this->belongsToMany('App\Perfil', 'perfil_usuarios', 'idusuario', 'idperfil');
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

    public function autorizarPerfis($perfis)
    {
      if (is_array($perfis)) {
        return $this->hasAnyRole($perfis) || 
          abort(401, 'Ação não autorizada.');
      }
      return $this->hasRole($perfis) || 
        abort(401, 'Ação não autorizada.');
    }

    public function hasAnyRole($perfis)
    {
        return null !== $this->perfil()->whereIn('nome', $perfis)->first();
    }

    public function hasRole($perfil)
    {
        return null !== $this->perfil()->where('nome', $perfil)->first();
    }
}
