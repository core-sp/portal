<?php

namespace App;

use App\Mail\RepresentanteResetPasswordMail;
use App\Notifications\RepresentanteResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Support\Facades\Mail;
use App\Connections\FirebirdConnection;
use App\Traits\GerentiProcedures;

class Representante extends Authenticable
{
    use Notifiable, SoftDeletes, GerentiProcedures;

    protected $guard = 'representante';

    protected $fillable = ['cpf_cnpj', 'registro_core', 'ass_id', 'nome', 'email', 'password', 'verify_token', 'ativo'];

    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $body = emailResetRepresentante($token);

        // $this->notify(new RepresentanteResetPasswordNotification($token)); - FALLBACK
        Mail::to($this->email)->send(new RepresentanteResetPasswordMail($token, $body));
    }

    public function enderecos()
    {
        return $this->gerentiEnderecos($this->ass_id);
    }

    public function contatos()
    {
        return $this->gerentiContatos($this->ass_id);
    }

    public function tipoPessoa()
    {
        return strlen($this->cpf_cnpj) === 14 ? 'PF' : 'PJ';
    }

    public function dadosGerais()
    {
        return $this->tipoPessoa() === 'PF' ? $this->gerentiDadosGeraisPF($this->ass_id) : $this->gerentiDadosGeraisPJ($this->ass_id);
    }

    public function getCpfCnpjAttribute($value)
    {
        if(strlen($value) === 11) {
            return substr($value, 0, 3) . '.' . substr($value, 3, 3) . '.' . substr($value, 6, 3) . '-' . substr($value, 9, 2);
        } elseif(strlen($value) === 14) {
            return substr($value, 0, 2) . '.' . substr($value, 2, 3) . '.' . substr($value, 5, 3) . '/' . substr($value, 8, 4) . '-' . substr($value, 12, 2);
        } else {
            return 'Indefinido';
        }
    }

    public function getRegistroCoreAttribute($value)
    {
        return substr_replace($value, '/', -4, 0);
    }

    public function cobrancas()
    {
        $values = $this->gerentiBolestosLista($this->ass_id);
        
        $extrato = [];
        $parcelamento = [];

        foreach($values as $value) {
            if($value['TPGERBOL_ID'] === 1) {
                array_push($extrato, $value);
            } else {
                array_push($parcelamento, $value);
            }
        }
        
        $result = [
            'extrato' => $extrato,
            'parcelamento' => $parcelamento
        ];

        return $result;
    }
}
