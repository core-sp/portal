<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\UserExternoResetPasswordNotification;

class UserExterno extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $guard = 'user_externo';
    protected $table = 'users_externo';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserExternoResetPasswordNotification($token));
    }

    // RU = registro do login do usuário
    public static function codigosPreRegistro()
    {
        return [
            'RU01' => 'cpf_cnpj',
            'RU02' => 'nome',
            'RU03' => 'email'
        ];
    }

    public function preRegistro()
    {
        return $this->hasOne('App\PreRegistro')->withTrashed();
    }
}
