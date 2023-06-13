<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notifiable;
use App\Mail\RepresentanteResetPasswordMail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticable;
use App\Notifications\RepresentanteResetPasswordNotification;
use Carbon\Carbon;

class Representante extends Authenticable
{
    use Notifiable, SoftDeletes;

    private $gerentiRepository;

    protected $guard = 'representante';
    protected $fillable = ['cpf_cnpj', 'registro_core', 'ass_id', 'nome', 'email', 'password', 'verify_token', 'aceite', 'ativo'];
    protected $hidden = ['password', 'remember_token'];

    // Tipos de pessoa.
    const PESSOA_FISICA = "PF";
    const PESSOA_JURIDICA = "PJ";
    const RESP_TECNICO = "RT";

    // Tipos de empresa.
    const EMPRESA_INDIVIDUAL = "Empresa Individual";

    // Situações do Representante Comercial.
    const EM_DIA = "Situação: Em dia.";
    const PARCELAMENTO_EM_ABERTO = "Situação: Parcelamento em aberto.";
    const EXECUÇÃO_FISCAL = 'Situação: Execução Fiscal.';
    const CANCELADO_BLOQUEADO = 'Situação: Cancelado ou Bloqueado';

    // Status do Representante Comercial.
    const ATIVO = "Ativo";

    // Situações do pagamento de cobranças.
    const PAGO = "Pago";
    const EM_ABERTO = "Em aberto";

    /**
     * Mapeia código retornado pelo GERENTI na busca por Representante Comercial
     */
    public static function mapaCodigoTipoPessoa($codigo)
    {
        switch ($codigo) {
            case '1':
                return Representante::PESSOA_JURIDICA;
            break;

            case '2':
                return Representante::PESSOA_FISICA;
            break;

            case '5':
                return Representante::RESP_TECNICO;
            break;

            default:
                return 'Indefinida';
            break;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $body = emailResetRepresentante($token);

        // $this->notify(new RepresentanteResetPasswordNotification($token)); - FALLBACK
        Mail::to($this->email)->send(new RepresentanteResetPasswordMail($token, $body));
    }

    public function tipoPessoa()
    {
        return strlen($this->cpf_cnpj) === 14 ? Representante::PESSOA_FISICA : Representante::PESSOA_JURIDICA;
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

    public function solicitacoesEnderecos()
    {
        return RepresentanteEndereco::where('ass_id', '=', $this->ass_id)->where('status', '!=', 'Enviado')->orderBy('created_at', 'DESC')->get();
    }

    public function cedulas()
    {
        return $this->hasMany('App\SolicitaCedula', 'idrepresentante');
    }

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'idrepresentante');
    }

    public function agendamentosSalas()
    {
        return $this->hasMany('App\AgendamentoSala', 'idrepresentante');
    }

    public function agendamentosAtivos()
    {
        return $this->agendamentosSalas()
        ->with('sala.regional')
        ->where(function($query){
            $query->whereNull('status')->orWhere('status', 'Aguardando Justificativa');
        })
        ->orderBy('dia', 'ASC')
        ->orderBy('periodo', 'ASC')
        ->orderBy('status', 'ASC')
        ->paginate(5);
    }

    public function podeAgendar($mes = null)
    {        
        if(isset($mes))
            return $this->agendamentosSalas()
            ->whereMonth('dia', $mes)
            ->where(function($query){
                $query->whereNull('status')->orWhere('status', 'Aguardando Justificativa');
            })
            ->count() < 4;

        $mesSeguinte = now()->addMonth()->month;

        $atual = $this->agendamentosSalas()
        ->whereMonth('dia', now()->month)
        ->where(function($query){
            $query->whereNull('status')->orWhere('status', 'Aguardando Justificativa');
        })
        ->count() < 4;

        if($mesSeguinte == now()->month)
            return $atual;

        $seguinte = $this->agendamentosSalas()
        ->whereMonth('dia', $mesSeguinte)
        ->where(function($query){
            $query->whereNull('status')->orWhere('status', 'Aguardando Justificativa');
        })
        ->count() < 4;

        return $atual || $seguinte;
    }

    public function podeAgendarByDiaPeriodo($dia, $periodo)
    {
        $dia = Carbon::createFromFormat('d/m/Y', $dia)->format('Y-m-d');
        if(!in_array($periodo, ['manha', 'tarde']))
            return false;

        return $this->agendamentosSalas()
        ->where('dia', $dia)
        ->where('periodo', $periodo)
        ->where(function($query){
            $query->whereNull('status')->orWhere('status', 'Aguardando Justificativa');
        })
        ->count() == 0;
    }
}
