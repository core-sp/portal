<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\UserExternoResetPasswordNotification;
use Carbon\Carbon;

class Contabil extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $guard = 'contabil';
    protected $table = 'contabeis';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserExternoResetPasswordNotification($token));
    }

    public static function camposPreRegistro()
    {
        return [
            'c1' => 'cnpj',
            'c2' => 'nome',
            'c3' => 'email',
            'c4' => 'nome_contato',
            'c5' => 'telefone'
        ];
    }

    public function preRegistros()
    {
        return $this->hasMany('App\PreRegistro')->withTrashed();
    }

    public static function buscar($cnpj, $canEdit = null)
    {
        if(isset($cnpj) && (strlen($cnpj) == 14))
        {
            if(isset($canEdit) && !$canEdit)
                return 'notUpdate';

            $existe = Contabil::where('cnpj', $cnpj)->first();

            return isset($existe) ? $existe->makeHidden(['verify_token']) : Contabil::create(['cnpj' => $cnpj]);
        }

        return null;
    }

    public function validarUpdateAjax($campo, $valor, $canEdit = null)
    {
        if($campo == 'cnpj')
        {
            if(isset($valor) && (strlen($valor) == 14)) 
                return Contabil::buscar($valor, $canEdit);
            if(!isset($valor))
                return 'remover';
        }

        return null;
    }

    public function updateAjax($campo, $valor)
    {
        if(!$this->possuiLogin())
            $this->update([$campo => $valor]);
    }

    public static function atualizar($arrayCampos)
    {
        if(isset($arrayCampos['cnpj']) && (strlen($arrayCampos['cnpj']) == 14))
        {
            $contabil = Contabil::buscar($arrayCampos['cnpj']);
            if(!$contabil->possuiLogin())
                $contabil->update($arrayCampos);
            return $contabil;
        }

        return 'remover';
    }

    public function podeAtivar()
    {
        $update = Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at);
        $update->addDay();

        if(!$this->possuiLogin())
            return false;
        if($update >= now())
        {
            if($this->trashed())
                $this->restore();
            return true;
        }
        return false;
    }

    public function possuiLogin()
    {
        return isset($this->aceite) && isset($this->ativo);
    }
}
