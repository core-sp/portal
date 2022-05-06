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
    protected $guarded = [];

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

    public function isAdmin()
    {
        return $this->idperfil == 1;
    }

    public function isEditor()
    {
        return $this->idperfil == 3;
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

    public function solicitaCedula()
    {
        return $this->hasMany('App\SolicitaCedula');
    }

    public function agendamentos()
    {
        return $this->hasMany('App\Agendamento', 'idusuario');
    }

    public function getRelatorioAgendadosPorPerfil($todos_agendados)
    {
        $subject = 'em '.$this->regional->regional;
        $agendados = $todos_agendados;
        $empty = $agendados->isEmpty();

        switch($this->idperfil)
        {
            case 1:
            case 6:
                $subject = 'em São Paulo e Seccionais';
                break;
            case 8:
                $agendados = $todos_agendados->where('idregional', $this->idregional)->count();
                $empty = $agendados == 0;
                break;
            case 12:
                $agendados = $todos_agendados->where('idregional', 1);
                $empty = $agendados->isEmpty();
                break;
            case 13:
                $agendados = $todos_agendados->where('idregional', '!=', 1);
                $empty = $agendados->isEmpty();
                $subject = 'nas Seccionais';
                break;
            case 21:
                $agendados = $todos_agendados->where('idregional', $this->idregional);
                $empty = $agendados->isEmpty();
                break;
        }

        return $empty ? null : [
            'agendados' => $agendados,
            'subject' => $subject
        ];
    }
}
