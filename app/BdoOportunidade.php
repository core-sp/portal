<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BdoOportunidade extends Model
{
    use SoftDeletes;

	protected $primaryKey = 'idoportunidade';
    protected $table = 'bdo_oportunidades';
    protected $fillable = ['idempresa', 'titulo', 'segmento', 'regiaoatuacao', 'descricao', 'vagasdisponiveis', 'vagaspreenchidas', 'status', 'observacao', 'datainicio', 'idusuario'];

    // Status de BdoOportunidade
    const STATUS_SOB_ANALISE = "Sob Análise";
    const STATUS_RECUSADO = "Recusado";
    const STATUS_EM_ANDAMENTO = "Em andamento";
    const STATUS_CONCLUIDO = "Concluído";
    const STATUS_EXPIRADO = "Expirado";

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function empresa()
    {
    	return $this->belongsTo('App\BdoEmpresa', 'idempresa');
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'idbdo');
    }

    public static function status()
    {
    	$status = [
            BdoOportunidade::STATUS_SOB_ANALISE,
            BdoOportunidade::STATUS_RECUSADO,
            BdoOportunidade::STATUS_EM_ANDAMENTO,
            BdoOportunidade::STATUS_CONCLUIDO,
    		BdoOportunidade::STATUS_EXPIRADO
        ];
        
        sort($status);
        
        return $status;
    }

    public static function statusDestacado($status)
    {
        switch ($status) {
            case BdoOportunidade::STATUS_SOB_ANALISE:
                return '<strong><i>' .  $status . '</i></strong>';
            break;

            case BdoOportunidade::STATUS_RECUSADO:
                return '<strong class="text-danger">' . $status . '</strong>';
            break;

            case BdoOportunidade::STATUS_CONCLUIDO:
                return '<strong class="text-warning">' . $status . '</strong>';
            break;

            case BdoOportunidade::STATUS_EM_ANDAMENTO:
                return '<strong class="text-success">' . $status . '</strong>';
            break;
            
            default:
                return $status;
            break;
        }
    }
}
