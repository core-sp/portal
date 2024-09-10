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

    private function validarUpdateAjax($campo)
    {
        // Não atualiza CNPJ de Contabil já criado
        if($campo == 'cnpj')
            return 'remover';

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
            $pr->relacionarContabil($valido->id);

        return $valido;
    }

    public function atualizarFinal($campo, $valor, $pr)
    {
        $valido = $this->validarUpdateAjax($campo);
        if(isset($valido) && ($valido == 'remover'))
            $pr->update(['contabil_id' => null]);
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

    public function possuiLoginAtivo()
    {
        return $this->possuiLogin() && ($this->ativo == 1);
    }

    public function arrayValidacaoInputs()
    {
        return collect(Arr::only($this->attributesToArray(), ['cnpj', 'nome', 'email', 'nome_contato', 'telefone']))->keyBy(function ($item, $key) {
            return $key . '_contabil';
        })->toArray();
    }
}
