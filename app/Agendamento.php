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

    static $status_compareceu = 'Compareceu';
    static $status_nao_compareceu = 'Não Compareceu';
    static $status_cancelado = 'Cancelado';

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public static function status()
    {
        return [
            Agendamento::$status_compareceu,
            Agendamento::$status_nao_compareceu,
            Agendamento::$status_cancelado
        ];
    }

    public static function servicos()
    {
        return [
            'Atualização de Cadastro',
            'Cancelamento de Registro',
            'Registro Inicial',
            'Outros'
        ];
    }

    public static function pessoas()
    {
        return [
            'Pessoa Física' => 'PF',
            'Pessoa Jurídica' => 'PJ',
            'Ambas' => 'PF e PJ'
        ];
    }

    public static function servicosCompletos()
    {
        return [
            'Atualização de Cadastro para PF',
            'Atualização de Cadastro para PJ',
            'Atualização de Cadastro para PF e PJ',
            'Cancelamento de Registro para PF',
            'Cancelamento de Registro para PJ',
            'Cancelamento de Registro para PF e PJ',
            'Registro Inicial para PF',
            'Registro Inicial para PJ',
            'Registro Inicial para PF e PJ',
            'Outros para PF',
            'Outros para PJ',
            'Outros para PF e PJ'
        ];
    }
}
