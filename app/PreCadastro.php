<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PreCadastro extends Model
{
    use SoftDeletes;

    protected $fillable = ["cpf", "cnpj", "tipo", "nome", "anexo"];
}
