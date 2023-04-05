<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idagendamento';
    protected $table = 'agendamentos';
    protected $guarded = [];

    const STATUS_COMPARECEU = "Compareceu";
    const STATUS_NAO_COMPARECEU = "Não Compareceu";
    const STATUS_CANCELADO = "Cancelado";

    const SERVICOS_ATUALIZACAO_DE_CADASTRO = "Atualização de Cadastro";
    const SERVICOS_CANCELAMENTO_DE_REGISTRO = "Cancelamento de Registro";
    const SERVICOS_PLANTAO_JURIDICO = "Plantão Jurídico";
    const SERVICOS_REFIS = "Refis";
    const SERVICOS_REGISTRO_INICIAL = "Registro Inicial";
    const SERVICOS_OUTROS = "Outros";

    const TIPOS_PESSOA = ['Pessoa Física' => 'PF', 'Pessoa Jurídica' => 'PJ', 'Ambas' => 'PF e PJ'];

    private function btnReenviarEmail()
    {
        $mensagem = '<form method="POST" action="'.route('agendamentos.reenviarEmail', $this->idagendamento).'" class="d-inline">';
        $mensagem .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
        $mensagem .= '<input type="submit" class="btn btn-sm btn-default" value="Reenviar email de confirmação"></input>';
        $mensagem .= '</form>';

        return $mensagem;
    }

    public static function status()
    { 
        return [
            Agendamento::STATUS_COMPARECEU,
            Agendamento::STATUS_NAO_COMPARECEU,
            Agendamento::STATUS_CANCELADO
        ];
    }

    public static function servicos()
    {
        return [
            Agendamento::SERVICOS_ATUALIZACAO_DE_CADASTRO,
            Agendamento::SERVICOS_CANCELAMENTO_DE_REGISTRO,
            Agendamento::SERVICOS_PLANTAO_JURIDICO,
            // Agendamento::SERVICOS_REFIS,
            Agendamento::SERVICOS_REGISTRO_INICIAL,
            Agendamento::SERVICOS_OUTROS
        ];
    }

    public static function servicosCompletos()
    {
        $resultado = array();

        foreach(Agendamento::servicos() as $servico)
            foreach(Agendamento::TIPOS_PESSOA as $tipoPessoa)
                array_push($resultado, $servico.' para '.$tipoPessoa);

        return $resultado;
    }

    public static function getProtocolo()
    {
        // Gera a HASH (protocolo) aleatória
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVXZ0123456789';
        do {
            $protocoloGerado = substr(str_shuffle($characters), 0, 6);
            $protocoloGerado = 'AGE-'.$protocoloGerado;
            $countProtocolo = Agendamento::where('protocolo', $protocoloGerado)->count();
        } while($countProtocolo != 0);

        return $protocoloGerado;
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'idagendamento');
    }

    public function getMsgByStatus()
    {
        $pendente = '<p class="mb-0 text-danger"><strong><i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Validação pendente</strong></p>';
        $msg = [
            Agendamento::STATUS_CANCELADO => '<p class="mb-0 text-muted"><strong><i class="fas fa-ban"></i>&nbsp;&nbsp;Atendimento cancelado</strong></p>',
            Agendamento::STATUS_NAO_COMPARECEU => '<p class="mb-0 text-warning"><strong><i class="fas fa-user-alt-slash"></i>&nbsp;&nbsp;Não compareceu</strong></p>',
            Agendamento::STATUS_COMPARECEU => '<p class="mb-0 text-success"><strong><i class="fas fa-check-circle"></i>&nbsp;&nbsp;Atendimento realizado com sucesso no dia '.onlyDate($this->dia).', às '.$this->hora.'</strong></p>'
        ];

        if(!$this->isAfter())
            return isset($msg[$this->status]) ? $msg[$this->status] : $pendente;

        if($this->status == Agendamento::STATUS_CANCELADO)
            return $msg[$this->status];

        return $this->btnReenviarEmail();
    }

    public function isAfter()
    {
        return $this->dia > date('Y-m-d');
    }

    public function getBtnByStatus()
    {
        if($this->isAfter())
            $default = null;
        else
        {
            $default = '<form method="POST" action="'.route('agendamentos.updateStatus').'" class="d-inline">';
            $default .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $default .= '<input type="hidden" name="_method" value="PUT" id="method" />';
            $default .= '<input type="hidden" name="idagendamento" value="'.$this->idagendamento.'" />';
            $default .= '<button type="submit" name="status" class="btn btn-sm btn-primary" value="'.Agendamento::STATUS_COMPARECEU.'">Confirmar</button>';
            $default .= '<button type="submit" name="status" class="btn btn-sm btn-danger ml-1" value="'.Agendamento::STATUS_NAO_COMPARECEU.'">'.Agendamento::STATUS_NAO_COMPARECEU.'</button>';
            $default .= '</form>';
        }
        
        $tiposStatus = [
            Agendamento::STATUS_CANCELADO => '<strong>'.Agendamento::STATUS_CANCELADO.'</strong>',
            Agendamento::STATUS_COMPARECEU => '<p class="d-inline"><i class="fas fa-check checkIcone"></i>&nbsp;&nbsp;'.Agendamento::STATUS_COMPARECEU.'</p>',
            Agendamento::STATUS_NAO_COMPARECEU => '<strong>'.Agendamento::STATUS_NAO_COMPARECEU.'</strong>'
        ];

        return isset($tiposStatus[$this->status]) ? $tiposStatus[$this->status] : $default;
    }
}
