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
use App\BdoRepresentante;

class Representante extends Authenticable
{
    use Notifiable, SoftDeletes;

    private $gerentiRepository;

    protected $guard = 'representante';
    protected $fillable = ['cpf_cnpj', 'registro_core', 'ass_id', 'nome', 'email', 'password', 'verify_token', 'aceite', 'ativo', 'ultimo_acesso'];
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

    public function agendamentosSalas()
    {
        return $this->hasMany('App\AgendamentoSala', 'idrepresentante');
    }

    public function suspensao()
    {
        return $this->hasMany('App\SuspensaoExcecao', 'idrepresentante')->first();
    }

    public function bdoPerfis()
    {
        return $this->hasMany('App\BdoRepresentante', 'idrepresentante');
    }

    public function perfilPublicoSolicitado()
    {
        return $this->bdoPerfis()->whereIn('status->status_final', ['', BdoRepresentante::STATUS_RC_CADASTRO])->orderBy('id', 'DESC')->first();
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

    public function estaHomologado(GerentiRepositoryInterface $gerenti, $servicoSolicitado = null)
    {
        $texto_erro = ' só é disponibilizada após a aprovação/homologação do Pedido de Registro junto a Diretoria. Aguarde a disponibilização, em média 20 dias após a finalização do pedido de registro inicial.';
        $servicoSolicitado_array = [
            'certidao' => 'A Certidão de Registro Profissional', 
            'cedula' => 'A Cédula Profissional'
        ];

        $resultado = $gerenti->gerentiDadosGerais($this->tipoPessoa(), $this->ass_id);

        $resposta = isset($resultado["Data de homologação"]) && ($resultado["Data de homologação"] != '----------');

        if(isset($servicoSolicitado_array[$servicoSolicitado]) && !$resposta)
            return $servicoSolicitado_array[$servicoSolicitado] . $texto_erro;

        return $resposta;
    }

    public function ultimoAcesso()
    {
        return is_null($this->ultimo_acesso) ? $this->updated_at : $this->ultimo_acesso;
    }

    public function registrarUltimoAcesso()
    {
        return $this->update(['ultimo_acesso' => $this->updated_at]);
    }
}
