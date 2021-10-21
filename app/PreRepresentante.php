<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\PreRepresentanteResetPasswordNotification;

class PreRepresentante extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $guard = 'pre_representante';
    protected $table = 'pre_representantes';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PreRepresentanteResetPasswordNotification($token));
    }
}
