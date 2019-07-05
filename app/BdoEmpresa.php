<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BdoEmpresa extends Model
{
    use SoftDeletes;

	protected $primaryKey = 'idempresa';
    protected $table = 'bdo_empresas';
    protected $fillable = ['segmento', 'cnpj', 'razaosocial', 'fantasia', 'descricao', 'capitalsocial',
    'endereco', 'site', 'email', 'telefone', 'contatonome', 'contatotelefone', 'contatoemail', 'idusuario'];

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }

    public function oportunidade()
    {
    	return $this->hasMany('App\BdoOportunidade', 'idoportunidade');
    }
}
