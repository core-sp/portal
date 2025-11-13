<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlteracaoRC extends Model
{
    use SoftDeletes;
    
    protected $table = 'alteracoes_rc';
    protected $guarded = [];

    public function bdoRepresentante()
    {
    	return $this->belongsTo('App\BdoRepresentante')->withTrashed();
    }

    public static function camposBdoRC()
    {
    	return [
            'REGIONAL',
            'SEGMENTO'
        ];
    }

    public function alteracaoAceita()
    {
        return !$this->aguardandoAlteracao() && $this->aceito;
    }

    public function aguardandoAlteracao()
    {
        return is_null($this->aceito);
    }
}
