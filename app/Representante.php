<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notifiable;
use App\Mail\RepresentanteResetPasswordMail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticable;
use App\Notifications\RepresentanteResetPasswordNotification;
use Carbon\Carbon;
use App\Repositories\GerentiRepositoryInterface;

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

    const NAME_ROUTE = 'representante';
    const NAME_VIEW = 'representante';
    const NAME_AREA_RESTRITA = 'Representante';

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
        Mail::to($this->email)->queue(new RepresentanteResetPasswordMail($token, $body));
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

    public function pagamentos()
    {
        return $this->hasMany('App\Pagamento', 'idrepresentante');
    }

    public function agendamentosSalas()
    {
        return $this->hasMany('App\AgendamentoSala', 'idrepresentante');
    }

    public function suspensao()
    {
        return $this->hasMany('App\SuspensaoExcecao', 'idrepresentante')->first();
    }

    public function agendamentosAtivos()
    {
        return $this->agendamentosSalas()
        ->with('sala.regional')
        ->whereNull('status')
        ->orderBy('dia', 'ASC')
        ->orderBy('periodo', 'ASC')
        ->orderBy('status', 'ASC')
        ->paginate(4);
    }

    public function podeAgendar($mes = null, $ano = null)
    {
        $total = 4;

        // devido poder agendar somente no dia seguinte
        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();

        $atual = $this->agendamentosSalas()
        ->when(isset($mes) && !isset($ano), function($query) use($mes){
            $query->whereMonth('dia', $mes)
            ->whereYear('dia', now()->year);
        })
        ->when(isset($ano) && !isset($mes), function($query) use($ano){
            $query->whereMonth('dia', now()->month)
            ->whereYear('dia', $ano);
        })
        ->when(isset($mes) && isset($ano), function($query) use($mes, $ano){
            $query->whereMonth('dia', $mes)
            ->whereYear('dia', $ano);
        })
        ->when(!isset($mes) && !isset($ano), function($query) use($dia){
            $query->whereMonth('dia', $dia->month)
            ->whereYear('dia', $dia->year);
        })
        ->where(function($query){
            $query->whereNull('status')
            ->orWhere('status', 'Compareceu');
        })        
        ->count() < $total;

        $seguinte = false;
        // Evitar que pule mês. Ex: janeiro para fevereiro.
        $dataSeguinte = Carbon::parse($dia->format('Y-m') . '-01')->addMonth();
        $mesSeguinte = $dataSeguinte->month;
        $anoSeguinte = $dataSeguinte->year;
        
        if(!isset($mes) && !isset($ano))
            $seguinte = $this->agendamentosSalas()
            ->whereMonth('dia', $mesSeguinte)
            ->whereYear('dia', $anoSeguinte)
            ->where(function($query){
                $query->whereNull('status')
                ->orWhere('status', 'Compareceu');
            }) 
            ->count() < $total;

        return $atual || $seguinte;
    }

    public function getPeriodoByDia($dia, $periodosDisponiveis)
    {
        $agendados = $this->agendamentosSalas()
        ->where('dia', $dia)
        ->whereNull('status')
        ->orderBy('dia')
        ->orderBy('periodo_todo', 'DESC')
        ->get();

        foreach($agendados as $agendado)
            $periodosDisponiveis = $agendado->getHorasPermitidas($periodosDisponiveis);

        return $periodosDisponiveis;
    }

    public function getAgendamentos30Dias($diasLotados = array())
    {
        $diasAgendado = array();
        $agendados = $this->agendamentosSalas()
        ->whereNull('status')
        ->whereBetween('dia', [Carbon::tomorrow()->format('Y-m-d'), Carbon::today()->addMonth()->format('Y-m-d')])
        ->orderBy('dia')
        ->get();

        foreach($agendados as $agendado)
        {
            $add = true;
            $dia = Carbon::parse($agendado->dia);
            foreach($diasLotados as $dias){
                if($dias === [$dia->month, $dia->day, 'lotado'])
                    $add = false;
            }
            if($add)
                array_push($diasAgendado, [$dia->month, $dia->day, 'agendado']);
        }

        return $diasAgendado;
    }

    public function getContatosTipoTelefone(GerentiRepositoryInterface $gerenti)
    {
        $contatos = $gerenti->gerentiContatos($this->ass_id);
        foreach($contatos as $chave => $contato)
        {
            if(!in_array($contato['CXP_TIPO'], ['1', '2', '4', '6', '7', '8', '51', '52', '53']) || ($contato['CXP_STATUS'] != 1))
                unset($contatos[$chave]);
        }

        return $contatos;
    }

    public function getPagamento($cobranca_id, $pagamento_id)
    {
        return $this->pagamentos()->where('cobranca_id', $cobranca_id)
        ->where('payment_id', $pagamento_id)->orWhere('combined_id', $pagamento_id)->get();
    }

    public function getPagamentosAprovados($cobrancas)
    {
        return $this->pagamentos()
        ->whereIn('cobranca_id', $cobrancas)
        ->whereIn('status', ['APPROVED', 'CONFIRMED'])
        ->get();
    }

    public function existePagamentoAprovado($cobranca_id)
    {
        return $this->pagamentos()->where('cobranca_id', $cobranca_id)
        ->whereIn('status', ['APPROVED', 'CONFIRMED'])->count() > 0;
    }

    public function getSessionIdPagamento($cobranca_id)
    {
        return apenasNumeros($this->cpf_cnpj) . $cobranca_id;
    }

    public function getCustomerId()
    {
        return apenasNumeros($this->cpf_cnpj) . '_rep';
    }
}
