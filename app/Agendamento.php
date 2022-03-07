<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idagendamento';
    protected $table = 'agendamentos';
    protected $fillable = ['nome', 'cpf', 'email', 'celular', 'dia', 'hora', 'protocolo', 'tiposervico', 'idregional', 'idusuario', 'status'];
    protected $with = ['user', 'regional'];

    // Status de agendamento
    const STATUS_COMPARECEU = "Compareceu";
    const STATUS_NAO_COMPARECEU = "Não Compareceu";
    const STATUS_CANCELADO = "Cancelado";

    // Serviços no agendamento
    const SERVICOS_ATUALIZACAO_DE_CADASTRO = "Atualização de Cadastro";
    const SERVICOS_CANCELAMENTO_DE_REGISTRO = "Cancelamento de Registro";
    const SERVICOS_PLANTAO_JURIDICO = "Plantão Jurídico";
    // const SERVICOS_REFIS = "Refis";
    const SERVICOS_REGISTRO_INICIAL = "Registro Inicial";
    const SERVICOS_OUTROS = "Outros";

    // Array de tipos de pessoas
    const TIPOS_PESSOA = ['Pessoa Física' => 'PF', 'Pessoa Jurídica' => 'PJ', 'Ambas' => 'PF e PJ'];

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

        foreach(Agendamento::servicos() as $servico) {
            foreach(Agendamento::TIPOS_PESSOA as $tipoPessoa) {
                array_push($resultado, $servico . " para " . $tipoPessoa);
            }
        }

        return $resultado;
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
}
