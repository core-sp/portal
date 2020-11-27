<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepresentanteEndereco extends Model
{
    use SoftDeletes;

    protected $fillable = ['ass_id', 'cep', 'bairro', 'logradouro', 'numero', 'complemento', 'estado', 'municipio', 'crimage', 'crimagedois', 'status', 'observacao'];

    const STATUS_AGUARDANDO_CONFIRMACAO = "Aguardando confirmação";
    const STATUS_ENVIADO = "Enviado";
    const STATUS_RECUSADO = "Recusado";
}
