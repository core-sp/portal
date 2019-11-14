<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepresentanteEndereco extends Model
{
    use SoftDeletes;

    protected $fillable = ['ass_id', 'cep', 'bairro', 'logradouro', 'numero', 'complemento', 'estado', 'municipio', 'crimage', 'status'];
}
