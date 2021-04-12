<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreCadastro extends Model
{
    use SoftDeletes;

    protected $fillable = ['cpf', 'cnpj', 'tipo', 'nome', 'email', 'anexo1', 'anexo2', 'status', 'motivo'];

    const STATUS_APROVADO = 'Aprovado';
    const STATUS_RECUSADO = 'Recusado';
    const STATUS_PEDENTE = 'Pendente';

    const TIPO_PF = 'Pessoa Física';
    const TIPO_PJ = 'Pessoa Jurídica';
    const TIPO_AMBAS = 'Ambas';

    public static function tipo() 
    {
        return [
            PreCadastro::TIPO_PF,
            PreCadastro::TIPO_PJ,
            PreCadastro::TIPO_AMBAS
        ];
    }
}
