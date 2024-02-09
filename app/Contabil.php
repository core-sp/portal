<?php

namespace App;

// use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\UserExternoResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class Contabil extends Authenticatable
{
    // use Notifiable;
    use SoftDeletes;

    protected $guard = 'contabil';
    protected $table = 'contabeis';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    protected static function criarFinal($campo, $valor, $pr)
    {
        $valido = $campo == 'cnpj' ? self::buscar($valor, $pr->getHistoricoCanEdit()) : null;
        if(isset($valido))
        {
            if($valido == 'notUpdate')
                $valido = ['update' => $pr->getNextUpdateHistorico()];
            else
                $pr->update(['contabil_id' => $valido->id, 'historico_contabil' => $pr->setHistorico()]);
        }

        return $valido;
    }

    public function atualizarFinal($campo, $valor, $pr)
    {
        $valido = $this->validarUpdateAjax($campo, $valor, $pr->getHistoricoCanEdit());
        if(isset($valido))
        {
            if($valido == 'notUpdate')
                $valido = ['update' => $pr->getNextUpdateHistorico()];
            else
                $valido == 'remover' ? $pr->update(['contabil_id' => null]) : 
                $pr->update(['contabil_id' => $valido->id, 'historico_contabil' => $pr->setHistorico()]);
        }
        else
        {
            $this->updateAjax($campo, $valor);
            $pr->touch();
        }

        return $valido;
    }

    public function sendPasswordResetNotification($token)
    {
        // $this->notify(new UserExternoResetPasswordNotification($token));
        Mail::to($this->email)->queue(new UserExternoResetPasswordNotification($token));
    }

    public static function camposPreRegistro()
    {
        return [
            'cnpj',
            'nome',
            'email',
            'nome_contato',
            'telefone'
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
            return 'remover';
        }

        return null;
    }

    public function updateAjax($campo, $valor)
    {
        if(!$this->possuiLogin())
            $campo != 'cnpj' ? $this->update([$campo => $valor]) : null;
    }

    public static function atualizar($arrayCampos)
    {
        if(isset($arrayCampos['cnpj']) && (strlen($arrayCampos['cnpj']) == 14))
        {
            $contabil = Contabil::buscar($arrayCampos['cnpj']);
            if(!$contabil->possuiLogin())
            {
                unset($arrayCampos['cnpj']);
                $contabil->update($arrayCampos);
            }
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
