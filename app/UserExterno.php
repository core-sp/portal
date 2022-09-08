<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\UserExternoResetPasswordNotification;
use Carbon\Carbon;

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

    public function isPessoaFisica()
    {
        return strlen($this->cpf_cnpj) == 11;
    }

    public function preRegistro()
    {
        return $this->hasOne('App\PreRegistro')->withTrashed();
    }

    public function podeAtivar()
    {
        $update = Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at);
        $update->addDay();
        if($update >= now())
        {
            if($this->trashed())
                $this->restore();
            return true;
        }
        return false;
    }
}
