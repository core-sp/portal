<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolicitaCedula extends Model
{
    protected $table = 'solicitacoes_cedulas';
    protected $guarded = [];
    protected $with = ['representante', 'usuario', 'regional'];

    const STATUS_EM_ANDAMENTO = "Em andamento";
    const STATUS_ACEITO = "Aceito";
    const STATUS_RECUSADO = "Recusado";

    const TIPO_FISICA = "Impressa";
    const TIPO_DIGITAL = "Digital";
    const TIPO_AMBOS = "Impressa e Digital";

    public static function allStatus()
    {
        return [
            self::STATUS_EM_ANDAMENTO,
            self::STATUS_ACEITO,
            self::STATUS_RECUSADO,
        ];
    }

    public static function allTipos()
    {
        return [
            self::TIPO_FISICA,
            self::TIPO_DIGITAL,
            self::TIPO_AMBOS,
        ];
    }
    
    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }

    public function usuario()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function podeGerarPdf()
    {
        return $this->status == SolicitaCedula::STATUS_ACEITO;
    }

    public function showStatus()
    {
        switch ($this->status) {
            case SolicitaCedula::STATUS_EM_ANDAMENTO:
                return 'font-italic';
                break;
            case SolicitaCedula::STATUS_RECUSADO:
                return 'text-danger';
                break;
            case SolicitaCedula::STATUS_ACEITO:
                return 'text-success';
                break;
            default:
                return '';
                break;
        }
    }

    public function contemTipoDigital()
    {
        return in_array($this->tipo, [self::TIPO_DIGITAL, self::TIPO_AMBOS]);
    }

    public function cedulaEmAndamento()
    {
        return $this->status == self::STATUS_EM_ANDAMENTO;
    }

    public function cedulaAceita()
    {
        return $this->status == self::STATUS_ACEITO;
    }

    public function cedulaRecusada()
    {
        return $this->status == self::STATUS_RECUSADO;
    }
}
