<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    protected $table = 'regionais';
    protected $primaryKey = 'idregional';
    protected $fillable = ['prefixo', 'regional', 'endereco', 'bairro',
    'numero', 'complemento', 'cep', 'telefone', 'fax', 'email',
    'funcionamento', 'ageporhorario', 'responsavel', 'descricao'];
    public $timestamps = false;

    public function user()
    {
        return $this->hasMany('App\User', 'idusuario');
    }
}
