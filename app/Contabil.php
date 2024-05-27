<?php

namespace App;

// use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\UserExternoResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;

class Contabil extends Authenticatable
{
    // use Notifiable;
    use SoftDeletes;

    protected $guard = 'contabil';
    protected $table = 'contabeis';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    private function validarUpdateAjax($campo, $valor, $canEdit = null)
    {
        if($campo == 'cnpj')
        {
            if(isset($valor) && (strlen($valor) == 14)) 
                return self::buscar($valor, $canEdit);
            return 'remover';
        }

        return null;
    }

    private function updateAjax($campo, $valor)
    {
        if(!$this->possuiLogin())
            $campo != 'cnpj' ? $this->update([$campo => $valor]) : null;
    }

    protected static function criarFinal($campo, $valor, $pr)
    {
        if($campo != 'cnpj')
            throw new \Exception('Não pode relacionar contábil sem CNPJ no pré-registro de ID ' . $pr->id . '.', 400);

        $valido = self::buscar($valor, $pr->getHistoricoCanEdit());

        if($valido == 'notUpdate')
            $valido = ['update' => $pr->getNextUpdateHistorico()];
        else
            $pr->update(['contabil_id' => $valido->id, 'historico_contabil' => $pr->setHistorico()]);

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

            $existe = self::where('cnpj', $cnpj)->first();

            return isset($existe) ? $existe->makeHidden(['verify_token']) : self::create(['cnpj' => $cnpj]);
        }

        throw new \Exception('Não pode buscar contábil sem CNPJ.', 400);
    }

    // public function finalArray($arrayCampos, $pr)
    // {
    //     $resultado = 'remover';

    //     if(isset($arrayCampos['cnpj']) && (strlen($arrayCampos['cnpj']) == 14))
    //     {
    //         $resultado = '';
    //         if(!$this->possuiLogin())
    //         {
    //             unset($arrayCampos['cnpj']);
    //             $this->update($arrayCampos);
    //         }
    //     }

    //     $resultado = $pr->update(['contabil_id' => $resultado === 'remover' ? null : $this->id]);
    //     $pr->touch();

    //     return $resultado;
    // }

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

    public function arrayValidacaoInputs()
    {
        return collect(Arr::only($this->attributesToArray(), ['cnpj', 'nome', 'email', 'nome_contato', 'telefone']))->keyBy(function ($item, $key) {
            return $key . '_contabil';
        })->toArray();
    }
}
