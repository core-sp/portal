<?php

namespace App;

use App\Notifications\RepresentanteResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticable;

class Representante extends Authenticable
{
    use Notifiable, SoftDeletes;

    protected $guard = 'representante';

    protected $fillable = ['cpf_cnpj', 'registro_core', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new RepresentanteResetPasswordNotification($token));
    }
}
