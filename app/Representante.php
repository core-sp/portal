<?php

namespace App;

use App\Mail\RepresentanteResetPasswordMail;
use App\Notifications\RepresentanteResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Support\Facades\Mail;

class Representante extends Authenticable
{
    use Notifiable, SoftDeletes;

    protected $guard = 'representante';

    protected $fillable = ['cpf_cnpj', 'registro_core', 'nome', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $body = 'Você está recebendo este email pois solicitou alteração de senha no Portal Core-SP.';
        $body .= '<br>';
        $body .= 'Clique no link abaixo para continuar o procedimento.';
        $body .= '<br><br>';
        $body .= '<a href="'. route('representante.password.reset', $token) .'">Alterar senha</a>';
        $body .= '<br><br>';
        $body .= 'Caso não tenha solicitado, favor desconsiderar este email.';
        $body .= '<br><br>';
        $body .= 'Atenciosamente,';
        $body .= '<br>';
        $body .= 'Portal Core-SP';

        // $this->notify(new RepresentanteResetPasswordNotification($token));

        Mail::to($this->email)->send(new RepresentanteResetPasswordMail($token, $body));
    }
}
